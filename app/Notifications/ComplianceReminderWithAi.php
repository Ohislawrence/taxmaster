<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ComplianceReminderWithAi extends Notification
{
    public function __construct(
        public $deadline,
        public $aiSuggestion,
        public $business
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('📋 ' . $this->deadline->name . ' - Action Required')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your business **{$this->business->name}** has an upcoming tax compliance deadline.");

        $message->line('**Deadline:** ' . $this->deadline->due_date->format('Y-m-d'));
        $message->line('**Priority:** ' . strtoupper($this->aiSuggestion['priority'] ?? 'Normal'));

        if (!empty($this->aiSuggestion['recommended_actions'])) {
            $message->line('**Recommended Actions:**');
            foreach ($this->aiSuggestion['recommended_actions'] as $action) {
                $message->line(
                    '• ' . $action['action'] .
                    ' (' . ($action['time_estimate_hours'] ?? 0) . ' hours)'
                );
            }
        }

        if (!empty($this->aiSuggestion['documents_needed'])) {
            $message->line('**Documents Needed:**');
            foreach ($this->aiSuggestion['documents_needed'] as $doc) {
                $message->line('• ' . $doc);
            }
        }

        if (!empty($this->aiSuggestion['common_mistakes'])) {
            $message->line('**Common Mistakes to Avoid:**');
            foreach ($this->aiSuggestion['common_mistakes'] as $mistake) {
                $message->line('• ' . $mistake);
            }
        }

        $message->action('View Details', route('business.compliance.show', $this->deadline->id))
            ->line('Thank you for using TaxMaster!');

        return $message;
    }

    public function toDatabase($notifiable)
    {
        return [
            'deadline_id' => $this->deadline->id,
            'deadline_name' => $this->deadline->name,
            'business_id' => $this->business->id,
            'priority' => $this->aiSuggestion['priority'] ?? 'normal',
            'due_date' => $this->deadline->due_date->toIso8601String(),
            'recommended_actions' => $this->aiSuggestion['recommended_actions'] ?? [],
            'documents_needed' => $this->aiSuggestion['documents_needed'] ?? [],
            'deadline_risk' => $this->aiSuggestion['deadline_risk'] ?? 'on_track',
        ];
    }
}
