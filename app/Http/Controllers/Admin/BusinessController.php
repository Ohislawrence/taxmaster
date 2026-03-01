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
        $businesses = Business::with('owner', 'subscription', 'staff', 'taxReturns', 'taxPayments')
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
     * Show business details
     */
    public function show(Business $business)
    {
        $business->load('owner', 'subscription');
        $business->loadCount('staff', 'taxReturns', 'taxPayments');

        $recentActivity = BusinessActivityLog::where('business_id', $business->id)
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Admin/Businesses/Show', [
            'business' => $business,
            'recentActivity' => $recentActivity,
        ]);
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
