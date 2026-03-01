<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxDeadline extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_type_id',
        'period_type',
        'due_day',
        'due_month',
        'grace_days',
        'late_filing_penalty_rate',
        'interest_rate_per_annum',
        'is_active',
        'description',
    ];

    protected $casts = [
        'late_filing_penalty_rate' => 'decimal:2',
        'interest_rate_per_annum' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tax type this deadline belongs to
     */
    public function taxType(): BelongsTo
    {
        return $this->belongsTo(TaxType::class);
    }

    /**
     * Scope: Get active deadlines
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate next due date from a given date
     */
    public function getNextDueDate(\DateTime $fromDate = null): \DateTime
    {
        $date = $fromDate ?? new \DateTime();

        switch ($this->period_type) {
            case 'monthly':
                $dueDate = new \DateTime($date->format('Y-m-') . str_pad($this->due_day, 2, '0', STR_PAD_LEFT));
                if ($dueDate <= $date) {
                    $dueDate->modify('+1 month');
                }
                break;

            case 'quarterly':
                // Calculate next quarter end
                $currentQuarter = ceil($date->format('n') / 3);
                $nextQuarter = $currentQuarter + 1;
                if ($nextQuarter > 4) {
                    $nextQuarter = 1;
                    $year = (int)$date->format('Y') + 1;
                } else {
                    $year = (int)$date->format('Y');
                }
                $month = $nextQuarter * 3;
                $dueDate = new \DateTime("$year-$month-" . str_pad($this->due_day, 2, '0', STR_PAD_LEFT));
                break;

            case 'annual':
                $dueDate = new \DateTime($date->format('Y') . '-' . str_pad($this->due_month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($this->due_day, 2, '0', STR_PAD_LEFT));
                if ($dueDate <= $date) {
                    $dueDate->modify('+1 year');
                }
                break;

            default:
                $dueDate = $date;
        }

        return $dueDate;
    }

    /**
     * Calculate penalty for late filing
     */
    public function calculatePenalty(float $taxAmount, int $daysLate): float
    {
        if ($daysLate <= $this->grace_days) {
            return 0;
        }

        // Standard penalty: 10% of unpaid tax
        return $taxAmount * ($this->late_filing_penalty_rate / 100);
    }

    /**
     * Calculate interest for late payment
     */
    public function calculateInterest(float $taxAmount, int $daysLate): float
    {
        if ($daysLate <= $this->grace_days) {
            return 0;
        }

        // Interest = Principal × Rate × Time (in years)
        $years = $daysLate / 365;
        return $taxAmount * ($this->interest_rate_per_annum / 100) * $years;
    }
}
