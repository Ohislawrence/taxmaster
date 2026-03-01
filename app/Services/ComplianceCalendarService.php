<?php

namespace App\Services;

use App\Models\ComplianceDeadline;
use App\Models\Business;
use Carbon\Carbon;

class ComplianceCalendarService
{
    protected $deadlineTypes = [
        'VAT' => [
            'frequency' => 'monthly',
            'day' => 21,
            'description' => 'VAT Return and Payment',
            'forms' => ['FIRS VAT Form 002'],
            'applies_to' => ['all'],
        ],
        'WHT' => [
            'frequency' => 'monthly',
            'day' => 21,
            'description' => 'Withholding Tax Remittance',
            'forms' => ['WHT Schedule'],
            'applies_to' => ['all'],
        ],
        'PAYE' => [
            'frequency' => 'monthly',
            'day' => 10,
            'description' => 'PAYE/Income Tax Payment',
            'forms' => ['PAYE Declaration'],
            'applies_to' => ['with_staff'],
        ],
        'CIT' => [
            'frequency' => 'annual',
            'months_after_year_end' => 6,
            'description' => 'Corporate Income Tax Return',
            'forms' => ['CIT Form', 'Financial Statements', 'Audit Report'],
            'applies_to' => ['all'],
        ],
        'CAC_ANNUAL' => [
            'frequency' => 'annual',
            'based_on' => 'incorporation_date',
            'description' => 'CAC Annual Return Filing',
            'forms' => ['Form AR', 'Notice of Situation'],
            'applies_to' => ['all'],
        ],
        'ITF' => [
            'frequency' => 'monthly',
            'day' => 30,
            'description' => 'Industrial Training Fund (1% of payroll)',
            'forms' => ['ITF Remittance Schedule'],
            'applies_to' => ['with_staff'],
        ],
        'PENCOM' => [
            'frequency' => 'monthly',
            'day' => 10,
            'description' => 'Pension Contribution (8% employer + 8% employee)',
            'forms' => ['PENCOM Schedule'],
            'applies_to' => ['with_staff'],
        ],
        'NSITF' => [
            'frequency' => 'monthly',
            'day' => 30,
            'description' => 'NSITF Contribution (1% of payroll)',
            'forms' => ['NSITF Schedule'],
            'applies_to' => ['with_staff'],
        ],
    ];

    /**
     * Generate deadlines for a business for next 12 months
     */
    public function generateDeadlines(Business $business): int
    {
        $count = 0;
        $startDate = now();
        $endDate = now()->addYear();

        foreach ($this->deadlineTypes as $type => $config) {
            if (!$this->appliesToBusiness($business, $config['applies_to'])) {
                continue;
            }

            if ($config['frequency'] === 'monthly') {
                $count += $this->generateMonthlyDeadlines($business, $type, $config, $startDate, $endDate);
            } elseif ($config['frequency'] === 'annual') {
                $count += $this->generateAnnualDeadline($business, $type, $config);
            }
        }

        return $count;
    }

    /**
     * Check if deadline applies to business
     */
    protected function appliesToBusiness(Business $business, array $appliesTo): bool
    {
        if (in_array('all', $appliesTo)) {
            return true;
        }

        if (in_array('with_staff', $appliesTo)) {
            return $business->has_staff || $business->staff_count > 0;
        }

        return false;
    }

    /**
     * Generate monthly deadlines
     */
    protected function generateMonthlyDeadlines(Business $business, string $type, array $config, Carbon $start, Carbon $end): int
    {
        $count = 0;
        $current = $start->copy();

        while ($current <= $end) {
            $dueDate = $current->copy()->day($config['day']);

            // If deadline is in the past for current month, skip to next month
            if ($dueDate < now()) {
                $current->addMonth();
                continue;
            }

            ComplianceDeadline::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'deadline_type' => $type,
                    'period' => $current->format('Y-m'),
                ],
                [
                    'description' => $config['description'],
                    'due_date' => $dueDate,
                    'frequency' => 'monthly',
                    'required_documents' => $config['forms'],
                    'status' => 'pending',
                ]
            );

            $count++;
            $current->addMonth();
        }

        return $count;
    }

    /**
     * Generate annual deadline
     */
    protected function generateAnnualDeadline(Business $business, string $type, array $config): int
    {
        if (isset($config['based_on']) && $config['based_on'] === 'incorporation_date') {
            if (!$business->incorporation_date) {
                return 0; // Skip if no incorporation date
            }
            $dueDate = Carbon::parse($business->incorporation_date)
                ->addYear()
                ->startOfDay();
        } else {
            // CIT - 6 months after accounting year-end
            $yearEnd = $business->accounting_year_end
                ? Carbon::parse($business->accounting_year_end)
                : Carbon::parse($business->created_at)->endOfYear();
            $dueDate = $yearEnd->copy()->addMonths($config['months_after_year_end'] ?? 6);
        }

        // Only create if due date is within next 12 months
        if ($dueDate > now() && $dueDate <= now()->addYear()) {
            ComplianceDeadline::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'deadline_type' => $type,
                    'period' => $dueDate->format('Y'),
                ],
                [
                    'description' => $config['description'],
                    'due_date' => $dueDate,
                    'frequency' => 'annual',
                    'required_documents' => $config['forms'],
                    'status' => 'pending',
                ]
            );
            return 1;
        }

        return 0;
    }

    /**
     * Get upcoming deadlines
     */
    public function getUpcomingDeadlines(Business $business, int $days = 30): array
    {
        return ComplianceDeadline::where('business_id', $business->id)
            ->where('status', 'pending')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays($days))
            ->orderBy('due_date')
            ->get()
            ->toArray();
    }

    /**
     * Mark deadline as completed
     */
    public function markCompleted(ComplianceDeadline $deadline, ?string $notes = null, ?array $attachments = null): void
    {
        $deadline->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $notes,
            'attachments' => $attachments,
        ]);
    }

    /**
     * Get overdue deadlines
     */
    public function getOverdueDeadlines(Business $business): array
    {
        return ComplianceDeadline::where('business_id', $business->id)
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->orderBy('due_date')
            ->get()
            ->toArray();
    }

    /**
     * Update overdue statuses
     */
    public function updateOverdueStatuses(): int
    {
        return ComplianceDeadline::where('status', 'pending')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);
    }

    /**
     * Get deadlines needing reminders
     */
    public function getDeadlinesNeedingReminders()
    {
        return ComplianceDeadline::needsReminder()->get();
    }
}
