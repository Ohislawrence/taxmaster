<?php

namespace App\Console\Commands;

use App\Services\ComplianceService;
use App\Services\AiAutomationService;
use App\Models\Business;
use App\Models\AiSuggestion;
use App\Notifications\ComplianceReminderWithAi;
use Illuminate\Console\Command;

class GenerateComplianceReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'compliance:generate-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Generate compliance reminders for all businesses with AI suggestions';

    /**
     * Execute the console command.
     */
    public function handle(ComplianceService $complianceService, AiAutomationService $aiService): int
    {
        $this->info('Generating compliance reminders for all businesses...');

        $businesses = Business::where('status', 'active')->get();
        $totalReminders = 0;

        $bar = $this->output->createProgressBar($businesses->count());
        $bar->start();

        foreach ($businesses as $business) {
            $remindersCreated = $complianceService->generateReminders($business);
            $totalReminders += $remindersCreated;

            // Generate AI suggestions for upcoming deadlines
            if (config('ai-automation.features.smart_compliance_reminders')) {
                $daysAhead = config('ai-automation.compliance_reminders.days_before', 30);

                $upcomingDeadlines = $business->complianceDeadlines()
                    ->whereBetween('due_date', [now(), now()->addDays($daysAhead)])
                    ->where('status', '!=', 'completed')
                    ->limit(5)
                    ->get();

                foreach ($upcomingDeadlines as $deadline) {
                    try {
                        $suggestion = $aiService->generateComplianceReminder($deadline, $business);

                        if ($suggestion) {
                            AiSuggestion::updateOrCreate(
                                [
                                    'type' => 'compliance_reminder',
                                    'suggestible_type' => 'App\Models\ComplianceDeadline',
                                    'suggestible_id' => $deadline->id,
                                ],
                                [
                                    'data' => $suggestion,
                                    'confidence' => 1.0,
                                ]
                            );

                            // Notify with AI suggestions
                            if ($business->owner && $business->owner->email) {
                                $business->owner->notify(
                                    new ComplianceReminderWithAi($deadline, $suggestion, $business)
                                );
                            }
                        }
                    } catch (\Exception $e) {
                        $this->warn("AI suggestion failed for {$deadline->name}: {$e->getMessage()}");
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Also process overdue returns
        $this->info('Processing overdue returns...');
        $overdueReminders = $complianceService->processOverdueReturns();
        $totalReminders += $overdueReminders;

        $this->info("Generated {$totalReminders} compliance reminders.");

        return self::SUCCESS;
    }
}
