<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\VATReturn;
use App\Models\PayeReturn;
use App\Models\WhtReturn;
use App\Services\AiAgentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\PayeSchedule;

class TaxInsightsController extends Controller
{
    /**
     * Return aggregated tax trends for VAT, PAYE and WHT.
     * Query param: months (int) default 12
     */
    public function taxTrends(Request $request)
    {
        $months = (int) $request->query('months', 12);
        $months = max(1, min(60, $months));

        $end = Carbon::now()->startOfMonth();
        $start = (clone $end)->subMonths($months - 1);

        // build period labels (Y-m)
        $periods = [];
        $cursor = clone $start;
        while ($cursor->lte($end)) {
            $periods[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        $vat = VATReturn::select('period', DB::raw('COALESCE(SUM(vat_due),0) as total'))
            ->whereBetween('period', [$start->format('Y-m'), $end->format('Y-m')])
            ->groupBy('period')
            ->pluck('total', 'period')
            ->toArray();

        $paye = PayeReturn::select('period', DB::raw('COALESCE(SUM(total_tax_deducted),0) as total'))
            ->whereBetween('period', [$start->format('Y-m'), $end->format('Y-m')])
            ->groupBy('period')
            ->pluck('total', 'period')
            ->toArray();

        $wht = WhtReturn::select('period', DB::raw('COALESCE(SUM(total_wht_deducted),0) as total'))
            ->whereBetween('period', [$start->format('Y-m'), $end->format('Y-m')])
            ->groupBy('period')
            ->pluck('total', 'period')
            ->toArray();

        $vatSeries = [];
        $payeSeries = [];
        $whtSeries = [];

        foreach ($periods as $p) {
            $vatSeries[] = (float) ($vat[$p] ?? 0);
            $payeSeries[] = (float) ($paye[$p] ?? 0);
            $whtSeries[] = (float) ($wht[$p] ?? 0);
        }

        return response()->json([
            'labels' => $periods,
            'datasets' => [
                ['key' => 'vat', 'label' => 'VAT due', 'data' => $vatSeries],
                ['key' => 'paye', 'label' => 'PAYE deducted', 'data' => $payeSeries],
                ['key' => 'wht', 'label' => 'WHT deducted', 'data' => $whtSeries],
            ],
        ]);
    }

    /**
     * Return rule-based insights for business tax trends
     */
    public function summary(Request $request)
    {
        $months = (int) $request->query('months', 3);
        $months = max(1, min(24, $months));

        $end = Carbon::now()->startOfMonth();
        $start = (clone $end)->subMonths($months - 1);

        $labels = [];
        $cursor = clone $start;
        while ($cursor->lte($end)) {
            $labels[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        $vat = VATReturn::select('period', DB::raw('COALESCE(SUM(vat_due),0) as total'))
            ->whereBetween('period', [$start->format('Y-m'), $end->format('Y-m')])
            ->groupBy('period')
            ->pluck('total', 'period')
            ->toArray();

        $paye = PayeReturn::select('period', DB::raw('COALESCE(SUM(total_tax_deducted),0) as total'))
            ->whereBetween('period', [$start->format('Y-m'), $end->format('Y-m')])
            ->groupBy('period')
            ->pluck('total', 'period')
            ->toArray();

        $wht = WhtReturn::select('period', DB::raw('COALESCE(SUM(total_wht_deducted),0) as total'))
            ->whereBetween('period', [$start->format('Y-m'), $end->format('Y-m')])
            ->groupBy('period')
            ->pluck('total', 'period')
            ->toArray();

        $business = Auth::user()->defaultBusiness();

        $cacheKey = "insights_summary:business:{$business->id}:months:{$months}";

        $insights = Cache::remember($cacheKey, 600, function () use ($labels, $vat, $paye, $wht) {
            $ins = [];

            // Helper to compute pct change
            $computeChange = function ($series, $labels) {
                $len = count($labels);
                if ($len < 2) return null;
                $last = (float) ($series[end($labels)] ?? 0);
                $prev = (float) ($series[$labels[$len-2]] ?? 0);
                if ($prev == 0) return null;
                return (($last - $prev) / max(1, abs($prev))) * 100;
            };

            $labelsLocal = $labels;

            // VAT insight
            $vatChange = $computeChange($vat, $labelsLocal);
            if ($vatChange !== null && abs($vatChange) >= 10) {
                $ins[] = [
                    'key' => 'vat_change',
                    'label' => 'VAT change (month-over-month)',
                    'value' => $vatChange,
                    'message' => sprintf('Your VAT %s by %s%% compared to previous month.', $vatChange > 0 ? 'increased' : 'decreased', number_format(abs($vatChange), 1)),
                    'severity' => $vatChange > 0 ? 'warning' : 'info'
                ];
            }

            // PAYE insight
            $payeChange = $computeChange($paye, $labelsLocal);
            if ($payeChange !== null && abs($payeChange) >= 10) {
                $ins[] = [
                    'key' => 'paye_change',
                    'label' => 'PAYE change (month-over-month)',
                    'value' => $payeChange,
                    'message' => sprintf('PAYE total %s by %s%% compared to previous month.', $payeChange > 0 ? 'increased' : 'decreased', number_format(abs($payeChange), 1)),
                    'severity' => $payeChange > 0 ? 'warning' : 'info'
                ];
            }

            // WHT insight
            $whtChange = $computeChange($wht, $labelsLocal);
            if ($whtChange !== null && abs($whtChange) >= 10) {
                $ins[] = [
                    'key' => 'wht_change',
                    'label' => 'WHT change (month-over-month)',
                    'value' => $whtChange,
                    'message' => sprintf('WHT total %s by %s%% compared to previous month.', $whtChange > 0 ? 'increased' : 'decreased', number_format(abs($whtChange), 1)),
                    'severity' => $whtChange > 0 ? 'warning' : 'info'
                ];
            }

            return $ins;
        });

        // Helper to compute pct change
        $computeChange = function ($series) use ($labels) {
            $len = count($labels);
            if ($len < 2) return null;
            $last = (float) ($series[end($labels)] ?? 0);
            $prev = (float) ($series[$labels[$len-2]] ?? 0);
            if ($prev == 0) return null;
            return (($last - $prev) / max(1, abs($prev))) * 100;
        };

        // VAT insight
        $vatChange = $computeChange($vat);
        if ($vatChange !== null && abs($vatChange) >= 10) {
            $insights[] = [
                'key' => 'vat_change',
                'label' => 'VAT change (month-over-month)',
                'value' => $vatChange,
                'message' => sprintf('Your VAT %s by %s%% compared to previous month.', $vatChange > 0 ? 'increased' : 'decreased', number_format(abs($vatChange), 1)),
                'severity' => $vatChange > 0 ? 'warning' : 'info'
            ];
        }

        // PAYE insight
        $payeChange = $computeChange($paye);
        if ($payeChange !== null && abs($payeChange) >= 10) {
            $insights[] = [
                'key' => 'paye_change',
                'label' => 'PAYE change (month-over-month)',
                'value' => $payeChange,
                'message' => sprintf('PAYE total %s by %s%% compared to previous month.', $payeChange > 0 ? 'increased' : 'decreased', number_format(abs($payeChange), 1)),
                'severity' => $payeChange > 0 ? 'warning' : 'info'
            ];
        }

        // WHT insight
        $whtChange = $computeChange($wht);
        if ($whtChange !== null && abs($whtChange) >= 10) {
            $insights[] = [
                'key' => 'wht_change',
                'label' => 'WHT change (month-over-month)',
                'value' => $whtChange,
                'message' => sprintf('WHT total %s by %s%% compared to previous month.', $whtChange > 0 ? 'increased' : 'decreased', number_format(abs($whtChange), 1)),
                'severity' => $whtChange > 0 ? 'warning' : 'info'
            ];
        }

        // Enrich insights with AI explanations when possible (best-effort)
        try {
            if ($business && config('services.ai.enabled')) {
                $aiService = new AiAgentService($business, config('services.ai.provider'));
                foreach ($insights as &$ins) {
                    try {
                        $aiResp = $aiService->explainInsight($ins['label'], $ins['message']);
                        if (!empty($aiResp['success']) && !empty($aiResp['explanation'])) {
                            $ins['ai_explanation'] = $aiResp['explanation'];
                        }
                    } catch (\Throwable $e) {
                        // ignore per-insight AI failures
                    }
                }
                unset($ins);
            }
        } catch (\Throwable $e) {
            // best-effort only; do not fail the summary endpoint
        }

        return response()->json(['insights' => $insights]);
    }

    /**
     * Compute anomalies and surface top drivers for VAT/PAYE/WHT
     */
    public function anomalies(Request $request)
    {
        $months = (int) $request->query('months', 3);
        $months = max(1, min(12, $months));

        $business = Auth::user()->defaultBusiness();
        $cacheKey = "insights_anomalies:business:{$business->id}:months:{$months}";

        $result = Cache::remember($cacheKey, 600, function () use ($business, $months) {
            $end = Carbon::now()->startOfMonth();
            $start = (clone $end)->subMonths($months - 1);

            $labels = [];
            $cursor = clone $start;
            while ($cursor->lte($end)) {
                $labels[] = $cursor->format('Y-m');
                $cursor->addMonth();
            }

            // VAT top drivers: top invoices in last month by vat_on_sales or total
            $lastPeriod = end($labels);
            $vatDrivers = Invoice::where('business_id', $business->id)
                ->where('period', $lastPeriod)
                ->orderByDesc('vat_on_sales')
                ->take(3)
                ->get(['id', 'invoice_number', 'total', 'vat_on_sales'])
                ->map(function ($inv) {
                    return [
                        'type' => 'invoice',
                        'id' => $inv->id,
                        'label' => $inv->invoice_number,
                        'value' => (float) $inv->vat_on_sales,
                        'link' => url("/business/invoices/{$inv->id}"),
                    ];
                })->toArray();

            // WHT top drivers: top transactions in last month by wht_amount or wht_deducted
            $whtDrivers = Transaction::where('business_id', $business->id)
                ->whereBetween('transaction_date', [ $start->toDateString(), $end->toDateString() ])
                ->whereNotNull('wht_amount')
                ->orderByDesc('wht_amount')
                ->take(3)
                ->get(['id', 'description', 'amount', 'wht_amount'])
                ->map(function ($t) {
                    return [
                        'type' => 'transaction',
                        'id' => $t->id,
                        'label' => $t->description,
                        'value' => (float) $t->wht_amount,
                        'link' => url("/business/transactions?search=" . urlencode($t->reference ?? '')),
                    ];
                })->toArray();

            // PAYE drivers: best-effort from latest paye return schedules
            $payeDrivers = [];
            try {
                $latestPaye = $business->payeReturns()->latest()->first();
                if ($latestPaye) {
                    $schedules = $latestPaye->schedules()->orderByDesc('tax_deducted')->take(3)->get(['id','employee_name','tax_deducted']);
                    $payeDrivers = $schedules->map(function($s){
                        return [
                            'type' => 'paye_schedule',
                            'id' => $s->id,
                            'label' => $s->employee_name ?? 'Employee',
                            'value' => (float) $s->tax_deducted,
                            'link' => url("/business/paye/" . ($s->paye_return_id ?? '')),
                        ];
                    })->toArray();
                }
            } catch (\Throwable $e) {
                $payeDrivers = [];
            }

            return [
                'vat' => $vatDrivers,
                'wht' => $whtDrivers,
                'paye' => $payeDrivers,
            ];
        });

        return response()->json($result);
    }
}
