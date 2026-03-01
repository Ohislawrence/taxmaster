<?php

namespace App\Services\TaxCalculators;

class VatTaxCalculator extends BaseTaxCalculator
{
    /**
     * Calculate VAT
     * 
     * @param float $taxableSupplies Total value of taxable supplies
     * @param array $params ['input_vat' => 0, 'exempt_supplies' => []]
     * @return array VAT calculation breakdown
     */
    public function calculate(float $taxableSupplies, array $params = []): array
    {
        // VAT rate is 7.5% in Nigeria
        $vatRate = $this->taxType->flat_rate;

        // Calculate output VAT (on sales)
        $outputVat = ($taxableSupplies * $vatRate) / 100;

        // Input VAT (VAT paid on purchases - can be claimed)
        $inputVat = $params['input_vat'] ?? 0;

        // Net VAT payable
        $netVatPayable = max(0, $outputVat - $inputVat);

        // Handle exempt supplies
        $exemptSupplies = $params['exempt_supplies'] ?? 0;
        $totalSupplies = $taxableSupplies + $exemptSupplies;

        return $this->formatResult([
            'taxable_supplies' => $this->roundCurrency($taxableSupplies),
            'exempt_supplies' => $this->roundCurrency($exemptSupplies),
            'total_supplies' => $this->roundCurrency($totalSupplies),
            'vat_rate' => $vatRate . '%',
            'output_vat' => $this->roundCurrency($outputVat),
            'input_vat' => $this->roundCurrency($inputVat),
            'net_vat_payable' => $this->roundCurrency($netVatPayable),
            'is_refundable' => $inputVat > $outputVat,
            'refund_amount' => $inputVat > $outputVat ? $this->roundCurrency($inputVat - $outputVat) : 0,
        ]);
    }

    /**
     * Check if a product/service is VAT exempt
     */
    public function isExempt(string $category): bool
    {
        $exemptCategories = [
            'basic_food_items',
            'medical_services',
            'pharmaceutical_products',
            'books_newspapers_magazines',
            'baby_products',
            'exported_services',
            'commercial_vehicles',
            'plant_machinery_for_production',
        ];

        return in_array($category, $exemptCategories);
    }

    /**
     * Calculate VAT on a single transaction
     */
    public function calculateTransactionVat(float $amount, bool $isVatInclusive = false): array
    {
        $vatRate = $this->taxType->flat_rate;

        if ($isVatInclusive) {
            // Amount includes VAT, extract it
            $vatAmount = ($amount * $vatRate) / (100 + $vatRate);
            $netAmount = $amount - $vatAmount;
        } else {
            // Amount excludes VAT, add it
            $vatAmount = ($amount * $vatRate) / 100;
            $netAmount = $amount;
        }

        return [
            'net_amount' => $this->roundCurrency($netAmount),
            'vat_amount' => $this->roundCurrency($vatAmount),
            'gross_amount' => $this->roundCurrency($netAmount + $vatAmount),
            'vat_rate' => $vatRate . '%',
            'is_vat_inclusive' => $isVatInclusive,
        ];
    }
}
