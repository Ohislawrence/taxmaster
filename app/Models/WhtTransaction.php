<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhtTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'transaction_date',
        'transaction_type',
        'vendor_name',
        'vendor_tin',
        'tin_validated',
        'gross_amount',
        'wht_rate',
        'original_rate',
        'is_double_rate',
        'wht_amount',
        'net_amount',
        'description',
        'payment_reference',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'tin_validated' => 'boolean',
        'gross_amount' => 'decimal:2',
        'wht_rate' => 'decimal:2',
        'original_rate' => 'decimal:2',
        'is_double_rate' => 'boolean',
        'wht_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    /**
     * Get the business that owns the transaction
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get transaction type label
     */
    public function getTransactionTypeLabelAttribute(): string
    {
        return match($this->transaction_type) {
            'dividends' => 'Dividends',
            'interest' => 'Interest',
            'rent' => 'Rent',
            'royalties' => 'Royalties',
            'commissions' => 'Commissions',
            'consultancy' => 'Consultancy',
            'contracts' => 'Contracts',
            'management_fees' => 'Management Fees',
            'directors_fees' => 'Directors Fees',
            'professional_fees' => 'Professional Fees',
            default => 'Unknown',
        };
    }

    /**
     * Scope for filtering by type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by period (YYYY-MM)
     */
    public function scopePeriod($query, string $period)
    {
        return $query->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$period]);
    }
}
