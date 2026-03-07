<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessStaff;
use App\Models\TaxReturn;
use Illuminate\Support\Collection;

class TaxCalculationService
{
    /**
     * Calculate total tax for a business based on gross income and staff
     */
    public function calculateBusinessTax(Business $business, string $taxPeriod): array
    {
        $taxReturn = TaxReturn::where('business_id', $business->id)
            ->where('tax_period', $taxPeriod)
            ->first();

        if (!$taxReturn) {
            return [
                'error' => 'Tax return not found for the given period',
            ];
        }

        $grossIncome = $taxReturn->gross_income;
        $deductions = $taxReturn->deductions;
        $taxableIncome = $grossIncome - $deductions;

        // Nigerian CIT tiers (Finance Act 2019)
        // Small: turnover < ₦25M = 0%, Medium: ₦25M–₦100M = 20%, Large: > ₦100M = 30%
        if ($grossIncome < 25_000_000) {
            $citRate = 0;
        } elseif ($grossIncome <= 100_000_000) {
            $citRate = 0.20;
        } else {
            $citRate = 0.30;
        }

        // Calculate tax on business income
        $businessTax = $taxableIncome * $citRate;

        // Calculate staff tax breakdown
        $staffTaxBreakdown = $this->calculateStaffTax($business, $taxPeriod);

        $totalTax = $businessTax + $staffTaxBreakdown['total_staff_tax'];

        return [
            'gross_income' => $grossIncome,
            'deductions' => $deductions,
            'taxable_income' => $taxableIncome,
            'business_tax' => $businessTax,
            'staff_tax_breakdown' => $staffTaxBreakdown['breakdown'],
            'total_staff_tax' => $staffTaxBreakdown['total_staff_tax'],
            'total_tax_due' => $totalTax,
            'tax_rate' => $citRate,
        ];
    }

    /**
     * Calculate tax for individual staff members
     */
    public function calculateStaffTax(Business $business, string $taxPeriod): array
    {
        $staff = $business->staff()->where('status', 'active')->get();
        $breakdown = [];
        $totalStaffTax = 0;

        foreach ($staff as $member) {
            $monthlyTax = $this->calculateMonthlyStaffTax($member);
            $breakdown[] = [
                'staff_id' => $member->id,
                'staff_name' => $member->full_name,
                'monthly_salary' => $member->monthly_salary,
                'monthly_tax' => $monthlyTax,
                'annual_tax' => $monthlyTax * 12,
            ];
            $totalStaffTax += $monthlyTax;
        }

        return [
            'breakdown' => $breakdown,
            'total_staff_tax' => $totalStaffTax * 12, // Annual tax for the period
        ];
    }

    /**
     * Calculate monthly tax for a staff member using Nigerian PAYE progressive brackets
     */
    public function calculateMonthlyStaffTax(BusinessStaff $staff): float
    {
        $payeService = app(PAYECalculationService::class);
        $result = $payeService->calculateMonthlyPAYE($staff->monthly_salary);

        return $result['paye_due'];
    }

    /**
     * Get pending tax obligations
     */
    public function getPendingTaxObligations(Business $business): Collection
    {
        return TaxReturn::where('business_id', $business->id)
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->get();
    }

    /**
     * Update tax return with calculated values
     */
    public function updateTaxReturn(TaxReturn $taxReturn, array $calculatedTax): void
    {
        $taxReturn->update([
            'total_tax_due' => $calculatedTax['total_tax_due'],
            'balance' => $calculatedTax['total_tax_due'] - $taxReturn->total_tax_paid,
            'staff_breakdown' => $calculatedTax['staff_tax_breakdown'],
        ]);
    }
}
