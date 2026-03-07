<?php

namespace App\Models;

use App\Traits\TracksStatusChanges;
use App\Traits\HasStandardStatus;
use App\Traits\HasTaxAuthority;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CitReturn extends Model
{
    use SoftDeletes, TracksStatusChanges, HasStandardStatus, HasTaxAuthority;

    protected $table = 'cit_returns';

    protected $fillable = [
        'business_id',
        'period',
        'return_type',
        'submitted_at',
        'due_date',
        'gross_profit',
        'revenue',
        'cost_of_goods_sold',
        'depreciation',
        'amortization',
        'other_add_backs',
        'capital_allowances',
        'allowable_expenses',
        'other_deductions',
        'taxable_income',
        'cit_rate',
        'cit_payable',
        'turnover',
        'gross_assets',
        'paid_up_capital',
        'minimum_tax_amount',
        'tax_due',
        'advance_tax',
        'withholding_tax',
        'total_credits',
        'balance_due',
        'balance_refund',
        'late_filing_penalty',
        'payment_interest',
        'status',
        'tax_authority',
        'firs_reference',
        'form_a_reference',
        'notes',
        'attachments',
        'calculation_details',
        'form_data',
        'filed_at',
        'paid_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'gross_profit' => 'decimal:2',
        'revenue' => 'decimal:2',
        'cost_of_goods_sold' => 'decimal:2',
        'depreciation' => 'decimal:2',
        'amortization' => 'decimal:2',
        'other_add_backs' => 'decimal:2',
        'capital_allowances' => 'decimal:2',
        'allowable_expenses' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'taxable_income' => 'decimal:2',
        'cit_rate' => 'decimal:4',
        'cit_payable' => 'decimal:2',
        'turnover' => 'decimal:2',
        'gross_assets' => 'decimal:2',
        'paid_up_capital' => 'decimal:2',
        'minimum_tax_amount' => 'decimal:2',
        'tax_due' => 'decimal:2',
        'advance_tax' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'total_credits' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'balance_refund' => 'decimal:2',
        'late_filing_penalty' => 'decimal:2',
        'payment_interest' => 'decimal:2',
        'submitted_at' => 'datetime',
        'due_date' => 'date',
        'filed_at' => 'datetime',
        'paid_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'attachments' => 'array',
        'calculation_details' => 'array',
        'form_data' => 'array',
    ];

    /**
     * Get the business that owns this CIT return
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the user who reviewed this return
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get government payments for this CIT return
     */
    public function governmentPayments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(GovernmentPayment::class, 'return');
    }

    /**
     * Calculate total income from revenue and COGS
     */
    public function calculateGrossProfit(): void
    {
        if ($this->revenue && $this->cost_of_goods_sold !== null) {
            $this->gross_profit = $this->revenue - $this->cost_of_goods_sold;
        }
    }

    /**
     * Calculate total add-backs
     */
    public function calculateTotalAddBacks(): float
    {
        return ($this->depreciation ?? 0) +
               ($this->amortization ?? 0) +
               ($this->other_add_backs ?? 0);
    }

    /**
     * Calculate total deductions
     */
    public function calculateTotalDeductions(): float
    {
        return ($this->capital_allowances ?? 0) +
               ($this->allowable_expenses ?? 0) +
               ($this->other_deductions ?? 0);
    }

    /**
     * Calculate taxable income
     */
    public function calculateTaxableIncome(): void
    {
        $grossProfit = $this->gross_profit ?? 0;
        $addBacks = $this->calculateTotalAddBacks();
        $deductions = $this->calculateTotalDeductions();

        $this->taxable_income = max(0, $grossProfit + $addBacks - $deductions);
    }

    /**
     * Determine CIT rate based on turnover (Finance Act 2019)
     * Small: turnover < ₦25M = 0%
     * Medium: ₦25M – ₦100M = 20%
     * Large: > ₦100M = 30%
     */
    public function determineCITRate(): float
    {
        // If cit_rate was explicitly set (e.g. by controller), use it
        if ($this->cit_rate !== null && $this->cit_rate > 0) {
            return (float) $this->cit_rate;
        }

        $turnover = (float) ($this->turnover ?? 0);

        if ($turnover < 25000000) {
            return 0;
        } elseif ($turnover <= 100000000) {
            return 0.20;
        }

        return 0.30;
    }

    /**
     * Calculate CIT payable using Nigerian rate tiers
     */
    public function calculateCITPayable(): void
    {
        $this->cit_rate = $this->determineCITRate();
        $this->cit_payable = ($this->taxable_income ?? 0) * $this->cit_rate;
    }

    /**
     * Calculate minimum tax (0.5% of turnover or 0.5% of gross assets)
     */
    public function calculateMinimumTax(): void
    {
        $turnoverMinTax = ($this->turnover ?? 0) * 0.005;
        $assetsMinTax = ($this->gross_assets ?? 0) * 0.005;

        // Take the higher of the two
        $calculatedMinTax = max($turnoverMinTax, $assetsMinTax);

        // Also consider 0.25% of paid-up capital
        if ($this->paid_up_capital) {
            $calculatedMinTax = max($calculatedMinTax, $this->paid_up_capital * 0.0025);
        }

        $this->minimum_tax_amount = $calculatedMinTax;
    }

    /**
     * Calculate tax due (higher of CIT or minimum tax)
     * Small companies (turnover < ₦25M) are exempt from minimum tax
     */
    public function calculateTaxDue(): void
    {
        $this->calculateCITPayable();
        $this->calculateMinimumTax();

        $turnover = (float) ($this->turnover ?? 0);

        // Small companies pay 0% CIT and are exempt from minimum tax
        if ($turnover < 25000000) {
            $this->tax_due = 0;
            $this->minimum_tax_amount = 0;
        } else {
            $this->tax_due = max(
                $this->cit_payable ?? 0,
                $this->minimum_tax_amount ?? 0
            );
        }
    }

    /**
     * Calculate balance after credits
     */
    public function calculateBalance(): void
    {
        $totalCredits = ($this->advance_tax ?? 0) +
                       ($this->withholding_tax ?? 0) +
                       ($this->total_credits ?? 0);

        $totalTax = ($this->tax_due ?? 0) +
                   ($this->late_filing_penalty ?? 0) +
                   ($this->payment_interest ?? 0);

        $balance = $totalTax - $totalCredits;

        if ($balance > 0) {
            $this->balance_due = $balance;
            $this->balance_refund = null;
        } else if ($balance < 0) {
            $this->balance_refund = abs($balance);
            $this->balance_due = null;
        } else {
            $this->balance_due = null;
            $this->balance_refund = null;
        }
    }

    /**
     * Perform full calculation
     */
    public function performCalculations(): void
    {
        $this->calculateGrossProfit();
        $this->calculateTaxableIncome();
        $this->calculateTaxDue();
        $this->calculateBalance();
    }

    /**
     * Mark as submitted
     */
    public function markAsSubmitted(): void
    {
        $this->status = 'submitted';
        $this->submitted_at = now();
        $this->filed_at = now();
        $this->save();
    }

    /**
     * Mark as accepted by FIRS
     */
    public function markAsAccepted(string $firsReference): void
    {
        $this->status = 'accepted';
        $this->firs_reference = $firsReference;
        $this->save();
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(): void
    {
        $this->status = 'paid';
        $this->paid_at = now();
        $this->save();
    }

    /**
     * Check if payment is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date && now()->isAfter($this->due_date) &&
               $this->status !== 'paid' && $this->status !== 'accepted';
    }

    /**
     * Get calculation summary for display
     */
    public function getCalculationSummary(): array
    {
        return [
            'gross_profit' => $this->gross_profit,
            'add_backs' => $this->calculateTotalAddBacks(),
            'deductions' => $this->calculateTotalDeductions(),
            'taxable_income' => $this->taxable_income,
            'cit_rate' => $this->cit_rate,
            'cit_payable' => $this->cit_payable,
            'minimum_tax' => $this->minimum_tax_amount,
            'tax_due' => $this->tax_due,
            'advance_tax' => $this->advance_tax,
            'withholding_tax' => $this->withholding_tax,
            'total_credits' => $this->total_credits,
            'balance_due' => $this->balance_due,
            'balance_refund' => $this->balance_refund,
        ];
    }
}
