<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'tax_return_id',
        'payment_reference',
        'paystack_reference',
        'amount',
        'payment_method',
        'status',
        'currency',
        'payment_date',
        'description',
        'paystack_response',
        'metadata',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
        'paystack_response' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the business that owns the payment
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the tax return associated with this payment
     */
    public function taxReturn(): BelongsTo
    {
        return $this->belongsTo(TaxReturn::class);
    }

    /**
     * Generate unique payment reference
     */
    public static function generateReference(): string
    {
        return 'PAY-' . strtoupper(uniqid(date('YmdHis')));
    }

    /**
     * Scope: Get completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Get pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
