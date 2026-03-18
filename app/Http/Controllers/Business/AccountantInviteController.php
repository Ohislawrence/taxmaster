<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BusinessInvitation;
use App\Notifications\BusinessInvitationNotification;
use Illuminate\Support\Facades\Notification;

class AccountantInviteController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        $business = $user->currentBusiness ?? null;

        if (! $business) {
            abort(403);
        }

        // Only the business owner or admins may invite accountants
        if (! (method_exists($user, 'hasRole') && $user->hasRole('admin')) && $business->owner_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'email' => ['required','email','max:255'],
        ]);

        // Only one accountant may be assigned to a business
        if ($business->accountants()->exists()) {
            return back()->with('error', 'This business already has an assigned accountant.');
        }

        $token = Str::random(48);

        $invite = BusinessInvitation::create([
            'business_id' => $business->id,
            'inviter_id' => $user->id,
            'email' => strtolower(trim($data['email'])),
            'token' => hash('sha256', $token),
            'expires_at' => now()->addDays(14),
            'role' => 'accountant',
        ]);

        Notification::route('mail', $invite->email)
            ->notify(new BusinessInvitationNotification($invite, $token));

        return back()->with('success', 'Invitation sent to ' . $invite->email);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $business = $user->currentBusiness ?? null;

        if (! $business) {
            return response()->json(['message' => 'No business context'], 403);
        }

        if ($business->owner_id !== $user->id && ! (method_exists($user, 'hasRole') && $user->hasRole('admin'))) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $invites = \App\Models\BusinessInvitation::where('business_id', $business->id)
            ->with('inviter')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['invites' => $invites]);
    }

    public function destroy(Request $request, \App\Models\BusinessInvitation $invite)
    {
        $user = $request->user();
        $business = $user->currentBusiness ?? null;

        if (! $business || $invite->business_id !== $business->id) {
            return back()->with('error', 'Cannot revoke this invite.');
        }

        // Only owner or admin may revoke pending invites
        if ($business->owner_id !== $user->id && ! (method_exists($user, 'hasRole') && $user->hasRole('admin'))) {
            return back()->with('error', 'Not authorized to revoke invites.');
        }

        try {
            $invite->delete();
            return back()->with('success', 'Invitation revoked.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to revoke invite: ' . $e->getMessage());
            return back()->with('error', 'Could not revoke invite.');
        }
    }

    public function detachAccountant(Request $request)
    {
        $user = $request->user();
        $business = $user->currentBusiness ?? null;

        if (! $business) {
            return back()->with('error', 'No business context');
        }

        $data = $request->validate([
            'accountant_id' => ['required','integer'],
        ]);

        // Only owner or admin may detach accountant
        if ($business->owner_id !== $user->id && ! (method_exists($user, 'hasRole') && $user->hasRole('admin'))) {
            return back()->with('error', 'Not authorized to remove accountant.');
        }

        $acctId = $data['accountant_id'];

        try {
            $business->accountants()->detach($acctId);
            return back()->with('success', 'Accountant removed.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to detach accountant: ' . $e->getMessage());
            return back()->with('error', 'Could not remove accountant.');
        }
    }
}
