<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'calculation_method',
        'flat_rate',
        'frequency',
        'due_day',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'flat_rate' => 'decimal:2',
        'settings' => 'array',
    ];

    /**
     * Get all tax brackets for this tax type
     */
    public function brackets(): HasMany
    {
        return $this->hasMany(TaxBracket::class)->orderBy('order');
    }

    /**
     * Get all active tax brackets
     */
    public function activeBrackets(): HasMany
    {
        return $this->brackets()->where('is_active', true);
    }

    /**
     * Get all reliefs for this tax type
     */
    public function reliefs(): HasMany
    {
        return $this->hasMany(TaxRelief::class)->orderBy('order');
    }

    /**
     * Get all active reliefs
     */
    public function activeReliefs(): HasMany
    {
        return $this->reliefs()->where('is_active', true);
    }

    /**
     * Get deadlines for this tax type
     */
    public function deadlines(): HasMany
    {
        return $this->hasMany(TaxDeadline::class);
    }

    /**
     * Get state configurations
     */
    public function stateConfigs(): HasMany
    {
        return $this->hasMany(StateTaxConfig::class);
    }

    /**
     * Scope: Get active tax types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get by code
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Check if this is a progressive tax
     */
    public function isProgressive(): bool
    {
        return $this->calculation_method === 'progressive';
    }

    /**
     * Check if this is a flat rate tax
     */
    public function isFlat(): bool
    {
        return $this->calculation_method === 'flat';
    }
}
