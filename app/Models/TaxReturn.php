<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'tax_type_id',
        'state_code',
        'filing_status',
        'return_type',
        'tax_period',
        'submission_date',
        'due_date',
        'gross_income',
        'deductions',
        'taxable_income',
        'total_tax_due',
        'total_tax_paid',
        'balance',
        'penalties',
        'interest',
        'total_amount_due',
        'status',
        'ai_analysis',
        'rejection_reason',
        'ai_processed_at',
        'staff_breakdown',
        'deduction_breakdown',
        'reliefs_claimed',
        'calculation_details',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'due_date' => 'date',
        'gross_income' => 'decimal:2',
        'deductions' => 'decimal:2',
        'taxable_income' => 'decimal:2',
        'total_tax_due' => 'decimal:2',
        'total_tax_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'penalties' => 'decimal:2',
        'interest' => 'decimal:2',
        'total_amount_due' => 'decimal:2',
        'ai_processed_at' => 'datetime',
        'staff_breakdown' => 'array',
        'deduction_breakdown' => 'array',
        'reliefs_claimed' => 'array',
        'calculation_details' => 'array',
    ];

    /**
     * Get the business that owns the tax return
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the tax type
     */
    public function taxType(): BelongsTo
    {
        return $this->belongsTo(TaxType::class);
    }

    /**
     * Get all payments related to this return
     */
    public function payments(): HasMany
    {
        return $this->hasMany(TaxPayment::class);
    }

    /**
     * Get compliance reminders
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(ComplianceReminder::class);
    }

    /**
     * Scope: Get returns by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Get returns by period
     */
    public function scopeByPeriod($query, $period)
    {
        return $query->where('tax_period', $period);
    }

    /**
     * Calculate balance
     */
    public function calculateBalance(): float
    {
        return $this->total_tax_due - $this->total_tax_paid;
    }

    /**
     * Is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date < now() && $this->status !== 'paid';
    }
}
