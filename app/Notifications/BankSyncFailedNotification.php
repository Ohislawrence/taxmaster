<?php

namespace App\Notifications;

use App\Models\BankAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BankSyncFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BankAccount $bankAccount,
        public string $error,
        public int $attempts
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bank Sync Failed - Action Required')
            ->greeting("Hello {$notifiable->name}")
            ->line("Automatic sync failed for your {$bankAccount->bank_name} account ({$bankAccount->masked_account_number}).")
            ->line("**Error:** {$this->error}")
            ->line("**Attempt:** {$this->attempts} of 3")
            ->action('Try Manual Sync', route('business.banks.index'))
            ->line('If this issue persists, please contact support.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'bank_account_id' => $this->bankAccount->id,
            'bank_account_name' => $this->bankAccount->bank_name,
            'account_number' => $this->bankAccount->masked_account_number,
            'error' => $this->error,
            'attempts' => $this->attempts,
        ];
    }
}
