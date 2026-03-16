<?php

namespace App\Http\Controllers\Admin;

use App\Models\Business;
use App\Models\BusinessActivityLog;
use Inertia\Inertia;
use Illuminate\Http\Request;

class BusinessController
{
    /**
     * Display all businesses
     */
    public function index(Request $request)
    {
        $businesses = Business::with('owner', 'createdByAccountant', 'subscription', 'staff', 'taxReturns', 'taxPayments')
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->subscription, function ($query, $subscription) {
                return $query->whereHas('subscription', function ($q) use ($subscription) {
                    $q->where('plan_type', $subscription);
                });
            })
            ->withCount('staff', 'taxReturns', 'taxPayments')
            ->paginate(20);

        return Inertia::render('Admin/Businesses/Index', [
            'businesses' => $businesses,
            'filters' => $request->only(['search', 'status', 'subscription']),
        ]);
    }

    /**
     * Show create form for admin to create a business and assign an owner/accountant
     */
    public function create()
    {
        // fetch users who can be owners or accountants
        $owners = \App\Models\User::role('business')->get(['id', 'name']);
        $accountants = \App\Models\User::role('accountant')->get(['id', 'name']);

        return Inertia::render('Admin/Businesses/Create', [
            'owners' => $owners,
            'accountants' => $accountants,
        ]);
    }

    /**
     * Store a new business created by admin. If assigned to a business-role user, set owner_id.
     * If assigned to an accountant, attach via pivot so they manage the business.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'registered_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:businesses',
            'phone' => 'required|string',
            'business_type' => 'required|in:sole_proprietorship,partnership,limited_liability,corporation,company',
            'industry' => 'nullable|string',
            'tin' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'nullable|string',
            'state' => 'required|string',
            'postal_code' => 'nullable|string',
            'assigned_user_id' => 'nullable|exists:users,id',
            'assigned_role' => 'nullable|in:business,accountant',
        ]);

        $business = new Business($validated);

        // initialize billing managed by platform by default
        $business->billing_managed_by_platform = true;

        // assign owner or accountant based on selected role
        if (!empty($validated['assigned_user_id'])) {
            $assignedUser = \App\Models\User::find($validated['assigned_user_id']);
            if ($assignedUser && $validated['assigned_role'] === 'business') {
                if (! $assignedUser->hasRole('business')) {
                    return back()->withErrors(['assigned_user_id' => 'Selected user is not a business owner']);
                }
                $business->owner_id = $assignedUser->id;
            }
        }

        $business->save();

        if (!empty($validated['assigned_user_id']) && $validated['assigned_role'] === 'accountant') {
            $acct = \App\Models\User::find($validated['assigned_user_id']);
            if (! $acct || ! $acct->hasRole('accountant')) {
                return back()->withErrors(['assigned_user_id' => 'Selected user is not an accountant']);
            }
            $business->accountants()->syncWithoutDetaching([$acct->id]);
        }

        BusinessActivityLog::create([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'subject_type' => Business::class,
            'subject_id' => $business->id,
            'description' => 'Business created by admin',
        ]);

        return redirect()->route('admin.businesses.show', $business)
            ->with('message', 'Business created successfully');
    }

    /**
     * Assign an accountant to a business (admin only)
     */
    public function assignAccountant(Request $request, Business $business)
    {
        $validated = $request->validate([
            'accountant_id' => 'required|exists:users,id',
        ]);

        $accountant = \App\Models\User::findOrFail($validated['accountant_id']);

        // Attach via pivot (no duplicate)
        $business->accountants()->syncWithoutDetaching([$accountant->id]);

        BusinessActivityLog::create([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
            'action' => 'accountant_assigned',
            'subject_type' => Business::class,
            'subject_id' => $business->id,
            'description' => "Assigned accountant {$accountant->id} to business",
        ]);

        return back()->with('message', 'Accountant assigned to business');
    }

    /**
     * Show business details
     */
    public function show(Business $business)
    {
        $business->load('owner', 'createdByAccountant', 'subscription');
        $business->loadCount('staff', 'taxReturns', 'taxPayments');

        $recentActivity = BusinessActivityLog::where('business_id', $business->id)
            ->latest()
            ->take(10)
            ->get();

        $owners = \App\Models\User::role('business')->get(['id', 'name']);
        $accountants = \App\Models\User::role('accountant')->get(['id', 'name']);

        return Inertia::render('Admin/Businesses/Show', [
            'business' => $business,
            'recentActivity' => $recentActivity,
            'owners' => $owners,
            'accountants' => $accountants,
        ]);
    }

    /**
     * Assign owner or accountant to a business (from admin UI)
     */
    public function assignOwner(Request $request, Business $business)
    {
        $this->authorize('manageUsers', \App\Models\User::class);

        $validated = $request->validate([
            'assigned_user_id' => 'required|exists:users,id',
            'assigned_role' => 'required|in:business,accountant',
        ]);

        $user = \App\Models\User::find($validated['assigned_user_id']);

        if ($validated['assigned_role'] === 'business') {
            if (! $user->hasRole('business')) {
                return back()->withErrors(['assigned_user_id' => 'Selected user is not a business owner']);
            }
            $business->owner_id = $user->id;
            $business->save();
        } else {
            if (! $user->hasRole('accountant')) {
                return back()->withErrors(['assigned_user_id' => 'Selected user is not an accountant']);
            }
            $business->accountants()->syncWithoutDetaching([$user->id]);
        }

        BusinessActivityLog::create([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
            'action' => 'owner_changed',
            'subject_type' => Business::class,
            'subject_id' => $business->id,
            'description' => 'Assigned owner/accountant via admin UI',
        ]);

        return back()->with('message', 'Assignment updated');
    }

    /**
     * Update business status
     */
    public function updateStatus(Business $business, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $business->update($validated);

        BusinessActivityLog::create([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
            'action' => 'status_changed',
            'subject_type' => Business::class,
            'subject_id' => $business->id,
            'description' => "Status changed to {$validated['status']}",
        ]);

        return back()->with('message', 'Business status updated');
    }

    /**
     * Get activity log
     */
    public function activity(Business $business)
    {
        $activityLogs = BusinessActivityLog::where('business_id', $business->id)
            ->with('user')
            ->latest()
            ->paginate(30);

        return Inertia::render('Admin/Businesses/Activity', [
            'business' => $business,
            'activityLogs' => $activityLogs,
        ]);
    }

    /**
     * Edit business
     */
    public function edit(Business $business)
    {
        return Inertia::render('Admin/Businesses/Edit', [
            'business' => $business,
        ]);
    }

    /**
     * Update business
     */
    public function update(Business $business, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'registered_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:businesses,email,' . $business->id,
            'phone' => 'required|string',
            'business_type' => 'required|in:sole_proprietorship,partnership,limited_liability,corporation',
            'industry' => 'nullable|string',
            'tin' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended',
            'address' => 'required|string',
            'city' => 'nullable|string',
            'state' => 'required|string',
            'postal_code' => 'nullable|string',
        ]);

        $business->update($validated);

        BusinessActivityLog::create([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
            'action' => 'updated',
            'subject_type' => Business::class,
            'subject_id' => $business->id,
            'description' => 'Business information updated',
        ]);

        return redirect()->route('admin.businesses.show', $business)
            ->with('message', 'Business updated successfully');
    }

    /**
     * Delete business
     */
    public function destroy(Business $business)
    {
        $business->delete();

        return redirect()->route('admin.businesses.index')
            ->with('message', 'Business deleted successfully');
    }
}
