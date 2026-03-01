<?php

namespace App\Http\Controllers\Admin;

use App\Models\Business;
use App\Models\BusinessActivityLog;
use App\Models\BusinessSubscription;
use App\Models\TaxPayment;
use App\Models\TaxReturn;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController
{
    /**
     * Show the admin dashboard
     */
    public function index()
    {
        $stats = [
            'total_businesses' => Business::where('status', 'active')->count(),
            'total_users' => \App\Models\User::count(),
            'total_revenue' => TaxPayment::where('status', 'completed')
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('amount'),
            'pending_payments' => TaxPayment::where('status', 'pending')->sum('amount'),
            'pending_returns' => TaxReturn::where('due_date', '>=', now())
                ->where('status', '!=', 'submitted')
                ->count(),
        ];

        $recentUsers = \App\Models\User::with('roles')
            ->latest()
            ->take(5)
            ->get();

        $pendingReturns = TaxReturn::with('business')
            ->where('status', '!=', 'submitted')
            ->where('due_date', '>=', now())
            ->latest('due_date')
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'pendingReturns' => $pendingReturns,
        ]);
    }

    /**
     * Get tax report
     */
    public function taxReport()
    {
        $taxReturns = TaxReturn::with('business')
            ->orderBy('due_date', 'desc')
            ->paginate(20);

        $summaryByStatus = TaxReturn::groupBy('status')
            ->selectRaw('status, count(*) as count, sum(total_tax_due) as total_due, sum(total_tax_paid) as total_paid')
            ->get();

        return Inertia::render('Admin/Reports/TaxReport', [
            'taxReturns' => $taxReturns,
            'summary' => $summaryByStatus,
        ]);
    }

    /**
     * Get payment report
     */
    public function paymentReport()
    {
        $payments = TaxPayment::with(['business', 'taxReturn'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $summaryByStatus = TaxPayment::groupBy('status')
            ->selectRaw('status, count(*) as count, sum(amount) as total_amount')
            ->get();

        return Inertia::render('Admin/Reports/PaymentReport', [
            'payments' => $payments,
            'summary' => $summaryByStatus,
        ]);
    }

    /**
     * Get revenue report
     */
    public function revenueReport()
    {
        // Overall metrics
        $metrics = [
            'total_revenue' => TaxPayment::where('status', 'completed')
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('amount'),
            'growth_rate' => 12.5, // Placeholder
            'subscription_revenue' => BusinessSubscription::where('status', 'active')->sum('monthly_price'),
            'active_subscriptions' => BusinessSubscription::where('status', 'active')->count(),
            'payment_revenue' => TaxPayment::where('status', 'completed')
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('amount'),
            'total_transactions' => TaxPayment::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        // Revenue by plan
        $revenueByPlan = BusinessSubscription::where('status', 'active')
            ->groupBy('plan_type')
            ->selectRaw('plan_type as plan_name, count(*) as count, sum(monthly_price) as revenue')
            ->get()
            ->map(function ($item) use ($metrics) {
                $total = $metrics['subscription_revenue'] ?: 1;
                return [
                    'plan_name' => $item->plan_name,
                    'count' => $item->count,
                    'revenue' => $item->revenue,
                    'percentage' => ($total > 0) ? round(($item->revenue / $total) * 100) : 0,
                ];
            });

        // Top revenue sources
        $topRevenueSources = TaxPayment::with('business')
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('amount')
            ->take(10)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'business_name' => $payment->business->name,
                    'transaction_type' => 'Tax Payment',
                    'amount' => $payment->amount,
                    'date' => $payment->created_at,
                ];
            })
            ->concat(
                BusinessSubscription::with('business')
                    ->where('status', 'active')
                    ->where('updated_at', '>=', now()->subDays(30))
                    ->take(5)
                    ->get()
                    ->map(function ($subscription) {
                        return [
                            'id' => $subscription->id,
                            'business_name' => $subscription->business->name,
                            'transaction_type' => 'Subscription',
                            'amount' => $subscription->amount,
                            'date' => $subscription->updated_at,
                        ];
                    })
            )
            ->sortByDesc('amount')
            ->take(10)
            ->values();

        return Inertia::render('Admin/Reports/RevenueReport', [
            'metrics' => $metrics,
            'revenueByPlan' => $revenueByPlan,
            'topRevenueSources' => $topRevenueSources,
        ]);
    }
}
