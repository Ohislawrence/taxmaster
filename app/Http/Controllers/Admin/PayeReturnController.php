<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayeReturn;
use App\Models\Business;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayeReturnController extends Controller
{
    /**
     * Display all PAYE returns across all businesses
     */
    public function index(Request $request)
    {
        $query = PayeReturn::with(['business', 'governmentPayments'])
            ->orderBy('period', 'desc');

        // Filter by business
        if ($request->filled('business_id')) {
            $query->where('business_id', $request->business_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by period
        if ($request->filled('period')) {
            $query->where('period', 'like', $request->period . '%');
        }

        // Search by business name
        if ($request->filled('search')) {
            $query->whereHas('business', function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%');
            });
        }

        $returns = $query->paginate(15);

        // Get stats
        $totalReturns = PayeReturn::count();
        $filedReturns = PayeReturn::where('status', 'filed')->count();
        $pendingReturns = PayeReturn::where('status', 'pending')->count();

        // Calculate total PAYE from all businesses
        $totalPayeTax = PayeReturn::sum('total_paye_tax');

        // Get list of businesses for filter
        $businesses = Business::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/PayeReturns/Index', [
            'returns' => $returns,
            'stats' => [
                'total' => $totalReturns,
                'filed' => $filedReturns,
                'pending' => $pendingReturns,
                'totalTax' => $totalPayeTax,
            ],
            'businesses' => $businesses,
            'filters' => $request->only('business_id', 'status', 'period', 'search'),
        ]);
    }

    /**
     * Display a specific PAYE return
     */
    public function show(PayeReturn $payeReturn)
    {
        $payeReturn->load(['business', 'schedules.staff', 'governmentPayments']);

        $stats = [
            'totalStaff' => $payeReturn->schedules->count(),
            'totalGross' => $payeReturn->schedules->sum('gross_income'),
            'totalRelief' => $payeReturn->schedules->sum('relief_amount'),
            'totalPayeTax' => $payeReturn->total_paye_tax,
        ];

        return Inertia::render('Admin/PayeReturns/Show', [
            'payeReturn' => $payeReturn,
            'stats' => $stats,
        ]);
    }

    /**
     * Export PAYE returns
     */
    public function export(Request $request)
    {
        $query = PayeReturn::with('business');

        if ($request->filled('business_id')) {
            $query->where('business_id', $request->business_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $returns = $query->get();

        $csv = "Period,Business,Status,Total Staff,Gross Income,PAYE Tax,Filed Date\n";

        foreach ($returns as $return) {
            $csv .= implode(',', [
                $return->period,
                $return->business->name,
                $return->status,
                $return->schedules->count(),
                $return->schedules->sum('gross_income'),
                $return->total_paye_tax,
                $return->filed_date ?? 'N/A',
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="paye-returns-' . date('Y-m-d') . '.csv"');
    }

    /**
     * Get revenue report
     */
    public function revenueReport()
    {
        // Revenue by month
        $monthlyRevenue = PayeReturn::selectRaw('YEAR(period) as year, MONTH(period) as month, SUM(total_paye_tax) as revenue')
            ->groupByRaw('YEAR(period), MONTH(period)')
            ->orderByRaw('YEAR(period) DESC, MONTH(period) DESC')
            ->limit(12)
            ->get();

        // Top businesses by PAYE collection
        $topBusinesses = PayeReturn::with('business')
            ->selectRaw('business_id, SUM(total_paye_tax) as total_tax')
            ->groupBy('business_id')
            ->orderByRaw('total_tax DESC')
            ->limit(10)
            ->get();

        $totalRevenue = PayeReturn::sum('total_paye_tax');

        return Inertia::render('Admin/Reports/PayeRevenue', [
            'monthlyRevenue' => $monthlyRevenue,
            'topBusinesses' => $topBusinesses,
            'totalRevenue' => $totalRevenue,
        ]);
    }
}
