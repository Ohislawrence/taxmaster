<?php

namespace App\Http\Controllers\Admin;

use App\Models\ComplianceDeadline;
use App\Models\Business;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ComplianceController
{
    /**
     * Display all compliance deadlines across all businesses
     */
    public function index(Request $request)
    {
        $deadlines = ComplianceDeadline::with('business.owner')
            ->when($request->deadline_type, function ($query, $deadlineType) {
                return $query->where('deadline_type', $deadlineType);
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'overdue') {
                    return $query->where('status', 'pending')
                        ->where('due_date', '<', now());
                } else {
                    return $query->where('status', $status);
                }
            })
            ->when($request->business_id, function ($query, $businessId) {
                return $query->where('business_id', $businessId);
            })
            ->when($request->year, function ($query, $year) {
                return $query->whereYear('due_date', $year);
            })
            ->orderBy('due_date', 'asc')
            ->paginate(30)
            ->through(function ($deadline) {
                return [
                    'id' => $deadline->id,
                    'deadline_type' => $deadline->deadline_type,
                    'type_label' => $deadline->type_label,
                    'description' => $deadline->description,
                    'period' => $deadline->period,
                    'due_date' => $deadline->due_date->format('M d, Y'),
                    'status' => $deadline->status,
                    'completed_at' => $deadline->completed_at?->format('M d, Y H:i'),
                    'days_until' => $deadline->days_until,
                    'is_overdue' => $deadline->is_overdue,
                    'urgency' => $deadline->urgency,
                    'frequency' => $deadline->frequency,
                    'business' => [
                        'id' => $deadline->business->id,
                        'name' => $deadline->business->name,
                        'owner_name' => $deadline->business->owner->name ?? 'N/A',
                    ],
                ];
            });

        // Summary statistics
        $stats = [
            'total_deadlines' => ComplianceDeadline::count(),
            'overdue' => ComplianceDeadline::where('status', 'pending')
                ->where('due_date', '<', now())
                ->count(),
            'due_this_week' => ComplianceDeadline::where('status', 'pending')
                ->whereBetween('due_date', [now(), now()->addWeek()])
                ->count(),
            'due_this_month' => ComplianceDeadline::where('status', 'pending')
                ->whereBetween('due_date', [now(), now()->addMonth()])
                ->count(),
            'completed' => ComplianceDeadline::where('status', 'completed')->count(),
        ];

        // Get businesses for filter dropdown
        $businesses = Business::select('id', 'name')
            ->whereHas('complianceDeadlines')
            ->orderBy('name')
            ->get();

        $deadlineTypes = [
            'VAT' => 'VAT Return',
            'WHT' => 'Withholding Tax',
            'PAYE' => 'PAYE/Income Tax',
            'CIT' => 'Corporate Income Tax',
            'CAC_ANNUAL' => 'CAC Annual Return',
            'ITF' => 'Industrial Training Fund',
            'PENCOM' => 'Pension Contribution',
            'NSITF' => 'NSITF Contribution',
        ];

        return Inertia::render('Admin/Compliance/Index', [
            'deadlines' => $deadlines,
            'stats' => $stats,
            'businesses' => $businesses,
            'deadlineTypes' => $deadlineTypes,
            'filters' => $request->only(['deadline_type', 'status', 'business_id', 'year']),
        ]);
    }

    /**
     * Show compliance deadline details
     */
    public function show(ComplianceDeadline $deadline)
    {
        $deadline->load('business.owner');

        return Inertia::render('Admin/Compliance/Show', [
            'deadline' => [
                'id' => $deadline->id,
                'deadline_type' => $deadline->deadline_type,
                'type_label' => $deadline->type_label,
                'description' => $deadline->description,
                'period' => $deadline->period,
                'due_date' => $deadline->due_date->format('M d, Y'),
                'frequency' => ucfirst($deadline->frequency),
                'status' => $deadline->status,
                'completed_at' => $deadline->completed_at?->format('M d, Y H:i:s'),
                'reminded_at' => $deadline->reminded_at?->format('M d, Y H:i:s'),
                'reminder_count' => $deadline->reminder_count,
                'days_until' => $deadline->days_until,
                'is_overdue' => $deadline->is_overdue,
                'urgency' => $deadline->urgency,
                'notes' => $deadline->notes,
                'required_documents' => $deadline->required_documents,
                'attachments' => $deadline->attachments,
                'created_at' => $deadline->created_at->format('M d, Y H:i:s'),
                'business' => [
                    'id' => $deadline->business->id,
                    'name' => $deadline->business->name,
                    'email' => $deadline->business->email,
                    'tin' => $deadline->business->tax_identification_number,
                    'owner' => [
                        'name' => $deadline->business->owner->name ?? 'N/A',
                        'email' => $deadline->business->owner->email ?? 'N/A',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Generate report of overdue compliance
     */
    public function overdueReport(Request $request)
    {
        $query = ComplianceDeadline::with('business.owner')
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->when($request->search, function ($q, $search) {
                return $q->whereHas('business', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('tax_identification_number', 'like', "%{$search}%");
                });
            })
            ->when($request->deadline_type, function ($q, $type) {
                return $q->where('deadline_type', $type);
            })
            ->orderBy('due_date', 'asc');

        $allOverdue = $query->get();

        $groupedDeadlines = $allOverdue
            ->groupBy('business_id')
            ->map(function ($deadlines, $businessId) {
                $business = $deadlines->first()->business;
                $overdueDeadlines = $deadlines->map(function ($deadline) {
                    $daysOverdue = now()->diffInDays($deadline->due_date, false);
                    return [
                        'id' => $deadline->id,
                        'deadline_type' => $deadline->deadline_type,
                        'tax_type' => $deadline->deadline_type,
                        'type_label' => $deadline->type_label,
                        'description' => $deadline->description,
                        'period' => $deadline->period,
                        'due_date' => $deadline->due_date->format('Y-m-d'),
                        'deadline' => $deadline->due_date->format('Y-m-d'),
                        'days_overdue' => abs($daysOverdue),
                        'status' => $deadline->status,
                        'urgency' => $deadline->urgency,
                    ];
                });

                $criticalCount = $overdueDeadlines->filter(function ($d) {
                    return $d['days_overdue'] >= 30;
                })->count();

                $totalDaysOverdue = $overdueDeadlines->sum('days_overdue');

                return [
                    'id' => $business->id,
                    'business_name' => $business->name,
                    'tin' => $business->tax_identification_number,
                    'owner_name' => $business->owner->name ?? 'N/A',
                    'owner_email' => $business->owner->email ?? 'N/A',
                    'overdue_count' => $deadlines->count(),
                    'critical_count' => $criticalCount,
                    'total_days_overdue' => $totalDaysOverdue,
                    'deadlines' => $overdueDeadlines->values(),
                ];
            })
            ->sortByDesc('overdue_count')
            ->values();

        // Summary statistics
        $summary = [
            'total_overdue' => $allOverdue->count(),
            'businesses_affected' => $groupedDeadlines->count(),
            'critical_overdue' => $allOverdue->filter(function ($d) {
                return now()->diffInDays($d->due_date, false) <= -30;
            })->count(),
            'overdue_this_month' => $allOverdue->filter(function ($d) {
                return $d->due_date->isCurrentMonth();
            })->count(),
            'estimated_penalties' => 0, // TODO: Calculate based on business rules
        ];

        return Inertia::render('Admin/Compliance/OverdueReport', [
            'groupedDeadlines' => $groupedDeadlines,
            'summary' => $summary,
            'filters' => $request->only(['search', 'deadline_type', 'overdue_period']),
        ]);
    }
}
