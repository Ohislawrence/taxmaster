<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AccountantWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public function __construct()
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $appUrl = config('app.url') ?: url('/');

        return (new MailMessage)
            ->subject('Welcome to TaxMaster — for Accountants')
            ->greeting('Welcome aboard!')
            ->line("Thanks for joining TaxMaster — your practice just got more efficient.")
            ->line('As an accountant you can:')
            ->line('- Manage multiple client businesses from a single dashboard')
            ->line('- Create businesses for clients and attach them to your portfolio')
            ->line('- Earn via referrals and affiliate links when clients sign up or subscribe')
            ->line('- Invite your team with role-based access and approval workflows')
            ->line('- Leverage our analytics, filing automation and AI categorisation tools')
            ->action('Open Accountant Dashboard', $appUrl . route('accountant.dashboard', [], false))
            ->line('Tip: Generate your referral link from the dashboard to start earning commissions.')
            ->line('Need help? Visit our accountant docs or contact support at support@taxmaster.ng.')
            ->line('— The TaxMaster team');
    }

    public function toArray($notifiable)
    {
        return [];
    }
}
