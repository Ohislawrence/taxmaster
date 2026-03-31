<?php

namespace App\Console\Commands;

use App\Jobs\SendWelcomeEmail;
use App\Models\User;
use Illuminate\Console\Command;

class SendMissingWelcomeEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:send-missing-welcome {--immediate : Send emails immediately without delay}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send welcome emails to users who registered 24+ hours ago but haven\'t received one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $immediate = $this->option('immediate');

        // Find users who:
        // 1. Registered 24+ hours ago
        // 2. Haven't received welcome email
        $users = User::whereNull('welcome_email_sent_at')
            ->where('created_at', '<=', now()->subHours(24))
            ->get();

        if ($users->isEmpty()) {
            $this->info('No users found who need welcome emails.');
            return Command::SUCCESS;
        }

        $this->info("Found {$users->count()} user(s) who need welcome emails.");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            if ($immediate) {
                // Send immediately without delay
                SendWelcomeEmail::dispatch($user);
            } else {
                // Send with small staggered delay to avoid overwhelming mail server
                SendWelcomeEmail::dispatch($user)->delay(now()->addMinutes($users->search($user) * 2));
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Welcome email jobs dispatched successfully!');

        return Command::SUCCESS;
    }
}

