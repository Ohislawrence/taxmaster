<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Business;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountantController extends Controller
{
    public function index()
    {
        $accountants = User::role('accountant')->withCount('managedBusinesses')->get();
        return Inertia::render('Admin/Accountants/Index', [
            'accountants' => $accountants,
        ]);
    }

    public function show(User $user)
    {
        $this->authorize('viewAny', User::class);

        $managed = $user->managedBusinesses()->with('owner')->get();

        // available businesses for assignment (not already managed by this accountant)
        $managedIds = $managed->pluck('id')->toArray();
        $available = Business::with('owner')
            ->when(count($managedIds) > 0, function ($q) use ($managedIds) {
                $q->whereNotIn('id', $managedIds);
            })
            ->get();

        // get recent activity from business activity logs if available
        $activities = [];
        if (method_exists($user, 'activityLogs')) {
            $activities = $user->activityLogs()->latest()->limit(50)->get();
        }

        return Inertia::render('Admin/Accountants/Show', [
            'accountant' => $user->load('roles'),
            'managedBusinesses' => $managed,
            'activities' => $activities,
            'availableBusinesses' => $available,
        ]);
    }

    public function detachBusiness(Request $request, User $user, Business $business)
    {
        $this->authorize('manageUsers', User::class);

        $user->managedBusinesses()->detach($business->id);

        return redirect()->back()->with('message', 'Accountant detached from business');
    }

    public function assignBusiness(Request $request, User $user)
    {
        $this->authorize('manageUsers', User::class);

        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
        ]);

        $businessId = $validated['business_id'];

        // attach without removing other assignments
        $user->managedBusinesses()->syncWithoutDetaching([$businessId]);

        return redirect()->back()->with('message', 'Business assigned to accountant');
    }

    public function transferOwnership(Request $request, User $user, Business $business)
    {
        $this->authorize('manageUsers', User::class);

        $business->owner_id = $request->input('new_owner_id');
        $business->billing_managed_by_platform = $request->boolean('enable_billing', false);
        $business->save();

        return redirect()->back()->with('message', 'Ownership transferred');
    }

    public function enableBilling(Request $request, User $user, Business $business)
    {
        $this->authorize('manageUsers', User::class);

        $business->billing_managed_by_platform = true;
        $business->save();

        return redirect()->back()->with('message', 'Billing enabled on-platform for business');
    }
}
