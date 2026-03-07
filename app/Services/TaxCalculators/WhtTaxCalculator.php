<?php

namespace App\Services\TaxCalculators;

class WhtTaxCalculator extends BaseTaxCalculator
{
    /**
     * WHT rates by entity type and transaction type (Nigerian FIRS rates)
     * Companies are taxed at higher rates than individuals for most transaction types.
     */
    protected array $companyRates = [
        'dividends' => 10.00,
        'interest' => 10.00,
        'rent' => 10.00,
        'royalties' => 10.00,
        'professional_fees' => 10.00,
        'technical_fees' => 10.00,
        'management_fees' => 10.00,
        'consultancy' => 10.00,
        'commission' => 10.00,
        'directors_fees' => 10.00,
        'contract' => 5.00,
        'construction' => 5.00,
    ];

    protected array $individualRates = [
        'dividends' => 10.00,
        'interest' => 10.00,
        'rent' => 10.00,
        'royalties' => 5.00,
        'professional_fees' => 5.00,
        'technical_fees' => 5.00,
        'management_fees' => 5.00,
        'consultancy' => 5.00,
        'commission' => 5.00,
        'directors_fees' => 10.00,
        'contract' => 5.00,
        'construction' => 5.00,
    ];

    /**
     * Calculate WHT
     *
     * @param float $transactionAmount Amount of transaction
     * @param array $params ['transaction_type' => '', 'entity_type' => 'company', 'is_final_tax' => false]
     * @return array WHT calculation breakdown
     */
    public function calculate(float $transactionAmount, array $params = []): array
    {
        $transactionType = $params['transaction_type'] ?? 'professional_fees';
        $entityType = $params['entity_type'] ?? 'company';
        $rate = $this->getWhtRate($transactionType, $entityType);

        // Calculate WHT amount
        $whtAmount = ($transactionAmount * $rate) / 100;
        $netPayable = $transactionAmount - $whtAmount;

        // Check if this is final tax
        $isFinalTax = $params['is_final_tax'] ?? false;

        return $this->formatResult([
            'gross_amount' => $this->roundCurrency($transactionAmount),
            'transaction_type' => $transactionType,
            'entity_type' => $entityType,
            'wht_rate' => $rate . '%',
            'wht_amount' => $this->roundCurrency($whtAmount),
            'net_payable' => $this->roundCurrency($netPayable),
            'is_final_tax' => $isFinalTax,
            'notes' => $isFinalTax
                ? 'WHT is treated as final tax - no further tax due'
                : 'WHT can be offset against final tax liability',
        ]);
    }

    /**
     * Get WHT rate for transaction type and entity type
     */
    public function getWhtRate(string $transactionType, string $entityType = 'company'): float
    {
        $rates = $entityType === 'individual' ? $this->individualRates : $this->companyRates;
        return $rates[$transactionType] ?? 5.00;
    }

    /**
     * Calculate WHT for multiple transactions
     */
    public function calculateBulkWht(array $transactions): array
    {
        $results = [];
        $totalGross = 0;
        $totalWht = 0;
        $totalNet = 0;

        foreach ($transactions as $transaction) {
            $calculation = $this->calculate(
                $transaction['amount'],
                [
                    'transaction_type' => $transaction['type'] ?? 'professional_fees',
                    'entity_type' => $transaction['entity_type'] ?? 'company',
                ]
            );

            $results[] = array_merge($calculation, [
                'description' => $transaction['description'] ?? '',
                'beneficiary' => $transaction['beneficiary'] ?? '',
            ]);

            $totalGross += $calculation['gross_amount'];
            $totalWht += $calculation['wht_amount'];
            $totalNet += $calculation['net_payable'];
        }

        return [
            'transactions' => $results,
            'summary' => [
                'total_transactions' => count($transactions),
                'total_gross' => $this->roundCurrency($totalGross),
                'total_wht' => $this->roundCurrency($totalWht),
                'total_net_payable' => $this->roundCurrency($totalNet),
            ],
        ];
    }

    /**
     * Get all available WHT rates for an entity type
     */
    public function getAvailableRates(string $entityType = 'company'): array
    {
        $rates = $entityType === 'individual' ? $this->individualRates : $this->companyRates;
        return array_map(function ($rate) {
            return $rate . '%';
        }, $rates);
    }

    /**
     * Get all rates for both entity types
     */
    public function getAllRates(): array
    {
        return [
            'company' => $this->getAvailableRates('company'),
            'individual' => $this->getAvailableRates('individual'),
        ];
    }
}
