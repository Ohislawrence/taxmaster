<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StateTaxConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'state_code',
        'state_name',
        'tax_type_id',
        'rate_override',
        'minimum_tax',
        'exemptions',
        'additional_levies',
        'is_active',
    ];

    protected $casts = [
        'rate_override' => 'decimal:2',
        'minimum_tax' => 'decimal:2',
        'exemptions' => 'array',
        'additional_levies' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tax type this config belongs to
     */
    public function taxType(): BelongsTo
    {
        return $this->belongsTo(TaxType::class);
    }

    /**
     * Scope: Get active configs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get by state code
     */
    public function scopeByState($query, string $stateCode)
    {
        return $query->where('state_code', strtoupper($stateCode));
    }

    /**
     * Get effective tax rate (override or default)
     */
    public function getEffectiveRate(): ?float
    {
        return $this->rate_override ?? $this->taxType->flat_rate;
    }
}
