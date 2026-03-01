<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    /**
     * Display a listing of subscription plans.
     */
    public function index()
    {
        $plans = SubscriptionPlan::withCount('subscriptions')
            ->orderBy('display_order')
            ->paginate(15)
            ->through(function ($plan) {
                $plan->monthly_price = (float) $plan->monthly_price ?? 0;
                $plan->annual_price = (float) $plan->annual_price ?? 0;
                return $plan;
            });



        return Inertia::render('Admin/Plans/Index', [
            'plans' => $plans,
        ]);
    }

    /**
     * Show the form for creating a new plan.
     */
    public function create()
    {
        return Inertia::render('Admin/Plans/Form', [
            'plan' => null,
            'isEditing' => false,
        ]);
    }

    /**
     * Store a newly created plan in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscription_plans',
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'nullable|numeric|min:0',
            'max_staff_members' => 'required|integer|min:1',
            'max_returns_per_year' => 'required|integer|min:1',
            'storage_gb' => 'required|integer|min:1',
            'ai_analysis_included' => 'boolean',
            'payment_automation' => 'boolean',
            'priority_support' => 'boolean',
            'custom_branding' => 'boolean',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'display_order' => 'required|integer|min:0',
        ]);

        $validated['features'] = $validated['features'] ?? [];

        SubscriptionPlan::create($validated);

        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    /**
     * Show the form for editing the specified plan.
     */
    public function edit(SubscriptionPlan $plan)
    {
        return Inertia::render('Admin/Plans/Form', [
            'plan' => $plan,
            'isEditing' => true,
        ]);
    }

    /**
     * Update the specified plan in storage.
     */
    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscription_plans,slug,' . $plan->id,
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'nullable|numeric|min:0',
            'max_staff_members' => 'required|integer|min:1',
            'max_returns_per_year' => 'required|integer|min:1',
            'storage_gb' => 'required|integer|min:1',
            'ai_analysis_included' => 'boolean',
            'payment_automation' => 'boolean',
            'priority_support' => 'boolean',
            'custom_branding' => 'boolean',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'display_order' => 'required|integer|min:0',
        ]);

        $validated['features'] = $validated['features'] ?? [];

        $plan->update($validated);

        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    /**
     * Remove the specified plan from storage.
     */
    public function destroy(SubscriptionPlan $plan)
    {
        // Check if plan has active subscriptions
        if ($plan->activeSubscriptions()->exists()) {
            return redirect()
                ->route('admin.plans.index')
                ->with('error', 'Cannot delete plan with active subscriptions.');
        }

        $plan->delete();

        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }
}
