<?php

namespace App\Jobs;

use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if welcome email was already sent to avoid duplicates
        if ($this->user->welcome_email_sent_at !== null) {
            return;
        }

        // Send the welcome email
        Mail::to($this->user->email)->send(new WelcomeEmail($this->user));

        // Mark as sent
        $this->user->update(['welcome_email_sent_at' => now()]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Log the failure or send notification
        Log::error('Welcome email failed for user: ' . $this->user->id, [
            'error' => $exception->getMessage(),
        ]);
    }
}
