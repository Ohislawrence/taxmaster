<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Notifications\BusinessWelcomeNotification;

class SendBusinessWelcomeEmail
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;

        try {
            // Only send to users with the business role
            if (method_exists($user, 'hasRole') && $user->hasRole('business')) {
                $user->notify(new BusinessWelcomeNotification());
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send business welcome email: ' . $e->getMessage());
        }
    }
}
