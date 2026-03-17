<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\BusinessInvitation;

class BusinessInvitationNotification extends Notification
{
    protected BusinessInvitation $invite;
    protected string $rawToken;

    public function __construct(BusinessInvitation $invite, string $rawToken)
    {
        $this->invite = $invite;
        $this->rawToken = $rawToken;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $link = url('/register') . '?invite=' . $this->rawToken . '&email=' . urlencode($this->invite->email);

        return (new MailMessage)
            ->subject('You have been invited to own a business on TaxMaster')
            ->greeting('Hello')
            ->line("You've been invited to claim ownership of the business: {$this->invite->business->name}.")
            ->action('Accept invitation and register', $link)
            ->line('If you did not expect this invitation, you may ignore this email.')
            ->salutation('Regards, TaxMaster');
    }
}
