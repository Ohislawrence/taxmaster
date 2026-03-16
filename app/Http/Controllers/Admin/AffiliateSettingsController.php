<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AffiliateRule;
use App\Models\SubscriptionPlan;

class AffiliateSettingsController extends Controller
{
    public function __construct()
    {
        // Admin routes are protected by the admin middleware in routes/admin.php.
        // Removing the additional ability check to allow users with the admin role
        // to manage affiliate settings even if the specific permission is not assigned.
    }
    public function index()
    {
        $rule = AffiliateRule::where('key', 'global')->first();
        $plans = SubscriptionPlan::active()->get(['id', 'name', 'slug']);

        return Inertia::render('Admin/Affiliate/Settings/Index', [
            'rule' => $rule,
            'plans' => $plans,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:percentage,fixed',
            'mode' => 'required|in:one_off,recurring_1_year',
            'value' => 'required|numeric|min:0',
            'active' => 'boolean',
            'applies_to' => 'required|in:global,plan',
            'plan_slug' => 'nullable|string|required_if:applies_to,plan',
            'description' => 'nullable|string|max:500',
        ]);

        $payload = [
            'type' => $validated['type'],
            'mode' => $validated['mode'],
            'value' => $validated['value'],
            'active' => $request->has('active') ? boolval($validated['active']) : true,
            'applies_to' => $validated['applies_to'],
            'plan_slug' => $validated['applies_to'] === 'plan' ? $validated['plan_slug'] : null,
            'meta' => ['description' => $validated['description'] ?? null],
        ];

        $rule = AffiliateRule::updateOrCreate(['key' => 'global'], $payload);

        return back()->with('success', 'Affiliate commission rule updated.');
    }
}
