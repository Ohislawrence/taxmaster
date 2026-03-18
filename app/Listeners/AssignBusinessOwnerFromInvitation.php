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
        if (! $business) {
            return;
        }

        $role = $invite->role ?? 'owner';

        if ($role === 'accountant') {
            // Only attach if there's no accountant yet
            try {
                if (! $business->accountants()->exists()) {
                    $business->accountants()->attach($user->id);
                } else {
                    \Illuminate\Support\Facades\Log::info('Accountant invite accepted but business already has an accountant. Skipping attach.');
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to attach accountant from invite: ' . $e->getMessage());
            }
        } else {
            // assign ownership only if unassigned
            if (! $business->owner_id) {
                $business->owner_id = $user->id;
                $business->save();
            } else {
                \Illuminate\Support\Facades\Log::info('Owner invite accepted but business already has an owner. Skipping assignment.');
            }
        }

        $invite->used_at = now();
        $invite->accepted_by = $user->id;
        $invite->save();
    }
}
