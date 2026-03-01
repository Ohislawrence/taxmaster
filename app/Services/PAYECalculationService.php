<?php

namespace App\Services;

use App\Models\PayeSchedule;
use Carbon\Carbon;

class PAYECalculationService
{
    /**
     * Nigerian PAYE tax brackets (2023 onwards)
     * Annual taxable income ranges and their corresponding rates
     */
    private const TAX_BRACKETS = [
        ['min' => 0, 'max' => 300000, 'rate' => 7],
        ['min' => 300001, 'max' => 600000, 'rate' => 11],
        ['min' => 600001, 'max' => 1100000, 'rate' => 15],
        ['min' => 1100001, 'max' => 1600000, 'rate' => 19],
        ['min' => 1600001, 'max' => 3200000, 'rate' => 21],
        ['min' => 3200001, 'max' => PHP_FLOAT_MAX, 'rate' => 24],
    ];

    /**
     * Standard relief allowances (2023)
     */
    private const RELIEF_CONSTANTS = [
        'CRA' => 0.20, // Consolidated Relief Allowance - 20% of gross or NGN 200,000 (whichever is higher)
        'MIN_CRA' => 200000,
        'PENSION_RATE' => 0.08, // 8% pension contribution
        'NHF_RATE' => 0.025, // 2.5% National Housing Fund
        'NHIS_RATE' => 0.05, // 5% NHIS contribution (on basic salary only)
    ];

    /**
     * Calculate monthly PAYE for a staff member
     *
     * @param float $grossPay Monthly gross pay
     * @param array $allowances Additional allowances (housing, transport, etc.)
     * @param array $reliefs Optional reliefs (pension, NHF, NHIS, life insurance, mortgage)
     * @param float|null $cumulativeGross Cumulative gross for the year (for progressive calculation)
     * @param float|null $cumulativeTax Cumulative tax already paid
     * @return array
     */
    public function calculateMonthlyPAYE(
        float $grossPay,
        array $allowances = [],
        array $reliefs = [],
        ?float $cumulativeGross = null,
        ?float $cumulativeTax = null
    ): array {
        // Calculate total gross including allowances
        $totalGross = $grossPay + array_sum(array_values($allowances));

        // Calculate annual equivalent for proper bracket calculation
        $currentMonth = $cumulativeGross ? ceil($cumulativeGross / $grossPay) : 1;
        $annualGross = $totalGross * 12;

        // Calculate reliefs
        $calculatedReliefs = $this->calculateReliefs($totalGross, $reliefs);
        $totalReliefs = array_sum(array_values($calculatedReliefs));

        // Calculate taxable income
        $taxableIncome = max(0, $totalGross - $totalReliefs);
        $annualTaxableIncome = $taxableIncome * 12;

        // Calculate annual tax using progressive brackets
        $annualTax = $this->computeProgressiveTax($annualTaxableIncome);

        // Calculate monthly tax
        $monthlyTax = round($annualTax / 12, 2);

        // If cumulative data provided, calculate actual tax due this month
        if ($cumulativeGross !== null && $cumulativeTax !== null) {
            $newCumulativeGross = $cumulativeGross + $totalGross;
            $newCumulativeTaxableIncome = ($newCumulativeGross - ($totalReliefs * $currentMonth)) * 12 / $currentMonth;
            $expectedCumulativeTax = $this->computeProgressiveTax($newCumulativeTaxableIncome) * $currentMonth / 12;
            $monthlyTax = round($expectedCumulativeTax - $cumulativeTax, 2);
        }

        return [
            'gross_pay' => $grossPay,
            'total_gross' => $totalGross,
            'allowances' => $allowances,
            'reliefs' => $calculatedReliefs,
            'total_reliefs' => $totalReliefs,
            'taxable_income' => $taxableIncome,
            'annual_taxable_income' => $annualTaxableIncome,
            'paye_due' => max(0, $monthlyTax),
            'net_pay' => $totalGross - max(0, $monthlyTax),
            'effective_rate' => $totalGross > 0 ? round(($monthlyTax / $totalGross) * 100, 2) : 0,
        ];
    }

