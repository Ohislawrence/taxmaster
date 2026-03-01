<?php

namespace App\Http\Controllers\Admin;

use App\Models\Business;
use App\Models\BusinessSubscription;
use Inertia\Inertia;
use Illuminate\Http\Request;

class SubscriptionController
{
    /**
     * Display all subscriptions
     */
    public function index(Request $request)
    {
        $subscriptions = BusinessSubscription::with('business')
            ->when($request->search, function ($query, $search) {
                return $query->whereHas('business', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->plan, function ($query, $plan) {
                return $query->where('plan_type', $plan);
            })
            ->paginate(20);

        $stats = [
            'active' => BusinessSubscription::where('status', 'active')->count(),
            'monthly_revenue' => BusinessSubscription::where('status', 'active')->sum('monthly_price'),
            'enterprise' => BusinessSubscription::where('plan_type', 'enterprise')->count(),
            'expiring_soon' => BusinessSubscription::where('renews_at', '<', now()->addDays(7))
                ->where('status', 'active')
                ->count(),
        ];

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'subscriptionStats' => $stats,
            'filters' => $request->only(['search', 'status', 'plan']),
        ]);
    }

    /**
     * Show subscription details
     */
    public function show(BusinessSubscription $subscription)
    {
        $subscription->load('business');

        // Placeholder for payment history - replace with actual model relationship
        $paymentHistory = [];

        return Inertia::render('Admin/Subscriptions/Show', [
            'subscription' => $subscription,
            'paymentHistory' => $paymentHistory,
        ]);
    }

    /**
     * Manage subscription (upgrade, downgrade, cancel)
     */
    public function manage(BusinessSubscription $subscription, Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:upgrade,downgrade,cancel,renew',
            'plan_type' => 'nullable|in:basic,professional,enterprise',
        ]);

        $action = $validated['action'];

        if ($action === 'cancel') {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        } elseif ($action === 'renew') {
            $subscription->update([
                'status' => 'active',
                'renews_at' => now()->add($subscription->billing_cycle === 'annual' ? '1 year' : '1 month'),
            ]);
        } elseif (in_array($action, ['upgrade', 'downgrade']) && isset($validated['plan_type'])) {
            $plan = config("taxmaster.pricing.plans.{$validated['plan_type']}");

            if ($plan) {
                $subscription->update([
                    'plan_type' => $validated['plan_type'],
                    'monthly_price' => $plan['monthly_price'],
                    'annual_price' => $plan['annual_price'],
                    'max_staff_members' => $plan['max_staff'],
                    'max_returns_per_year' => $plan['max_returns_per_year'],
                ]);
            }
        }

        return back()->with('message', "Subscription {$action} completed successfully");
    }
}
