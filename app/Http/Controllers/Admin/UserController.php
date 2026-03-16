<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserController
{
    /**
     * Display all users
     */
    public function index(Request $request)
    {
        $users = User::with('roles')
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->role, function ($query, $role) {
                return $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            })
            ->paginate(20);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    /**
     * Show create user form
     */
    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => ['admin', 'business', 'accountant'],
        ]);
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:admin,business,accountant',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'email_verified_at' => now(),
        ]);

        // Assign role using Spatie
        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.show', $user)
            ->with('message', 'User created successfully');
    }

    /**
     * Show user details
     */
    public function show(User $user)
    {
        $user->load(['roles', 'ownedBusiness', 'businesses', 'managedBusinesses']);

        $userRole = $user->roles->first()?->name;

        // Associated businesses: owned + managed
        $owned = $user->ownedBusiness ? collect([$user->ownedBusiness]) : collect();
        $ownedList = $user->businesses ?? collect();
        $managed = $user->managedBusinesses ?? collect();

        $associatedBusinesses = $owned->merge($ownedList)->merge($managed)->unique('id')->values();

        $businessInfo = null;
        if ($userRole === 'business' && $user->ownedBusiness) {
            $businessInfo = $user->ownedBusiness->load(['staff', 'taxReturns']);
        }

        // Affiliate earnings (for accountants)
        $affiliateSummary = null;
        $recentPayouts = collect();

        if ($user->hasRole('accountant') || $user->affiliate_code) {
            $totalPaid = \App\Models\AffiliatePayout::whereHas('referral', function ($q) use ($user) {
                $q->where('accountant_id', $user->id);
            })->where('paid', true)->sum('amount');

            $totalApprovedNotPaid = \App\Models\AffiliatePayout::whereHas('referral', function ($q) use ($user) {
                $q->where('accountant_id', $user->id);
            })->where('approved', true)->where('paid', false)->sum('amount');

            $totalPending = \App\Models\AffiliatePayout::whereHas('referral', function ($q) use ($user) {
                $q->where('accountant_id', $user->id);
            })->where('approved', false)->sum('amount');

            $countPayouts = \App\Models\AffiliatePayout::whereHas('referral', function ($q) use ($user) {
                $q->where('accountant_id', $user->id);
            })->count();

            $recentPayouts = \App\Models\AffiliatePayout::with(['referral.business'])
                ->whereHas('referral', function ($q) use ($user) {
                    $q->where('accountant_id', $user->id);
                })->latest()->take(10)->get();

            $affiliateSummary = [
                'total_paid' => round(floatval($totalPaid), 2),
                'total_approved_not_paid' => round(floatval($totalApprovedNotPaid), 2),
                'total_pending' => round(floatval($totalPending), 2),
                'payout_count' => $countPayouts,
            ];
        }

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'userRole' => $userRole,
            'businessInfo' => $businessInfo,
            'associatedBusinesses' => $associatedBusinesses,
            'affiliateSummary' => $affiliateSummary,
            'recentPayouts' => $recentPayouts,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(User $user)
    {
        $user->load('roles');

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => ['admin', 'business', 'accountant'],
            'currentRole' => $user->roles->first()?->name,
        ]);
    }

    /**
     * Update user
     */
    public function update(User $user, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,business,accountant',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Update role if changed
        $currentRole = $user->roles->first()?->name;
        if ($currentRole !== $validated['role']) {
            $user->syncRoles([$validated['role']]);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('message', 'User updated successfully');
    }

    /**
     * Update user password
     */
    public function updatePassword(User $user, Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        return back()->with('message', 'Password updated successfully');
    }

    /**
     * Change user role
     */
    public function changeRole(User $user, Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,business,accountant',
        ]);

        // Sync role (removes old roles and assigns new)
        $user->syncRoles([$validated['role']]);

        return back()->with('message', 'User role updated successfully');
    }

    /**
     * Disable/suspend user
     */
    public function suspend(User $user)
    {
        $user->update(['email_verified_at' => null]); // Mark as unverified

        return back()->with('message', 'User suspended');
    }

    /**
     * Reactivate user
     */
    public function reactivate(User $user)
    {
        $user->update(['email_verified_at' => now()]);

        return back()->with('message', 'User reactivated');
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        // Prevent deleting the last admin
        $adminCount = User::role('admin')->count();

        if ($user->hasRole('admin') && $adminCount <= 1) {
            return back()->withErrors(['error' => 'Cannot delete the last admin user']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('message', 'User deleted successfully');
    }
}
