<?php

namespace App\Http\Controllers\Admin;

use App\Models\VatReturn;
use App\Models\Business;
use Inertia\Inertia;
use Illuminate\Http\Request;

class VatController
{
    /**
     * Display all VAT returns across all businesses
     */
    public function index(Request $request)
    {
        $returns = VatReturn::with('business.owner')
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->business_id, function ($query, $businessId) {
                return $query->where('business_id', $businessId);
            })
            ->when($request->year, function ($query, $year) {
                return $query->whereRaw('YEAR(STR_TO_DATE(period, "%Y-%m")) = ?', [$year]);
            })
            ->orderBy('period', 'desc')
            ->paginate(30)
            ->through(function ($return) {
                return [
                    'id' => $return->id,
                    'period' => $return->period,
                    'period_label' => $return->period_label,
                    'output_vat' => $return->output_vat,
                    'input_vat' => $return->input_vat,
                    'net_vat' => $return->net_vat,
                    'status' => $return->status,
                    'status_label' => $return->status_label,
                    'submitted_at' => $return->submitted_at?->format('M d, Y'),
                    'paid_at' => $return->paid_at?->format('M d, Y'),
                    'due_date' => $return->due_date->format('M d, Y'),
                    'is_overdue' => $return->is_overdue,
                    'business' => [
                        'id' => $return->business->id,
                        'name' => $return->business->name,
                        'owner_name' => $return->business->owner->name ?? 'N/A',
                    ],
                ];
            });

        // Summary statistics
        $stats = [
            'total_returns' => VatReturn::count(),
            'submitted' => VatReturn::where('status', 'submitted')->count(),
            'paid' => VatReturn::where('status', 'paid')->count(),
            'total_vat_collected' => VatReturn::sum('output_vat'),
            'total_vat_paid' => VatReturn::where('status', 'paid')->sum('vat_due'),
            'total_vat_pending' => VatReturn::whereIn('status', ['draft', 'submitted'])->sum('vat_due'),
        ];

