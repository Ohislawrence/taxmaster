<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable)
    {
        $expiration = Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60));

        return URL::temporarySignedRoute(
            'verification.verify',
            $expiration,
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
        );
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify your TaxMaster account')
            ->greeting('Hello ' . ($notifiable->name ?? ''))
            ->line("Thanks for creating a TaxMaster account. To complete your registration, please verify your email address by clicking the button below.")
            ->action('Verify Email', $url)
            ->line('This verification link will expire in ' . Config::get('auth.verification.expire', 60) . ' minutes.')
            ->line('If you did not create an account, no further action is required.')
            ->salutation('Regards,\nThe TaxMaster Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [];
    }
}
