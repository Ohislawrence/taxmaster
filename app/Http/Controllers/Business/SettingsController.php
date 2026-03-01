<?php

namespace App\Http\Controllers\Business;

use App\Models\BusinessActivityLog;
use Inertia\Inertia;
use Illuminate\Http\Request;

class SettingsController
{
    /**
     * Show settings page
     */
    public function index()
    {
        $business = auth()->user()->ownedBusiness;

        $subscription = $business->subscription()
            ->where('status', 'active')
            ->latest()
            ->first();

        $activityLog = BusinessActivityLog::where('business_id', $business->id)
            ->with('user')
            ->latest()
            ->limit(15)
            ->get();

        return Inertia::render('Business/Settings/Index', [
            'business' => $business,
            'subscription' => $subscription,
            'activityLog' => $activityLog,
        ]);
    }

    /**
     * Update business settings
     */
    public function update(Request $request)
    {
        $business = auth()->user()->ownedBusiness;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:businesses,email,' . $business->id,
            'phone' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',
            'address' => 'required|string',
            'business_type' => 'required|string',
            'tax_identification_number' => 'nullable|string',
            'registration_number' => 'nullable|string',
            'annual_revenue' => 'nullable|numeric|min:0',
            'industry' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $business->update($validated);

        BusinessActivityLog::create([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
            'action' => 'settings_updated',
            'description' => 'Business settings were updated',
        ]);

        return back()->with('success', 'Settings updated successfully');
    }



    /**
     * Show activity log
     */
    public function activityLog(Request $request)
    {
        $business = auth()->user()->ownedBusiness ?? auth()->user()->businesses()->first();

        $activities = BusinessActivityLog::where('business_id', $business->id)
            ->with('user')
            ->latest()
            ->paginate(30);

        return Inertia::render('Business/Settings/ActivityLog', [
            'activities' => $activities,
        ]);
    }

    /**
     * Show subscription details
     */
    public function subscription()
    {
        $business = auth()->user()->ownedBusiness ?? auth()->user()->businesses()->first();

        $currentSubscription = $business->subscription()
            ->where('status', 'active')
            ->latest()
            ->first();

        $availablePlans = config('taxmaster.pricing.plans');

        return Inertia::render('Business/Settings/Subscription', [
            'currentSubscription' => $currentSubscription,
            'availablePlans' => $availablePlans,
        ]);
    }

    /**
     * Upgrade subscription plan
     */
    public function upgradePlan(Request $request)
    {
        $business = auth()->user()->ownedBusiness ?? auth()->user()->businesses()->first();

        $validated = $request->validate([
            'plan_type' => 'required|in:basic,professional,enterprise',
        ]);

        $plan = config("taxmaster.pricing.plans.{$validated['plan_type']}");

        if (!$plan) {
            return back()->withErrors(['error' => 'Invalid plan']);
        }

        // Create new subscription (in real scenario, handle payment first)
        $business->subscription()->create([
            'plan_type' => $validated['plan_type'],
            'monthly_price' => $plan['monthly_price'],
            'annual_price' => $plan['annual_price'],
            'max_staff_members' => $plan['max_staff'],
            'max_returns_per_year' => $plan['max_returns_per_year'],
            'ai_analysis_included' => $plan['features']['ai_analysis'],
            'payment_automation' => $plan['features']['payment_automation'] ?? false,
            'billing_cycle' => 'monthly',
            'status' => 'pending_payment',
            'started_at' => now(),
            'renews_at' => now()->addMonth(),
        ]);

        return back()->with('message', 'Plan upgrade initiated. Please complete the payment.');
    }
}
