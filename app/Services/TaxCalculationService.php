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

        // Get standard tax rate from config
        $standardRate = config('taxmaster.tax.standard_rate', 0.10);

        // Calculate tax on business income
        $businessTax = $taxableIncome * $standardRate;

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
            'tax_rate' => $standardRate,
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
     * Calculate monthly tax for a staff member based on Nigerian tax rules
     */
    public function calculateMonthlyStaffTax(BusinessStaff $staff): float
    {
        $monthlyIncome = $staff->monthly_salary;

        // Apply personal relief
        $personalRelief = config('taxmaster.tax.personal_reliefs.personal', 500000) / 12;

        $taxableIncome = max(0, $monthlyIncome - $personalRelief);

        // Nigerian tax brackets (simplified - adjust based on actual tax rules)
        $taxRate = 0.10; // 10% standard rate

        if ($taxableIncome > 800000) {
            $taxRate = 0.15;
        }
        if ($taxableIncome > 1500000) {
            $taxRate = 0.20;
        }

        return $taxableIncome * $taxRate;
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
