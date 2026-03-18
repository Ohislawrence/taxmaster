<?php

namespace App\Services;

use App\Models\Business;
use App\Models\ComplianceReminder;
use App\Models\TaxDeadline;
use App\Models\TaxReturn;
use App\Models\TaxType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ComplianceService
{
    /**
     * Get upcoming tax deadlines for a business
     */
    public function getUpcomingDeadlines(Business $business, int $daysAhead = 30): Collection
    {
        $deadlines = [];
        $taxTypes = TaxType::active()->with('deadlines')->get();

        foreach ($taxTypes as $taxType) {
            foreach ($taxType->deadlines as $deadline) {
                $nextDueDate = $deadline->getNextDueDate();
                $daysUntilDue = now()->diffInDays($nextDueDate, false);

                if ($daysUntilDue >= 0 && $daysUntilDue <= $daysAhead) {
                    $deadlines[] = [
                        'tax_type' => $taxType,
                        'deadline' => $deadline,
                        'due_date' => $nextDueDate,
                        'days_until_due' => $daysUntilDue,
                        'period_type' => $deadline->period_type,
                    ];
                }
            }
        }

        return collect($deadlines)->sortBy('due_date');
    }

    /**
     * Generate compliance reminders for a business
     */
    public function generateReminders(Business $business): int
    {
        $upcomingDeadlines = $this->getUpcomingDeadlines($business, 30);
        $remindersCreated = 0;

        foreach ($upcomingDeadlines as $item) {
            $dueDate = Carbon::instance($item['due_date']);
            $reminderDates = $this->getReminderDates($dueDate);

            foreach ($reminderDates as $reminderDate => $reminderType) {
                // Check if reminder already exists
                $exists = ComplianceReminder::where('business_id', $business->id)
                    ->where('tax_type_id', $item['tax_type']->id)
                    ->where('reminder_date', $reminderDate)
                    ->where('due_date', $dueDate->toDateString())
                    ->exists();

                if (!$exists) {
                    ComplianceReminder::create([
                        'business_id' => $business->id,
                        'tax_type_id' => $item['tax_type']->id,
                        'reminder_type' => $reminderType,
                        'due_date' => $dueDate,
                        'reminder_date' => $reminderDate,
                        'status' => 'pending',
                        'notification_channel' => 'email', // Default to email
                        'message' => $this->generateReminderMessage($item['tax_type'], $dueDate, $reminderType),
                    ]);

                    $remindersCreated++;
                }
            }
        }

        return $remindersCreated;
    }

    /**
     * Get reminder dates for a due date
     */
    protected function getReminderDates(Carbon $dueDate): array
    {
        return [
            $dueDate->copy()->subDays(7)->toDateString() => 'upcoming', // 7 days before
            $dueDate->copy()->subDays(3)->toDateString() => 'upcoming', // 3 days before
            $dueDate->copy()->subDays(1)->toDateString() => 'upcoming', // 1 day before
            $dueDate->toDateString() => 'due_today', // On due date
        ];
    }

    /**
     * Generate reminder message
     */
    protected function generateReminderMessage(TaxType $taxType, Carbon $dueDate, string $reminderType): string
    {
        $messages = [
            'upcoming' => "Reminder: Your {$taxType->name} filing is due on {$dueDate->format('F j, Y')}. Please ensure you file on time to avoid penalties.",
            'due_today' => "URGENT: Your {$taxType->name} filing is due today ({$dueDate->format('F j, Y')}). File immediately to avoid late fees.",
            'overdue' => "OVERDUE: Your {$taxType->name} filing was due on {$dueDate->format('F j, Y')}. Penalties may apply. Please file immediately.",
        ];

        return $messages[$reminderType] ?? $messages['upcoming'];
    }

    /**
     * Calculate penalties and interest for overdue tax returns
     */
    public function calculatePenaltiesAndInterest(TaxReturn $taxReturn): array
    {
        if (!$taxReturn->isOverdue()) {
            return [
                'penalties' => 0,
                'interest' => 0,
                'total' => 0,
                'days_overdue' => 0,
            ];
        }

        $deadline = TaxDeadline::where('tax_type_id', $taxReturn->tax_type_id)
            ->where('period_type', $taxReturn->return_type)
            ->first();

        if (!$deadline) {
            return [
                'penalties' => 0,
                'interest' => 0,
                'total' => 0,
                'days_overdue' => 0,
            ];
        }

        $daysOverdue = now()->diffInDays($taxReturn->due_date);
        $unpaidTax = $taxReturn->balance;

        $penalty = $deadline->calculatePenalty($unpaidTax, $daysOverdue);
        $interest = $deadline->calculateInterest($unpaidTax, $daysOverdue);

        return [
            'penalties' => round($penalty, 2),
            'interest' => round($interest, 2),
            'total' => round($penalty + $interest, 2),
            'days_overdue' => $daysOverdue,
        ];
    }

    /**
     * Get compliance status for a business
     */
    public function getComplianceStatus(Business $business): array
    {
        $taxReturns = $business->taxReturns()->with('taxType')->get();

        $statusCounts = [
            'paid' => 0,
            'pending' => 0,
            'overdue' => 0,
            'upcoming' => 0,
        ];

        $totalPenalties = 0;
        $overdueReturns = [];

        foreach ($taxReturns as $return) {
            if ($return->status === 'paid') {
                $statusCounts['paid']++;
            } elseif ($return->isOverdue()) {
                $statusCounts['overdue']++;
                $penalties = $this->calculatePenaltiesAndInterest($return);
                $totalPenalties += $penalties['total'];
                $overdueReturns[] = [
                    'return' => $return,
                    'penalties' => $penalties,
                ];
            } else {
                $statusCounts['pending']++;
            }
        }

        // Get upcoming deadlines
        $upcomingDeadlines = $this->getUpcomingDeadlines($business, 30);
        $statusCounts['upcoming'] = $upcomingDeadlines->count();

        $complianceRate = $this->calculateComplianceRate($statusCounts);
        $risk = $this->computeComplianceRisk($statusCounts, $complianceRate, $totalPenalties, $upcomingDeadlines->count());

        return [
            'status_counts' => $statusCounts,
            'total_estimated_penalties' => round($totalPenalties, 2),
            'overdue_returns' => $overdueReturns,
            'upcoming_deadlines' => $upcomingDeadlines,
            'compliance_rate' => $complianceRate,
            'risk' => $risk,
        ];
    }

    /**
     * Get cached compliance status for a business.
     * Cache key: `compliance_status:business:{id}`
     */
    public function getComplianceStatusCached(Business $business, int $ttlSeconds = 600): array
    {
        $key = "compliance_status:business:{$business->id}";
        return Cache::remember($key, $ttlSeconds, function () use ($business) {
            return $this->getComplianceStatus($business);
        });
    }

    /**
     * Clear cached compliance status for a business.
     */
    public function clearComplianceStatusCache(Business $business): void
    {
        $key = "compliance_status:business:{$business->id}";
        Cache::forget($key);
    }

    /**
     * Compute a simple compliance risk score and category.
     * Returns array with `level` (compliant|at_risk|high_risk), `emoji`, `score` and `reasons`.
     */
    protected function computeComplianceRisk(array $statusCounts, float $complianceRate, float $totalPenalties, int $upcomingCount): array
    {
        $points = 0;

        // Missed deadlines (overdue returns)
        $overdue = $statusCounts['overdue'] ?? 0;
        $points += min($overdue, 10) * 3;

        // Low compliance rate is a strong signal
        if ($complianceRate < 80) {
            $points += 6;
        } elseif ($complianceRate < 95) {
            $points += 2;
        }

        // Upcoming load: many upcoming deadlines increases risk
        if ($upcomingCount >= 5) {
            $points += 3;
        } elseif ($upcomingCount >= 2) {
            $points += 1;
        }

        // Estimated penalties increase risk proportionally (coarse)
        if ($totalPenalties > 0) {
            $points += min(10, (int) floor($totalPenalties / 10000));
        }

        // Determine level
        if ($points <= 3) {
            $level = 'compliant';
            $emoji = '✅';
        } elseif ($points <= 8) {
            $level = 'at_risk';
            $emoji = '⚠️';
        } else {
            $level = 'high_risk';
            $emoji = '🚨';
        }

        $reasons = [];
        if ($overdue > 0) {
            $reasons[] = "{$overdue} overdue return(s)";
        }
        if ($upcomingCount > 0) {
            $reasons[] = "{$upcomingCount} upcoming deadline(s)";
        }
        if ($totalPenalties > 0) {
            $reasons[] = 'Estimated penalties: ₦' . number_format($totalPenalties, 2);
        }
        $reasons[] = "Compliance rate: {$complianceRate}%";

        return [
            'level' => $level,
            'emoji' => $emoji,
            'score' => $points,
            'reasons' => $reasons,
        ];
    }

    /**
     * Calculate compliance rate percentage
     */
    protected function calculateComplianceRate(array $statusCounts): float
    {
        $total = array_sum($statusCounts);
        if ($total === 0) {
            return 100;
        }

        $compliant = $statusCounts['paid'];
        return round(($compliant / $total) * 100, 1);
    }

    /**
     * Mark overdue returns and create overdue reminders
     */
    public function processOverdueReturns(): int
    {
        $overdueReturns = TaxReturn::where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->get();

        $remindersCreated = 0;

        foreach ($overdueReturns as $return) {
            // Create overdue reminder if not exists
            $exists = ComplianceReminder::where('tax_return_id', $return->id)
                ->where('reminder_type', 'overdue')
                ->where('status', 'pending')
                ->exists();

            if (!$exists) {
                $penalties = $this->calculatePenaltiesAndInterest($return);

                ComplianceReminder::create([
                    'business_id' => $return->business_id,
                    'tax_type_id' => $return->tax_type_id,
                    'tax_return_id' => $return->id,
                    'reminder_type' => 'overdue',
                    'due_date' => $return->due_date,
                    'reminder_date' => now(),
                    'status' => 'pending',
                    'notification_channel' => 'email',
                    'message' => "OVERDUE: Tax return #{$return->id} is overdue. Estimated penalties: ₦" . number_format($penalties['total'], 2),
                ]);

                $remindersCreated++;
            }
        }

        return $remindersCreated;
    }
}
