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
            'roles' => ['admin', 'business'],
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
            'role' => 'required|in:admin,business',
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
        $user->load(['roles', 'ownedBusiness']);

        $userRole = $user->roles->first()?->name;
        $businessInfo = null;

        if ($userRole === 'business' && $user->ownedBusiness) {
            $businessInfo = $user->ownedBusiness->load(['staff', 'taxReturns']);
        }

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'userRole' => $userRole,
            'businessInfo' => $businessInfo,
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
            'roles' => ['admin', 'business'],
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
            'role' => 'required|in:admin,business',
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
            'role' => 'required|in:admin,business',
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