    /**
     * Calculate tax reliefs based on gross pay and additional deductions
     *
     * @param float $grossPay
     * @param array $additionalReliefs
     * @return array
     */
    private function calculateReliefs(float $grossPay, array $additionalReliefs = []): array
    {
        $reliefs = [];

        // CRA - 20% of gross or NGN 200,000 (whichever is higher)
        $craCalculated = $grossPay * self::RELIEF_CONSTANTS['CRA'];
        $reliefs['CRA'] = max($craCalculated, self::RELIEF_CONSTANTS['MIN_CRA']);

        // Pension - 8% of gross (if not provided)
        if (isset($additionalReliefs['pension'])) {
            $reliefs['pension'] = $additionalReliefs['pension'];
        } else {
            $reliefs['pension'] = $grossPay * self::RELIEF_CONSTANTS['PENSION_RATE'];
        }

        // NHF - 2.5% of gross (if applicable)
        if (isset($additionalReliefs['NHF'])) {
            $reliefs['NHF'] = $additionalReliefs['NHF'];
        }

        // NHIS - 5% of basic (if applicable)
        if (isset($additionalReliefs['NHIS'])) {
            $reliefs['NHIS'] = $additionalReliefs['NHIS'];
        }

        // Life insurance premium (max 10% of gross)
        if (isset($additionalReliefs['life_insurance'])) {
            $reliefs['life_insurance'] = min($additionalReliefs['life_insurance'], $grossPay * 0.10);
        }

        // Mortgage interest relief
        if (isset($additionalReliefs['mortgage'])) {
            $reliefs['mortgage'] = $additionalReliefs['mortgage'];
        }

        return array_map(fn($val) => round($val, 2), $reliefs);
    }

    /**
     * Compute progressive tax based on Nigerian PAYE brackets
     *
     * @param float $annualTaxableIncome
     * @return float
     */
    private function computeProgressiveTax(float $annualTaxableIncome): float
    {
        $totalTax = 0;
        $remainingIncome = $annualTaxableIncome;

        foreach (self::TAX_BRACKETS as $bracket) {
            if ($remainingIncome <= 0) {
                break;
            }

            $bracketSize = $bracket['max'] - $bracket['min'] + 1;
            $taxableInBracket = min($remainingIncome, $bracketSize);

            if ($annualTaxableIncome > $bracket['min']) {
                $actualTaxable = min($taxableInBracket, $remainingIncome);
                $totalTax += ($actualTaxable * $bracket['rate']) / 100;
                $remainingIncome -= $actualTaxable;
            }
        }

        return round($totalTax, 2);
    }

    /**
     * Calculate cumulative PAYE for a staff member across multiple months
     *
     * @param int $staffId
     * @param string $startPeriod Format: YYYY-MM
     * @param string $endPeriod Format: YYYY-MM
     * @return array
     */
    public function calculateCumulativePAYE(int $staffId, string $startPeriod, string $endPeriod): array
    {
        $schedules = PayeSchedule::where('business_staff_id', $staffId)
            ->whereHas('payeReturn', function($query) use ($startPeriod, $endPeriod) {
                $query->whereBetween('period', [$startPeriod, $endPeriod]);
            })
            ->orderBy('created_at')
            ->get();

        $cumulativeGross = 0;
        $cumulativeTax = 0;
        $monthlyBreakdown = [];

        foreach ($schedules as $schedule) {
            $cumulativeGross += $schedule->gross_pay;
            $cumulativeTax += $schedule->paye_due;

            $monthlyBreakdown[] = [
                'period' => $schedule->payeReturn->period,
                'gross_pay' => $schedule->gross_pay,
                'paye_due' => $schedule->paye_due,
                'cumulative_gross' => $cumulativeGross,
                'cumulative_tax' => $cumulativeTax,
            ];
        }

        return [
            'cumulative_gross' => $cumulativeGross,
            'cumulative_tax' => $cumulativeTax,
            'months_count' => count($monthlyBreakdown),
            'monthly_breakdown' => $monthlyBreakdown,
        ];
    }

    /**
     * Generate PAYE summary for a business for a given period
     *
     * @param int $businessId
     * @param string $period Format: YYYY-MM
     * @return array
     */
    public function generateBusinessPAYESummary(int $businessId, string $period): array
    {
        // This will be implemented when we create the controller
        // For now, just return structure
        return [
            'period' => $period,
            'business_id' => $businessId,
            'total_gross_pay' => 0,
            'total_tax_deducted' => 0,
            'staff_count' => 0,
            'schedules' => [],
        ];
    }

    /**
     * Validate PAYE calculation
     *
     * @param array $calculation
     * @return bool
     */
    public function validateCalculation(array $calculation): bool
    {
        // Basic validation rules
        if ($calculation['paye_due'] < 0) {
            return false;
        }

        if ($calculation['taxable_income'] > $calculation['total_gross']) {
            return false;
        }

        if ($calculation['net_pay'] > $calculation['total_gross']) {
            return false;
        }

        return true;
    }
}
