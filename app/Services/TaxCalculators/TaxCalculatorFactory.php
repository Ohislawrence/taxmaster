<?php

namespace App\Services\TaxCalculators;

use App\Models\TaxType;
use InvalidArgumentException;

class TaxCalculatorFactory
{
    /**
     * Create appropriate tax calculator for given tax type
     */
    public static function make(string $taxTypeCode): BaseTaxCalculator
    {
        $taxType = TaxType::where('code', $taxTypeCode)->where('is_active', true)->firstOrFail();

        return match ($taxTypeCode) {
            'paye' => new PayeTaxCalculator($taxType),
            'cit' => new CitTaxCalculator($taxType),
            'vat' => new VatTaxCalculator($taxType),
            'wht' => new WhtTaxCalculator($taxType),
            'cgt' => new CapitalGainsTaxCalculator($taxType),
            default => throw new InvalidArgumentException("No calculator available for tax type: {$taxTypeCode}"),
        };
    }

    /**
     * Create calculator from TaxType model
     */
    public static function makeFromModel(TaxType $taxType): BaseTaxCalculator
    {
        return self::make($taxType->code);
    }

    /**
     * Get all available tax calculators
     */
    public static function getAvailableCalculators(): array
    {
        return [
            'paye' => 'Personal Income Tax (PAYE)',
            'cit' => 'Company Income Tax',
            'vat' => 'Value Added Tax',
            'wht' => 'Withholding Tax',
            'cgt' => 'Capital Gains Tax',
        ];
    }
}
