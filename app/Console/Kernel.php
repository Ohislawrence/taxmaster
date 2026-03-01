<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Daily encrypted backup at 2 AM
        $schedule->command('backup:run-daily')
            ->dailyAt('02:00')
            ->runInBackground()
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Daily backup failed');
            })
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('Daily backup completed successfully');
            });

        // Check subscription renewals daily at 3 AM
        $schedule->call(function () {
            app(\App\Services\SubscriptionService::class)->processRenewals();
        })->dailyAt('03:00')->name('process-subscription-renewals');

        // Monitor backup health
        $schedule->command('backup:monitor')
            ->daily()
            ->runInBackground();

        // Generate compliance reminders with AI suggestions daily at 9 AM
        $schedule->command('compliance:generate-reminders')
            ->dailyAt('09:00')
            ->runInBackground()
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Compliance reminders generation failed');
            });

        // Process payment failures with AI recovery suggestions daily at 10 AM
        $schedule->call(function () {
            app(\App\Services\PaymentRecoveryService::class)->processFailedPayments();
        })->dailyAt('10:00')->name('payment-recovery-suggestions');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
