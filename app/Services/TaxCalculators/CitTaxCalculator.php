<?php

namespace App\Services\TaxCalculators;

class CitTaxCalculator extends BaseTaxCalculator
{
    /**
     * Calculate Company Income Tax
     * 
     * @param float $assessableProfit Profit after allowable deductions
     * @param array $params ['minimum_tax_base' => 0, 'previous_losses' => 0]
     * @return array Tax calculation breakdown
     */
    public function calculate(float $assessableProfit, array $params = []): array
    {
        // CIT is 30% flat rate on assessable profit
        $taxRate = $this->taxType->flat_rate;
        
        // Deduct previous losses if carried forward (up to 4 years)
        $previousLosses = $params['previous_losses'] ?? 0;
        $adjustedProfit = max(0, $assessableProfit - $previousLosses);

        // Calculate standard CIT
        $standardCit = ($adjustedProfit * $taxRate) / 100;

        // Calculate minimum tax (0.5% of gross turnover or 0.25% of gross assets, whichever is higher)
        $minimumTax = $this->calculateMinimumTax($params);

        // Tax payable is higher of standard CIT or minimum tax
        $totalTax = max($standardCit, $minimumTax);
        $isMinimumTaxApplied = $minimumTax > $standardCit;

        return $this->formatResult([
            'assessable_profit' => $this->roundCurrency($assessableProfit),
            'previous_losses_offset' => $this->roundCurrency($previousLosses),
            'adjusted_profit' => $this->roundCurrency($adjustedProfit),
            'tax_rate' => $taxRate . '%',
            'standard_cit' => $this->roundCurrency($standardCit),
            'minimum_tax' => $this->roundCurrency($minimumTax),
            'is_minimum_tax_applied' => $isMinimumTaxApplied,
            'total_tax' => $this->roundCurrency($totalTax),
            'notes' => $isMinimumTaxApplied 
                ? 'Minimum tax applied as it exceeds standard CIT' 
                : 'Standard CIT applied',
        ]);
    }

    /**
     * Calculate minimum tax
     * Minimum tax is higher of:
     * - 0.5% of gross turnover
     * - 0.25% of gross assets
     * - 0.25% of paid-up capital
     */
    protected function calculateMinimumTax(array $params): float
    {
        $grossTurnover = $params['gross_turnover'] ?? 0;
        $grossAssets = $params['gross_assets'] ?? 0;
        $paidUpCapital = $params['paid_up_capital'] ?? 0;

        $minTax1 = ($grossTurnover * 0.5) / 100;
        $minTax2 = ($grossAssets * 0.25) / 100;
        $minTax3 = ($paidUpCapital * 0.25) / 100;

        return max($minTax1, $minTax2, $minTax3);
    }

    /**
     * Calculate advance tax (quarterly payments)
     */
    public function calculateAdvanceTax(float $estimatedAnnualProfit): array
    {
        $annualTax = $this->calculate($estimatedAnnualProfit);
        $quarterlyAmount = $annualTax['total_tax'] / 4;

        return [
            'annual_estimated_tax' => $annualTax,
            'quarterly_payment' => $this->roundCurrency($quarterlyAmount),
            'payment_schedule' => [
                'Q1' => ['due_month' => 'April', 'amount' => $this->roundCurrency($quarterlyAmount)],
                'Q2' => ['due_month' => 'July', 'amount' => $this->roundCurrency($quarterlyAmount)],
                'Q3' => ['due_month' => 'October', 'amount' => $this->roundCurrency($quarterlyAmount)],
                'Q4' => ['due_month' => 'January', 'amount' => $this->roundCurrency($quarterlyAmount)],
            ],
        ];
    }
}
