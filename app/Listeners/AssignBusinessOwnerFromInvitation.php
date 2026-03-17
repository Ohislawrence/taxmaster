<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Models\BusinessInvitation;

class AssignBusinessOwnerFromInvitation
{
    public function handle(Registered $event)
    {
        $user = $event->user;

        // find pending invitation by email
        $invite = BusinessInvitation::where('email', strtolower($user->email))
            ->whereNull('used_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->latest()->first();

        if (! $invite) {
            return;
        }

        $business = $invite->business;
        if ($business) {
            // assign ownership to the newly registered user
            $business->owner_id = $user->id;
            $business->save();

            $invite->used_at = now();
            $invite->accepted_by = $user->id;
            $invite->save();
        }
    }
}
