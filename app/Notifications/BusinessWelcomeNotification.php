<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BusinessWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Number of times to attempt the job.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        // no payload required for now
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $appUrl = config('app.url') ?: url('/');

        return (new MailMessage)
            ->subject('Welcome to TaxMaster — Getting started')
            ->greeting('Welcome to TaxMaster!')
            ->line("Thanks for signing up — we're excited to help you simplify tax compliance for your business.")
            ->line('Here are the key things to get you started quickly:')
            ->line('- Complete your business profile so returns and reminders are accurate.')
            ->line('- Connect your bank to import transactions (Mono integration).')
            ->line('- Add staff or upload payroll via CSV to enable PAYE automation.')
            ->line('- Review your tax calendar and set reminders for upcoming filings.')
            ->line('- Invite your accountant to collaborate if you need help managing filings.')
            ->action('Complete business setup', $appUrl . route('business.setup.create', [], false))
            ->line('Need help? Visit our documentation or contact support at support@taxmaster.ng.')
            ->line('Thanks — the TaxMaster team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            // no database notification payload
        ];
    }
}
