<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Events\TransactionCreated;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_account_id',
        'business_id',
        'mono_transaction_id',
        'type',
        'amount',
        'currency',
        'description',
        'counterparty',
        'transaction_date',
        'balance',
        'category',
        'sub_category',
        'confidence',
        'vat_applicable',
        'is_business_expense',
        'user_verified',
        'notes',
        'attachments',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'confidence' => 'decimal:2',
        'transaction_date' => 'datetime',
        'vat_applicable' => 'boolean',
        'is_business_expense' => 'boolean',
        'user_verified' => 'boolean',
        'attachments' => 'array',
        'meta' => 'encrypted:array',
        'data_encrypted' => 'boolean',
    ];

    /**
     * Events to dispatch
     */
    protected $dispatchesEvents = [
        'created' => TransactionCreated::class,
    ];

    /**
     * Get the bank account that owns the transaction
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Get the business that owns the transaction
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get formatted amount with sign
     */
    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->type === 'credit' ? '+' : '-';
        return $sign . ' ₦' . number_format($this->amount, 2);
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        $labels = [
            'VAT_OUTPUT' => 'VATable Sales',
            'VAT_INPUT' => 'VATable Expenses',
            'EXEMPT_SALES' => 'VAT-exempt Sales',
            'SALARY_PAYE' => 'Salaries & PAYE',
            'RENT' => 'Rent & Facilities',
            'PROFESSIONAL' => 'Professional Fees',
            'MARKETING' => 'Marketing',
            'UTILITIES' => 'Utilities',
            'TRANSPORT' => 'Transport',
            'IT_SOFTWARE' => 'IT & Software',
            'RAW_MATERIALS' => 'Raw Materials',
            'OTHER_EXPENSES' => 'Other Expenses',
            'VAT_PAYMENT' => 'VAT Payment',
            'PAYE_PAYMENT' => 'PAYE Payment',
            'WHT_PAYMENT' => 'WHT Payment',
            'CIT_PAYMENT' => 'CIT Payment',
            'PERSONAL' => 'Personal',
            'UNCATEGORIZED' => 'Uncategorized',
        ];

        return $labels[$this->sub_category ?? ''] ?? $labels[$this->category ?? ''] ?? 'Unknown';
    }

    /**
     * Get confidence label
     */
    public function getConfidenceLabelAttribute(): string
    {
        if (!$this->confidence) return 'N/A';

        if ($this->confidence >= 0.9) return 'High';
        if ($this->confidence >= 0.7) return 'Medium';
        return 'Low';
    }

    /**
     * Scope: Uncategorized transactions
     */
    public function scopeUncategorized($query)
    {
        return $query->whereNull('category')
            ->orWhere('category', 'UNCATEGORIZED');
    }

    /**
     * Scope: VAT applicable transactions
     */
    public function scopeVatApplicable($query)
    {
        return $query->where('vat_applicable', true);
    }

    /**
     * Scope: Revenue transactions
     */
    public function scopeRevenue($query)
    {
        return $query->where('category', 'REVENUE');
    }

    /**
     * Scope: Expense transactions
     */
    public function scopeExpenses($query)
    {
        return $query->where('category', 'EXPENSES');
    }

    /**
     * Scope: By period
     */
    public function scopePeriod($query, string $period)
    {
        // Period format: 2026-02
        $date = \Carbon\Carbon::createFromFormat('Y-m', $period);
        return $query->whereBetween('transaction_date', [
            $date->startOfMonth(),
            $date->copy()->endOfMonth(),
        ]);
    }

    /**
     * Scope: Needs review
     */
    public function scopeNeedsReview($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('category')
              ->orWhere('confidence', '<', 0.7)
              ->orWhere('user_verified', false);
        });
    }
}
