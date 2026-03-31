<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CriticalErrorNotification extends Notification
{
    use Queueable;

    public function __construct(
        public \Throwable $exception,
        public ?string $userId = null,
        public array $context = []
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $exceptionClass = get_class($this->exception);
        $message = $this->exception->getMessage();
        $file = $this->exception->getFile();
        $line = $this->exception->getLine();
        $url = $this->context['url'] ?? 'Unknown';
        $userAgent = $this->context['user_agent'] ?? 'Unknown';

        return (new MailMessage)
            ->error()
            ->subject('🚨 Critical Error on ' . config('app.name'))
            ->greeting('Critical Error Alert!')
            ->line("**Error Type:** {$exceptionClass}")
            ->line("**Message:** " . Str::limit($message, 200))
            ->line("**File:** {$file}:{$line}")
            ->line("**URL:** {$url}")
            ->line("**User:** " . ($this->userId ? "ID: {$this->userId}" : 'Guest'))
            ->line("**User Agent:** " . Str::limit($userAgent, 100))
            ->line("**Time:** " . now()->format('Y-m-d H:i:s'))
            ->action('View in Laravel Logs', config('app.url') . '/admin/logs')
            ->line('Please investigate this issue as soon as possible.');
    }

    public function toArray($notifiable): array
    {
        return [
            'exception' => get_class($this->exception),
            'message' => $this->exception->getMessage(),
            'file' => $this->exception->getFile(),
            'line' => $this->exception->getLine(),
            'user_id' => $this->userId,
            'context' => $this->context,
        ];
    }
}
