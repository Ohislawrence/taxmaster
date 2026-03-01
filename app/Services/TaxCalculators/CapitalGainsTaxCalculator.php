<?php

namespace App\Services\TaxCalculators;

class CapitalGainsTaxCalculator extends BaseTaxCalculator
{
    /**
     * Calculate Capital Gains Tax
     * 
     * @param float $disposalValue Sale value of asset
     * @param array $params ['cost_of_acquisition' => 0, 'incidental_costs' => 0, 'exempt' => false]
     * @return array CGT calculation breakdown
     */
    public function calculate(float $disposalValue, array $params = []): array
    {
        // CGT rate is 10% in Nigeria
        $cgtRate = $this->taxType->flat_rate;

        // Calculate cost base
        $costOfAcquisition = $params['cost_of_acquisition'] ?? 0;
        $incidentalCosts = $params['incidental_costs'] ?? 0; // Legal fees, agent fees, etc.
        $totalCostBase = $costOfAcquisition + $incidentalCosts;

        // Calculate capital gain
        $capitalGain = max(0, $disposalValue - $totalCostBase);

        // Check for exemptions
        $isExempt = $params['exempt'] ?? false;
        $exemptionReason = $params['exemption_reason'] ?? '';

        // Calculate CGT
        $cgt = $isExempt ? 0 : ($capitalGain * $cgtRate) / 100;

        return $this->formatResult([
            'disposal_value' => $this->roundCurrency($disposalValue),
            'cost_of_acquisition' => $this->roundCurrency($costOfAcquisition),
            'incidental_costs' => $this->roundCurrency($incidentalCosts),
            'total_cost_base' => $this->roundCurrency($totalCostBase),
            'capital_gain' => $this->roundCurrency($capitalGain),
            'cgt_rate' => $cgtRate . '%',
            'capital_gains_tax' => $this->roundCurrency($cgt),
            'is_exempt' => $isExempt,
            'exemption_reason' => $exemptionReason,
        ]);
    }

    /**
     * Check if disposal is exempt from CGT
     */
    public function isExemptDisposal(string $assetType, array $params = []): bool
    {
        $exemptScenarios = [
            'government_securities' => true, // Disposal of Nigerian govt securities
            'stock_exchange' => true, // Securities purchased on Nigerian Stock Exchange
            'principal_residence' => $params['is_principal_residence'] ?? false,
            'compensation' => true, // Compensation for loss of office or employment
        ];

        return $exemptScenarios[$assetType] ?? false;
    }

    /**
     * Calculate CGT for property disposal
     */
    public function calculatePropertyCgt(float $salePrice, array $params = []): array
    {
        $purchasePrice = $params['purchase_price'] ?? 0;
        $improvementCosts = $params['improvement_costs'] ?? 0;
        $sellingCosts = $params['selling_costs'] ?? 0; // Agent fees, legal fees
        $isPrincipalResidence = $params['is_principal_residence'] ?? false;

        return $this->calculate($salePrice, [
            'cost_of_acquisition' => $purchasePrice + $improvementCosts,
            'incidental_costs' => $sellingCosts,
            'exempt' => $isPrincipalResidence,
            'exemption_reason' => $isPrincipalResidence ? 'Principal residence exemption' : '',
        ]);
    }
}
