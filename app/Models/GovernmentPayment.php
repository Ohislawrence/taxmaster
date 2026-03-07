<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GovernmentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'tax_type',
        'return_id',
        'return_type',
        'period',
        'amount',
        'payment_method',
        'remita_rrr',
        'payment_date',
        'receipt_path',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the business that owns the payment
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the associated tax return (polymorphic)
     */
    public function return(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get tax type label
     */
    public function getTaxTypeLabelAttribute(): string
    {
        return match($this->tax_type) {
            'VAT' => 'VAT',
            'PAYE' => 'PAYE',
            'WHT' => 'Withholding Tax',
            'CIT' => 'Company Income Tax',
            'CGT' => 'Capital Gains Tax',
            'STAMP_DUTY' => 'Stamp Duty',
            default => 'Unknown',
        };
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'remita' => 'Remita',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'cheque' => 'Cheque',
            default => 'Unknown',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            default => 'Unknown',
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'processing' => 'blue',
            'completed' => 'green',
            'failed' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get formatted payment date
     */
    public function getPaymentDateFormattedAttribute(): ?string
    {
        if (!$this->payment_date) {
            return null;
        }
        return \Carbon\Carbon::parse($this->payment_date)->format('M d, Y');
    }

    /**
     * Scope for filtering by tax type
     */
    public function scopeTaxType($query, string $type)
    {
        return $query->where('tax_type', $type);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }
}
