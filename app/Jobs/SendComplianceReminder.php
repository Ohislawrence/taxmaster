<?php

namespace App\Jobs;

use App\Models\ComplianceReminder;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendComplianceReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ComplianceReminder $reminder;

    /**
     * Create a new job instance.
     */
    public function __construct(ComplianceReminder $reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            $business = $this->reminder->business;
            $taxType = $this->reminder->taxType;

            // Prepare notification data
            $data = [
                'business_name' => $business->name,
                'tax_type' => $taxType->name,
                'due_date' => $this->reminder->due_date->format('F j, Y'),
                'reminder_type' => $this->reminder->reminder_type,
                'message' => $this->reminder->message,
                'days_until_due' => now()->diffInDays($this->reminder->due_date, false),
            ];

            // Send notification based on channel
            if (str_contains($this->reminder->notification_channel, 'email')) {
                $notificationService->sendEmail(
                    $business->email,
                    "Tax Filing Reminder: {$taxType->name}",
                    'emails.compliance-reminder',
                    $data
                );
            }

            if (str_contains($this->reminder->notification_channel, 'sms')) {
                $notificationService->sendSms(
                    $business->phone,
                    $this->reminder->message
                );
            }

            // Mark as sent
            $this->reminder->markAsSent();

            Log::info("Compliance reminder sent", [
                'reminder_id' => $this->reminder->id,
                'business_id' => $business->id,
                'tax_type' => $taxType->code,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send compliance reminder", [
                'reminder_id' => $this->reminder->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
