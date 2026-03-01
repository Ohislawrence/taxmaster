<?php

namespace App\Services\TaxCalculators;

use App\Models\TaxType;
use App\Models\TaxReturn;

abstract class BaseTaxCalculator
{
    protected TaxType $taxType;

    public function __construct(TaxType $taxType)
    {
        $this->taxType = $taxType;
    }

    /**
     * Calculate tax based on income and parameters
     * 
     * @param float $grossIncome
     * @param array $params Additional parameters (reliefs, deductions, etc.)
     * @return array Tax calculation breakdown
     */
    abstract public function calculate(float $grossIncome, array $params = []): array;

    /**
     * Get tax type code
     */
    public function getTaxTypeCode(): string
    {
        return $this->taxType->code;
    }

    /**
     * Format calculation result
     */
    protected function formatResult(array $data): array
    {
        return array_merge([
            'tax_type' => $this->taxType->code,
            'tax_type_name' => $this->taxType->name,
            'calculation_method' => $this->taxType->calculation_method,
            'calculated_at' => now()->toIso8601String(),
        ], $data);
    }

    /**
     * Round currency to 2 decimal places
     */
    protected function roundCurrency(float $amount): float
    {
        return round($amount, 2);
    }
}
