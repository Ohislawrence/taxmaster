<?php

namespace App\Services\TaxCalculators;

use App\Models\TaxType;
use App\Models\TaxBracket;
use App\Models\TaxRelief;

class PayeTaxCalculator extends BaseTaxCalculator
{
    /**
     * Calculate PAYE tax
     * 
     * @param float $grossIncome Annual gross income
     * @param array $params ['reliefs' => [], 'state_code' => '', 'filing_status' => '']
     * @return array Tax calculation breakdown
     */
    public function calculate(float $grossIncome, array $params = []): array
    {
        // Step 1: Calculate total reliefs
        $reliefs = $this->calculateReliefs($grossIncome, $params['reliefs'] ?? []);
        $totalReliefs = array_sum(array_column($reliefs, 'amount'));

        // Step 2: Calculate taxable income
        $taxableIncome = max(0, $grossIncome - $totalReliefs);

        // Step 3: Apply progressive tax brackets
        $taxBreakdown = $this->applyTaxBrackets($taxableIncome);

        // Step 4: Calculate total tax
        $totalTax = array_sum(array_column($taxBreakdown, 'tax'));

        return $this->formatResult([
            'gross_income' => $this->roundCurrency($grossIncome),
            'reliefs' => $reliefs,
            'total_reliefs' => $this->roundCurrency($totalReliefs),
            'taxable_income' => $this->roundCurrency($taxableIncome),
            'tax_brackets_applied' => $taxBreakdown,
            'total_tax' => $this->roundCurrency($totalTax),
            'effective_rate' => $grossIncome > 0 ? $this->roundCurrency(($totalTax / $grossIncome) * 100) : 0,
        ]);
    }

    /**
     * Calculate all applicable reliefs
     */
    protected function calculateReliefs(float $grossIncome, array $customReliefs = []): array
    {
        $reliefs = [];
        $taxReliefs = $this->taxType->activeReliefs;

        foreach ($taxReliefs as $relief) {
            // Check if this relief should be applied
            if ($relief->is_mandatory || isset($customReliefs[$relief->code])) {
                $amount = $relief->calculateAmount($grossIncome, $customReliefs[$relief->code] ?? []);
                
                $reliefs[] = [
                    'code' => $relief->code,
                    'name' => $relief->name,
                    'amount' => $this->roundCurrency($amount),
                    'is_mandatory' => $relief->is_mandatory,
                ];
            }
        }

        return $reliefs;
    }

    /**
     * Apply progressive tax brackets
     */
    protected function applyTaxBrackets(float $taxableIncome): array
    {
        $brackets = $this->taxType->activeBrackets;
        $breakdown = [];
        $remainingIncome = $taxableIncome;
        $cumulativeTax = 0;

        foreach ($brackets as $bracket) {
            if ($remainingIncome <= 0) {
                break;
            }

            $taxableInBracket = 0;
            $taxInBracket = 0;

            if ($bracket->max_amount === null) {
                // Highest bracket (no upper limit)
                $taxableInBracket = $remainingIncome;
            } else {
                // Calculate amount in this bracket
                $bracketRange = $bracket->max_amount - $bracket->min_amount;
                $taxableInBracket = min($remainingIncome, $bracketRange);
            }

            if ($taxableInBracket > 0) {
                $taxInBracket = ($taxableInBracket * $bracket->rate) / 100;
                $cumulativeTax += $taxInBracket;

                $breakdown[] = [
                    'bracket' => $bracket->order,
                    'min_amount' => $this->roundCurrency($bracket->min_amount),
                    'max_amount' => $bracket->max_amount ? $this->roundCurrency($bracket->max_amount) : 'No limit',
                    'rate' => $bracket->rate . '%',
                    'taxable_in_bracket' => $this->roundCurrency($taxableInBracket),
                    'tax' => $this->roundCurrency($taxInBracket),
                    'cumulative_tax' => $this->roundCurrency($cumulativeTax),
                ];

                $remainingIncome -= $taxableInBracket;
            }
        }

        return $breakdown;
    }

    /**
     * Calculate monthly PAYE from annual income
     */
    public function calculateMonthlyPaye(float $annualIncome, array $params = []): array
    {
        $annualCalculation = $this->calculate($annualIncome, $params);
        
        return [
            'annual' => $annualCalculation,
            'monthly' => [
                'gross_income' => $this->roundCurrency($annualIncome / 12),
                'total_reliefs' => $this->roundCurrency($annualCalculation['total_reliefs'] / 12),
                'taxable_income' => $this->roundCurrency($annualCalculation['taxable_income'] / 12),
                'total_tax' => $this->roundCurrency($annualCalculation['total_tax'] / 12),
            ],
        ];
    }
}
