<?php

namespace Database\Seeders;

use App\Models\TaxType;
use App\Models\TaxBracket;
use App\Models\TaxRelief;
use App\Models\TaxDeadline;
use Illuminate\Database\Seeder;

class NigerianTaxSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedTaxTypes();
        $this->seedPayeBrackets();
        $this->seedTaxReliefs();
        $this->seedTaxDeadlines();
    }

    /**
     * Seed Nigerian tax types
     */
    protected function seedTaxTypes(): void
    {
        $taxTypes = [
            [
                'code' => 'paye',
                'name' => 'Personal Income Tax (PAYE)',
                'description' => 'Pay As You Earn - Tax on employment income',
                'calculation_method' => 'progressive',
                'frequency' => 'monthly',
                'due_day' => 10,
                'is_active' => true,
                'settings' => [
                    'applies_to' => 'individuals',
                    'has_brackets' => true,
                    'has_reliefs' => true,
                ],
            ],
            [
                'code' => 'cit',
                'name' => 'Company Income Tax (CIT)',
                'description' => 'Tax on company profits',
                'calculation_method' => 'flat',
                'flat_rate' => 30.00,
                'frequency' => 'annual',
                'due_day' => 30,
                'is_active' => true,
                'settings' => [
                    'applies_to' => 'companies',
                    'minimum_tax' => true,
                ],
            ],
            [
                'code' => 'vat',
                'name' => 'Value Added Tax (VAT)',
                'description' => 'Tax on goods and services',
                'calculation_method' => 'flat',
                'flat_rate' => 7.50,
                'frequency' => 'monthly',
                'due_day' => 21,
                'is_active' => true,
                'settings' => [
                    'applies_to' => 'transactions',
                    'exemptions' => ['basic_food_items', 'medical_services', 'exported_services'],
                ],
            ],
            [
                'code' => 'wht',
                'name' => 'Withholding Tax (WHT)',
                'description' => 'Tax withheld at source',
                'calculation_method' => 'percentage',
                'frequency' => 'monthly',
                'due_day' => 21,
                'is_active' => true,
                'settings' => [
                    'applies_to' => 'transactions',
                    'rates' => [
                        'dividends' => 10,
                        'interest' => 10,
                        'rent' => 10,
                        'royalties' => 10,
                        'professional_fees' => 10,
                        'contractors' => 5,
                        'consultancy' => 10,
                    ],
                ],
            ],
            [
                'code' => 'cgt',
                'name' => 'Capital Gains Tax',
                'description' => 'Tax on capital gains from disposal of assets',
                'calculation_method' => 'flat',
                'flat_rate' => 10.00,
                'frequency' => 'annual',
                'due_day' => 30,
                'is_active' => true,
                'settings' => [
                    'applies_to' => 'capital_gains',
                ],
            ],
            [
                'code' => 'stamp_duty',
                'name' => 'Stamp Duties',
                'description' => 'Tax on legal documents and transactions',
                'calculation_method' => 'percentage',
                'frequency' => 'annual',
                'is_active' => true,
                'settings' => [
                    'applies_to' => 'documents',
                    'rates' => [
                        'property_transfer' => 0.50,
                        'loan_agreement' => 0.125,
                        'bank_deposits' => 0.50, // on deposits > ₦1000
                    ],
                ],
            ],
        ];

        foreach ($taxTypes as $taxType) {
            TaxType::create($taxType);
        }
    }

    /**
     * Seed PAYE tax brackets (Nigerian Personal Income Tax)
     */
    protected function seedPayeBrackets(): void
    {
        $payeTaxType = TaxType::where('code', 'paye')->first();

        $brackets = [
            [
                'tax_type_id' => $payeTaxType->id,
                'min_amount' => 0,
                'max_amount' => 300000,
                'rate' => 7.00,
                'fixed_amount' => 0,
                'order' => 1,
                'is_active' => true,
            ],
            [
                'tax_type_id' => $payeTaxType->id,
                'min_amount' => 300001,
                'max_amount' => 600000,
                'rate' => 11.00,
                'fixed_amount' => 21000, // 7% of first 300,000
                'order' => 2,
                'is_active' => true,
            ],
            [
                'tax_type_id' => $payeTaxType->id,
                'min_amount' => 600001,
                'max_amount' => 1100000,
                'rate' => 15.00,
                'fixed_amount' => 54000, // Previous + 11% of next 300,000
                'order' => 3,
                'is_active' => true,
            ],
            [
                'tax_type_id' => $payeTaxType->id,
                'min_amount' => 1100001,
                'max_amount' => 1600000,
                'rate' => 19.00,
                'fixed_amount' => 129000, // Previous + 15% of next 500,000
                'order' => 4,
                'is_active' => true,
            ],
            [
                'tax_type_id' => $payeTaxType->id,
                'min_amount' => 1600001,
                'max_amount' => 3200000,
                'rate' => 21.00,
                'fixed_amount' => 224000, // Previous + 19% of next 500,000
                'order' => 5,
                'is_active' => true,
            ],
            [
                'tax_type_id' => $payeTaxType->id,
                'min_amount' => 3200001,
                'max_amount' => null, // No upper limit
                'rate' => 24.00,
                'fixed_amount' => 560000, // Previous + 21% of next 1,600,000
                'order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($brackets as $bracket) {
            TaxBracket::create($bracket);
        }
    }

    /**
     * Seed tax reliefs
     */
    protected function seedTaxReliefs(): void
    {
        $payeTaxType = TaxType::where('code', 'paye')->first();

        $reliefs = [
            [
                'tax_type_id' => $payeTaxType->id,
                'code' => 'cra',
                'name' => 'Consolidated Relief Allowance (CRA)',
                'description' => 'Higher of 1% of gross income or ₦200,000 + 20% of gross income',
                'calculation_type' => 'formula',
                'formula' => 'max(gross_income * 0.01, 200000 + (gross_income * 0.20))',
                'minimum_amount' => 200000,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 1,
            ],
            [
                'tax_type_id' => $payeTaxType->id,
                'code' => 'nhf',
                'name' => 'National Housing Fund (NHF)',
                'description' => '2.5% of basic salary',
                'calculation_type' => 'percentage',
                'value' => 2.50,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 2,
            ],
            [
                'tax_type_id' => $payeTaxType->id,
                'code' => 'nhis',
                'name' => 'National Health Insurance Scheme (NHIS)',
                'description' => 'Health insurance contributions',
                'calculation_type' => 'percentage',
                'value' => 5.00,
                'maximum_amount' => 100000,
                'is_mandatory' => false,
                'is_active' => true,
                'order' => 3,
            ],
            [
                'tax_type_id' => $payeTaxType->id,
                'code' => 'pension',
                'name' => 'Pension Contribution',
                'description' => 'Employee pension contribution (8% of monthly emolument)',
                'calculation_type' => 'percentage',
                'value' => 8.00,
                'is_mandatory' => true,
                'is_active' => true,
                'order' => 4,
            ],
            [
                'tax_type_id' => $payeTaxType->id,
                'code' => 'life_assurance',
                'name' => 'Life Assurance Premium',
                'description' => 'Premium paid on life assurance policy',
                'calculation_type' => 'fixed',
                'maximum_amount' => 100000,
                'is_mandatory' => false,
                'is_active' => true,
                'order' => 5,
            ],
        ];

        foreach ($reliefs as $relief) {
            TaxRelief::create($relief);
        }
    }

    /**
     * Seed tax deadlines
     */
    protected function seedTaxDeadlines(): void
    {
        $deadlines = [
            [
                'tax_type_id' => TaxType::where('code', 'paye')->first()->id,
                'period_type' => 'monthly',
                'due_day' => 10,
                'grace_days' => 0,
                'late_filing_penalty_rate' => 10.00,
                'interest_rate_per_annum' => 21.00,
                'is_active' => true,
                'description' => 'PAYE must be remitted by the 10th day of the following month',
            ],
            [
                'tax_type_id' => TaxType::where('code', 'vat')->first()->id,
                'period_type' => 'monthly',
                'due_day' => 21,
                'grace_days' => 0,
                'late_filing_penalty_rate' => 10.00,
                'interest_rate_per_annum' => 21.00,
                'is_active' => true,
                'description' => 'VAT returns must be filed by the 21st day of the following month',
            ],
            [
                'tax_type_id' => TaxType::where('code', 'wht')->first()->id,
                'period_type' => 'monthly',
                'due_day' => 21,
                'grace_days' => 0,
                'late_filing_penalty_rate' => 10.00,
                'interest_rate_per_annum' => 21.00,
                'is_active' => true,
                'description' => 'WHT must be remitted by the 21st day of the following month',
            ],
            [
                'tax_type_id' => TaxType::where('code', 'cit')->first()->id,
                'period_type' => 'annual',
                'due_day' => 30,
                'due_month' => 6, // June
                'grace_days' => 0,
                'late_filing_penalty_rate' => 10.00,
                'interest_rate_per_annum' => 21.00,
                'is_active' => true,
                'description' => 'CIT returns due 6 months after year-end for most companies',
            ],
            [
                'tax_type_id' => TaxType::where('code', 'cgt')->first()->id,
                'period_type' => 'annual',
                'due_day' => 30,
                'due_month' => 6,
                'grace_days' => 0,
                'late_filing_penalty_rate' => 10.00,
                'interest_rate_per_annum' => 21.00,
                'is_active' => true,
                'description' => 'CGT returns due within 6 months after disposal of asset',
            ],
        ];

        foreach ($deadlines as $deadline) {
            TaxDeadline::create($deadline);
        }
    }
}
