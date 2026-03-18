<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Business;
use App\Models\BusinessInvitation;
use App\Notifications\BusinessInvitationNotification;
use Illuminate\Support\Facades\Notification;

use Inertia\Inertia;

class BusinessInviteController extends Controller
{
    public function store(Request $request, Business $business)
    {
        $user = $request->user();

        // Only accountants who manage the business may invite
        // Allow accountants who manage the business, or admins
        if (! (method_exists($user, 'hasRole') && $user->hasRole('admin')) && ! $user->managesBusiness($business)) {
            abort(403);
        }

        $data = $request->validate([
            'email' => ['required','email','max:255'],
        ]);

        // Ensure business does not already have an owner
        if ($business->owner_id) {
            return back()->with('error', 'This business already has an owner assigned.');
        }

        $token = Str::random(48);

        $invite = BusinessInvitation::create([
            'business_id' => $business->id,
            'inviter_id' => $user->id,
            'email' => strtolower(trim($data['email'])),
            'token' => hash('sha256', $token),
            'role' => 'owner',
            'expires_at' => now()->addDays(14),
        ]);

        // send notification with raw token
        Notification::route('mail', $invite->email)
            ->notify(new BusinessInvitationNotification($invite, $token));

        return back()->with('success', 'Invitation sent to ' . $invite->email);
    }

    public function index(Request $request, Business $business)
    {
        $user = $request->user();

        if (! $user->managesBusiness($business)) {
            abort(403);
        }

        $invites = BusinessInvitation::where('business_id', $business->id)
            ->with('inviter')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Accountant/Businesses/Invites', [
            'business' => $business,
            'invites' => $invites,
        ]);
    }
}