        // Get businesses for filter dropdown
        $businesses = Business::select('id', 'name')
            ->whereHas('vatReturns')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/VAT/Index', [
            'returns' => $returns,
            'stats' => $stats,
            'businesses' => $businesses,
            'filters' => $request->only(['status', 'business_id', 'year']),
        ]);
    }

    /**
     * Show VAT return details
     */
    public function show(VatReturn $return)
    {
        $return->load('business.owner');

        return Inertia::render('Admin/VAT/Show', [
            'return' => [
                'id' => $return->id,
                'period' => $return->period,
                'period_label' => $return->period_label,
                'due_date' => $return->due_date->format('M d, Y'),
                'vat_sales' => $return->vat_sales,
                'output_vat' => $return->output_vat,
                'vat_expenses' => $return->vat_expenses,
                'input_vat' => $return->input_vat,
                'net_vat' => $return->net_vat,
                'status' => $return->status,
                'status_label' => $return->status_label,
                'form_002_reference' => $return->form_002_reference,
                'submitted_at' => $return->submitted_at?->format('M d, Y H:i:s'),
                'paid_at' => $return->paid_at?->format('M d, Y H:i:s'),
                'payment_reference' => $return->payment_reference,
                'notes' => $return->notes,
                'created_at' => $return->created_at->format('M d, Y H:i:s'),
                'updated_at' => $return->updated_at->format('M d, Y H:i:s'),
                'business' => [
                    'id' => $return->business->id,
                    'name' => $return->business->name,
                    'email' => $return->business->email,
                    'tin' => $return->business->tax_identification_number,
                    'owner' => [
                        'name' => $return->business->owner->name ?? 'N/A',
                        'email' => $return->business->owner->email ?? 'N/A',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Export VAT returns report
     */
    public function export(Request $request)
    {
        $returns = VatReturn::with('business')
            ->when($request->business_id, fn($q, $id) => $q->where('business_id', $id))
            ->when($request->year, fn($q, $year) => $q->whereRaw('YEAR(STR_TO_DATE(period, "%Y-%m")) = ?', [$year]))
            ->orderBy('period', 'desc')
            ->get();

        $csv = "Business Name,TIN,Period,Output VAT,Input VAT,Net VAT,Status,Submitted Date,Payment Date\n";

        foreach ($returns as $return) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $return->business->name,
                $return->business->tax_identification_number ?? 'N/A',
                $return->period_label,
                $return->output_vat,
                $return->input_vat,
                $return->vat_due,
                $return->status_label,
                $return->submitted_at?->format('Y-m-d') ?? 'N/A',
                $return->paid_at?->format('Y-m-d') ?? 'N/A'
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="vat-returns-export-' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * VAT revenue report
     */
    public function revenueReport(Request $request)
    {
        $year = $request->year ?? now()->year;
        $businessId = $request->business_id;
        $status = $request->status;

        // Get monthly data
        $monthlyDataQuery = VatReturn::with('business')
            ->whereRaw('YEAR(STR_TO_DATE(period, "%Y-%m")) = ?', [$year])
            ->when($businessId, fn($q, $id) => $q->where('business_id', $id))
            ->when($status, function($q, $status) {
                if ($status === 'paid') {
                    return $q->where('status', 'paid');
                } elseif ($status === 'submitted') {
                    return $q->where('status', 'submitted');
                } elseif ($status === 'draft') {
                    return $q->where('status', 'draft');
                } elseif ($status === 'overdue') {
                    return $q->where('status', 'overdue');
                }
            })
            ->get();

        $monthlyData = $monthlyDataQuery->groupBy(function($return) {
            return (int) substr($return->period, 5, 2); // Extract month from "2026-02"
        })->map(function($returns, $month) {
            return [
                'month' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'output_vat' => $returns->sum('output_vat'),
                'input_vat' => $returns->sum('input_vat'),
                'net_vat' => $returns->sum('vat_due'),
                'returns_count' => $returns->count(),
            ];
        })->sortBy('month')->values();

        // Top businesses
        $topBusinesses = VatReturn::with('business')
            ->whereRaw('YEAR(STR_TO_DATE(period, "%Y-%m")) = ?', [$year])
            ->get()
            ->groupBy('business_id')
            ->map(function($returns, $businessId) {
                $business = $returns->first()->business;
                $totalReturns = $returns->count();
                $expectedReturns = 12; // Monthly
                return [
                    'id' => $business->id,
                    'name' => $business->name,
                    'tax_identification_number' => $business->tax_identification_number,
                    'total_output_vat' => $returns->sum('output_vat'),
                    'total_input_vat' => $returns->sum('input_vat'),
                    'total_net_vat' => $returns->sum('vat_due'),
                    'returns_count' => $totalReturns,
                    'compliance_rate' => round(($totalReturns / $expectedReturns) * 100),
                ];
            })
            ->sortByDesc('total_net_vat')
            ->take(10)
            ->values();

        // Payment breakdown
        $allReturns = VatReturn::whereRaw('YEAR(STR_TO_DATE(period, "%Y-%m")) = ?', [$year])
            ->when($businessId, fn($q, $id) => $q->where('business_id', $id))
            ->get();

        $totalVatDue = $allReturns->sum('vat_due');
        $paymentBreakdown = [
            'paid' => [
                'count' => $allReturns->where('status', 'paid')->count(),
                'amount' => $allReturns->where('status', 'paid')->sum('vat_due'),
                'percentage' => $totalVatDue > 0 ? ($allReturns->where('status', 'paid')->sum('vat_due') / $totalVatDue) * 100 : 0,
            ],
            'pending' => [
                'count' => $allReturns->whereIn('status', ['draft', 'submitted'])->count(),
                'amount' => $allReturns->whereIn('status', ['draft', 'submitted'])->sum('vat_due'),
                'percentage' => $totalVatDue > 0 ? ($allReturns->whereIn('status', ['draft', 'submitted'])->sum('vat_due') / $totalVatDue) * 100 : 0,
            ],
            'overdue' => [
                'count' => $allReturns->where('status', 'overdue')->count(),
                'amount' => $allReturns->where('status', 'overdue')->sum('vat_due'),
                'percentage' => $totalVatDue > 0 ? ($allReturns->where('status', 'overdue')->sum('vat_due') / $totalVatDue) * 100 : 0,
            ],
        ];

        // Summary
        $summary = [
            'total_output_vat' => $allReturns->sum('output_vat'),
            'total_input_vat' => $allReturns->sum('input_vat'),
            'net_vat' => $allReturns->sum('vat_due'),
            'total_returns' => $allReturns->count(),
            'businesses_count' => $allReturns->pluck('business_id')->unique()->count(),
        ];

        // Get businesses for filter
        $businesses = Business::select('id', 'name')
            ->whereHas('vatReturns')
            ->orderBy('name')
            ->get();

        // Years for filter
        $years = range(now()->year, now()->year - 5);

        return Inertia::render('Admin/VAT/RevenueReport', [
            'summary' => $summary,
            'monthlyData' => $monthlyData,
            'topBusinesses' => $topBusinesses,
            'paymentBreakdown' => $paymentBreakdown,
            'businesses' => $businesses,
            'years' => $years,
            'filters' => $request->only(['year', 'business_id', 'status']),
        ]);
    }
}
