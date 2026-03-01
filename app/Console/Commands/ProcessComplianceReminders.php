<?php

namespace App\Console\Commands;

use App\Jobs\SendComplianceReminder;
use App\Models\ComplianceReminder;
use Illuminate\Console\Command;

class ProcessComplianceReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'compliance:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Process and send pending compliance reminders';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing compliance reminders...');

        // Get all pending reminders due for sending
        $reminders = ComplianceReminder::pending()
            ->dueForSending()
            ->with(['business', 'taxType'])
            ->get();

        $count = $reminders->count();

        if ($count === 0) {
            $this->info('No pending reminders to send.');
            return self::SUCCESS;
        }

        $this->info("Found {$count} reminders to send.");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($reminders as $reminder) {
            SendComplianceReminder::dispatch($reminder);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Dispatched {$count} compliance reminders to queue.");

        return self::SUCCESS;
    }
}
