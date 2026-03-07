<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRelief extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_type_id',
        'code',
        'name',
        'description',
        'calculation_type',
        'value',
        'formula',
        'minimum_amount',
        'maximum_amount',
        'is_mandatory',
        'is_active',
        'order',
        'eligibility_rules',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_amount' => 'decimal:2',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
        'eligibility_rules' => 'array',
    ];

    /**
     * Get the tax type this relief belongs to
     */
    public function taxType(): BelongsTo
    {
        return $this->belongsTo(TaxType::class);
    }

    /**
     * Scope: Get active reliefs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get mandatory reliefs
     */
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * Calculate relief amount based on gross income
     */
    public function calculateAmount(float $grossIncome, array $params = []): float
    {
        $amount = 0;

        switch ($this->calculation_type) {
            case 'percentage':
                $amount = $grossIncome * ($this->value / 100);
                break;

            case 'fixed':
                $amount = $this->value;
                break;

            case 'formula':
                $amount = $this->evaluateFormula($grossIncome, $params);
                break;
        }

        // Apply minimum and maximum constraints
        if ($this->minimum_amount !== null) {
            $amount = max($amount, $this->minimum_amount);
        }

        if ($this->maximum_amount !== null) {
            $amount = min($amount, $this->maximum_amount);
        }

        return $amount;
    }

    /**
     * Evaluate formula for relief calculation
     */
    protected function evaluateFormula(float $grossIncome, array $params): float
    {
        // Special handling for CRA (Consolidated Relief Allowance)
        if ($this->code === 'cra') {
            // CRA = 20% of gross income + higher of (₦200,000 or 1% of gross income)
            // Per PITA as amended by Finance Act 2020
            $twentyPercent = $grossIncome * 0.20;
            $higherOf = max(200000, $grossIncome * 0.01);
            return $twentyPercent + $higherOf;
        }

        // For other formulas, you can implement a formula parser
        // For now, return the value field
        return $this->value ?? 0;
    }
}
