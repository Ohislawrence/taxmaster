<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Notifications\AccountantWelcomeNotification;

class SendAccountantWelcomeEmail
{
    public function __construct()
    {
    }

    public function handle(Registered $event)
    {
        $user = $event->user;

        if (! $user) {
            return;
        }

        if ($user->role !== 'accountant') {
            return;
        }

        $user->notify(new AccountantWelcomeNotification());
    }
}
