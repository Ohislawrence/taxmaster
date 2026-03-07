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
        $grossTurnover = $params['gross_turnover'] ?? $assessableProfit;

        // Determine CIT rate based on company size (Finance Act 2019/2020)
        // Small companies (turnover < ₦25M): 0%
        // Medium companies (₦25M - ₦100M): 20%
        // Large companies (> ₦100M): 30%
        $companySize = $this->determineCompanySize($grossTurnover);
        $taxRate = match ($companySize) {
            'small' => 0,
            'medium' => 20,
            'large' => 30,
        };

        // Deduct previous losses if carried forward (up to 4 years)
        $previousLosses = $params['previous_losses'] ?? 0;
        $adjustedProfit = max(0, $assessableProfit - $previousLosses);

        // Calculate standard CIT
        $standardCit = ($adjustedProfit * $taxRate) / 100;

        // Calculate minimum tax (Finance Act 2020: 0.5% of gross turnover)
        // Small companies (turnover < ₦25M) are exempt from minimum tax
        $minimumTax = $this->calculateMinimumTax($params, $companySize);

        // Tax payable is higher of standard CIT or minimum tax
        $totalTax = max($standardCit, $minimumTax);
        $isMinimumTaxApplied = $minimumTax > $standardCit && $minimumTax > 0;

        return $this->formatResult([
            'assessable_profit' => $this->roundCurrency($assessableProfit),
            'gross_turnover' => $this->roundCurrency($grossTurnover),
            'company_size' => $companySize,
            'previous_losses_offset' => $this->roundCurrency($previousLosses),
            'adjusted_profit' => $this->roundCurrency($adjustedProfit),
            'tax_rate' => $taxRate . '%',
            'standard_cit' => $this->roundCurrency($standardCit),
            'minimum_tax' => $this->roundCurrency($minimumTax),
            'is_minimum_tax_applied' => $isMinimumTaxApplied,
            'total_tax' => $this->roundCurrency($totalTax),
            'notes' => $this->getCitNotes($companySize, $isMinimumTaxApplied),
        ]);
    }

    /**
     * Determine company size based on gross turnover
     * Per Companies Income Tax Act as amended by Finance Act 2019
     */
    private function determineCompanySize(float $grossTurnover): string
    {
        if ($grossTurnover < 25_000_000) {
            return 'small';
        } elseif ($grossTurnover <= 100_000_000) {
            return 'medium';
        }
        return 'large';
    }

    /**
     * Get descriptive notes for the CIT calculation
     */
    private function getCitNotes(string $companySize, bool $isMinimumTaxApplied): string
    {
        if ($companySize === 'small') {
            return 'Small company (turnover < ₦25M): exempt from CIT and minimum tax per Finance Act 2019';
        }
        if ($isMinimumTaxApplied) {
            return 'Minimum tax (0.5% of gross turnover) applied as it exceeds standard CIT';
        }
        $label = $companySize === 'medium' ? 'Medium company (20% rate)' : 'Large company (30% rate)';
        return "{$label}: Standard CIT applied per Finance Act 2019";
    }

    /**
     * Calculate minimum tax
     * Finance Act 2020: simplified to 0.5% of gross turnover only
     * Small companies (turnover < ₦25M) are exempt
     */
    protected function calculateMinimumTax(array $params, string $companySize = 'large'): float
    {
        // Small companies are exempt from minimum tax
        if ($companySize === 'small') {
            return 0;
        }

        $grossTurnover = $params['gross_turnover'] ?? 0;

        return ($grossTurnover * 0.5) / 100;
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
