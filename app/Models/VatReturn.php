<?php

namespace App\Models;

use App\Traits\TracksStatusChanges;
use App\Traits\HasStandardStatus;
use App\Traits\HasTaxAuthority;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VATReturn extends Model
{
    use SoftDeletes, TracksStatusChanges, HasStandardStatus, HasTaxAuthority;

    protected $table = 'vat_returns';

    protected $fillable = [
        'business_id',
        'ai_workflow_id',
        'is_ai_generated',
        'period',
        'form_type',
        'reporting_period',
        'sales_turnover',
        'exempt_sales',
        'zero_rated_sales',
        'export_sales',
        'vat_on_sales',
        'purchases_turnover',
        'capital_goods_purchases',
        'services_purchases',
        'input_vat',
        'input_vat_adjustment',
        'input_credit',
        'vat_due',
        'settlement_amount',
        'settlement_type',
        'prior_month_credit',
        'advance_payment',
        'withholding_vat',
        'credit_notes_issued',
        'credit_notes_received',
        'bad_debt_relief',
        'status',
        'tax_authority',
        'due_date',
        'firs_reference',
        'notes',
        'form_data',
        'sales_schedule',
        'purchases_schedule',
        'attachments',
        'submitted_at',
        'filed_at',
        'paid_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'sales_turnover' => 'decimal:2',
        'exempt_sales' => 'decimal:2',
        'zero_rated_sales' => 'decimal:2',
        'export_sales' => 'decimal:2',
        'vat_on_sales' => 'decimal:2',
        'purchases_turnover' => 'decimal:2',
        'capital_goods_purchases' => 'decimal:2',
        'services_purchases' => 'decimal:2',
        'input_vat' => 'decimal:2',
        'input_vat_adjustment' => 'decimal:2',
        'input_credit' => 'decimal:2',
        'vat_due' => 'decimal:2',
        'settlement_amount' => 'decimal:2',
        'prior_month_credit' => 'decimal:2',
        'advance_payment' => 'decimal:2',
        'withholding_vat' => 'decimal:2',
        'credit_notes_issued' => 'decimal:2',
        'credit_notes_received' => 'decimal:2',
        'bad_debt_relief' => 'decimal:2',
        'due_date' => 'date',
        'submitted_at' => 'datetime',
        'filed_at' => 'datetime',
        'paid_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'form_data' => 'array',
        'sales_schedule' => 'array',
        'purchases_schedule' => 'array',
        'attachments' => 'array',
    ];

    protected $appends = [
        'period_label',
        'status_label',
        'output_vat',
        'net_vat',
    ];

    /**
     * Accessor: expose vat_due as net_vat for backward compatibility
     */
    public function getNetVatAttribute(): float
    {
        return $this->vat_due ?? 0;
    }

    /**
     * Accessor: get formatted period label
     */
    public function getPeriodLabelAttribute(): string
    {
        try {
            return \Carbon\Carbon::createFromFormat('Y-m', $this->period)->format('F Y');
        } catch (\Exception $e) {
            return $this->period ?? 'N/A';
        }
    }

    /**
     * Accessor: get total VAT on sales (alias for vat_on_sales)
     */
    public function getVatSalesAttribute(): float
    {
        return $this->vat_on_sales ?? 0;
    }

    /**
     * Accessor: get output VAT (alias for vat_on_sales)
     */
    public function getOutputVatAttribute(): float
    {
        return $this->vat_on_sales ?? 0;
    }

    /**
     * Accessor: get total VAT expenses (alias for input_vat)
     */
    public function getVatExpensesAttribute(): float
    {
        return $this->input_vat ?? 0;
    }

    /**
     * Accessor: get form 002 reference (alias for firs_reference)
     */
    public function getForm002ReferenceAttribute(): ?string
    {
        return $this->firs_reference;
    }

    /**
     * Accessor: get payment reference (alias for firs_reference)
     */
    public function getPaymentReferenceAttribute(): ?string
    {
        return $this->firs_reference;
    }

    /**
     * Accessor: get is_overdue status
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->isOverdue();
    }

    /**
     * Get the business that owns this VAT return
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the AI workflow that created this return
     */
    public function aiWorkflow(): BelongsTo
    {
        return $this->belongsTo(AiWorkflow::class, 'ai_workflow_id');
    }

    /**
     * Get the accountant who reviewed this return
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get government payments for this VAT return
     */
    public function governmentPayments(): MorphMany
    {
        return $this->morphMany(GovernmentPayment::class, 'return');
    }

    /**
     * Calculate total sales (including all types)
     */
    public function calculateTotalSales(): float
    {
        return ($this->sales_turnover ?? 0) +
               ($this->exempt_sales ?? 0) +
               ($this->zero_rated_sales ?? 0) +
               ($this->export_sales ?? 0);
    }

    /**
     * Calculate VAT on sales (output VAT) at 5%
     */
    public function calculateVatOnSales(): void
    {
        // Only sales_turnover attracts 5% VAT
        // Exempt, zero-rated, and exports don't have VAT
        $this->vat_on_sales = ($this->sales_turnover ?? 0) * 0.05;
    }

    /**
     * Calculate total purchases
     */
    public function calculateTotalPurchases(): float
    {
        return ($this->purchases_turnover ?? 0) +
               ($this->capital_goods_purchases ?? 0) +
               ($this->services_purchases ?? 0);
    }

    /**
     * Calculate input VAT at 5% on eligible purchases
     */
    public function calculateInputVat(): void
    {
        // Input VAT is 5% on eligible purchases
        // Capital goods and services are eligible
        $eligiblePurchases = ($this->capital_goods_purchases ?? 0) +
                           ($this->services_purchases ?? 0);

        $this->input_vat = $eligiblePurchases * 0.05;
    }

    /**
     * Calculate input credit (adjusted input VAT)
     */
    public function calculateInputCredit(): void
    {
        $inputVat = $this->input_vat ?? 0;
        $adjustment = $this->input_vat_adjustment ?? 0;
        $badDebtRelief = $this->bad_debt_relief ?? 0;
        $creditNotesReceived = ($this->credit_notes_received ?? 0) * 0.05;

        // Input credit = Input VAT + adjustments + bad debt relief + credit notes received
        $this->input_credit = max(0, $inputVat + $adjustment + $badDebtRelief + $creditNotesReceived);
    }

    /**
     * Calculate VAT due (Output VAT - Input Credit - Credit notes issued)
     */
    public function calculateVatDue(): void
    {
        $outVat = $this->vat_on_sales ?? 0;
        $inCredit = $this->input_credit ?? 0;
        $creditNotesIssued = ($this->credit_notes_issued ?? 0) * 0.05; // Reduces output VAT

        $this->vat_due = $outVat - $creditNotesIssued - $inCredit;
    }

    /**
     * Calculate settlement amount (what to pay or refund)
     */
    public function calculateSettlement(): void
    {
        $vatDue = $this->vat_due ?? 0;
        $priorCredit = $this->prior_month_credit ?? 0;
        $advancePayment = $this->advance_payment ?? 0;
        $withholdingVat = $this->withholding_vat ?? 0;

        $totalCredits = $priorCredit + $advancePayment + $withholdingVat;
        $settlement = $vatDue - $totalCredits;

        if ($settlement > 0) {
            $this->settlement_amount = $settlement;
            $this->settlement_type = 'payment';
        } elseif ($settlement < 0) {
            $this->settlement_amount = abs($settlement);
            $this->settlement_type = 'refund';
        } else {
            $this->settlement_amount = 0;
            $this->settlement_type = 'zero';
        }
    }

    /**
     * Perform all calculations in sequence
     */
    public function performCalculations(): void
    {
        $this->calculateVatOnSales();
        $this->calculateInputVat();
        $this->calculateInputCredit();
        $this->calculateVatDue();
        $this->calculateSettlement();
    }

    /**
     * Mark as submitted to FIRS
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
     * Mark refund as pending
     */
    public function markRefundPending(): void
    {
        $this->status = 'refund_pending';
        $this->save();
    }

    /**
     * Check if payment is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date && now()->isAfter($this->due_date) &&
               !in_array($this->status, ['paid', 'accepted', 'refund_pending']);
    }

    /**
     * Get calculation summary for display
     */
    public function getCalculationSummary(): array
    {
        return [
            'sales_turnover' => $this->sales_turnover,
            'exempt_sales' => $this->exempt_sales,
            'zero_rated_sales' => $this->zero_rated_sales,
            'export_sales' => $this->export_sales,
            'total_sales' => $this->calculateTotalSales(),
            'vat_on_sales' => $this->vat_on_sales,
            'purchases_turnover' => $this->purchases_turnover,
            'capital_goods_purchases' => $this->capital_goods_purchases,
            'services_purchases' => $this->services_purchases,
            'total_purchases' => $this->calculateTotalPurchases(),
            'input_vat' => $this->input_vat,
            'input_credit' => $this->input_credit,
            'credit_notes_issued' => $this->credit_notes_issued,
            'credit_notes_received' => $this->credit_notes_received,
            'bad_debt_relief' => $this->bad_debt_relief,
            'vat_due' => $this->vat_due,
            'prior_month_credit' => $this->prior_month_credit,
            'advance_payment' => $this->advance_payment,
            'withholding_vat' => $this->withholding_vat,
            'settlement_amount' => $this->settlement_amount,
            'settlement_type' => $this->settlement_type,
        ];
    }
}
