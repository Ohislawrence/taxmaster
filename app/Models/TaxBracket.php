<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxBracket extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_type_id',
        'min_amount',
        'max_amount',
        'rate',
        'fixed_amount',
        'order',
        'is_active',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'rate' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tax type this bracket belongs to
     */
    public function taxType(): BelongsTo
    {
        return $this->belongsTo(TaxType::class);
    }

    /**
     * Scope: Get active brackets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if an amount falls within this bracket
     */
    public function containsAmount(float $amount): bool
    {
        if ($this->max_amount === null) {
            return $amount >= $this->min_amount;
        }

        return $amount >= $this->min_amount && $amount <= $this->max_amount;
    }

    /**
     * Calculate tax for amount in this bracket
     */
    public function calculateTax(float $amount): float
    {
        $taxableInBracket = min($amount, $this->max_amount ?? $amount) - $this->min_amount;
        return ($taxableInBracket * $this->rate / 100) + $this->fixed_amount;
    }
}
