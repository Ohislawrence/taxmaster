<?php

namespace App\Services;

use App\Models\WhtTransaction;
use App\Models\WhtReturn;
use Carbon\Carbon;

class WHTCalculationService
{
    /**
     * WHT rates by transaction type (Nigerian rates)
     */
    private const WHT_RATES = [
        'dividends' => 10.0,
        'interest' => 10.0,
        'rent' => 10.0,
        'royalties' => 10.0,
        'commissions' => 5.0,
        'consultancy' => 10.0,
        'contracts' => 5.0,
        'management_fees' => 10.0,
        'directors_fees' => 10.0,
        'professional_fees' => 10.0,
    ];

    /**
     * Calculate WHT for a transaction
     *
     * @param float $grossAmount
     * @param string $transactionType
     * @param float|null $customRate Optional custom rate to override default
     * @return array
     */
    public function calculateWHT(float $grossAmount, string $transactionType, ?float $customRate = null): array
    {
        $rate = $customRate ?? $this->getWHTRate($transactionType);
        $whtAmount = round(($grossAmount * $rate) / 100, 2);
        $netAmount = round($grossAmount - $whtAmount, 2);

        return [
            'gross_amount' => $grossAmount,
            'wht_rate' => $rate,
            'wht_amount' => $whtAmount,
            'net_amount' => $netAmount,
            'transaction_type' => $transactionType,
        ];
    }

    /**
     * Get WHT rate for a transaction type
     *
     * @param string $transactionType
     * @return float
     */
    public function getWHTRate(string $transactionType): float
    {
        return self::WHT_RATES[$transactionType] ?? 0;
    }

    /**
     * Get all WHT rates
     *
     * @return array
     */
    public function getAllWHTRates(): array
    {
        return self::WHT_RATES;
    }

    /**
     * Reverse calculate gross amount from net amount
     *
     * @param float $netAmount
     * @param string $transactionType
     * @param float|null $customRate
     * @return array
     */
    public function reverseCalculateGross(float $netAmount, string $transactionType, ?float $customRate = null): array
    {
        $rate = $customRate ?? $this->getWHTRate($transactionType);
        $grossAmount = round($netAmount / (1 - ($rate / 100)), 2);
        $whtAmount = round($grossAmount - $netAmount, 2);

        return [
            'net_amount' => $netAmount,
            'gross_amount' => $grossAmount,
            'wht_rate' => $rate,
            'wht_amount' => $whtAmount,
            'transaction_type' => $transactionType,
        ];
    }

    /**
     * Bulk calculate WHT for multiple transactions
     *
     * @param array $transactions Array of ['gross_amount' => float, 'transaction_type' => string]
     * @return array
     */
    public function bulkCalculate(array $transactions): array
    {
        $results = [];
        $totalGross = 0;
        $totalWHT = 0;
        $totalNet = 0;

        foreach ($transactions as $transaction) {
            $calculation = $this->calculateWHT(
                $transaction['gross_amount'],
                $transaction['transaction_type'],
                $transaction['custom_rate'] ?? null
            );

            $results[] = array_merge($transaction, $calculation);
            $totalGross += $calculation['gross_amount'];
            $totalWHT += $calculation['wht_amount'];
            $totalNet += $calculation['net_amount'];
        }

        return [
            'transactions' => $results,
            'summary' => [
                'total_gross' => round($totalGross, 2),
                'total_wht' => round($totalWHT, 2),
                'total_net' => round($totalNet, 2),
                'transaction_count' => count($transactions),
            ],
        ];
    }

    /**
     * Generate WHT return schedule for a period
     *
     * @param int $businessId
     * @param string $period Format: YYYY-MM
     * @return array
     */
    public function generateWHTSchedule(int $businessId, string $period): array
    {
        $transactions = WhtTransaction::where('business_id', $businessId)
            ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$period])
            ->get();

        $scheduleByType = [];
        $totalWHT = 0;

        foreach ($transactions as $transaction) {
            $type = $transaction->transaction_type;

            if (!isset($scheduleByType[$type])) {
                $scheduleByType[$type] = [
                    'transaction_type' => $type,
                    'transaction_type_label' => $transaction->transaction_type_label,
                    'wht_rate' => $transaction->wht_rate,
                    'transaction_count' => 0,
                    'total_gross' => 0,
                    'total_wht' => 0,
                    'total_net' => 0,
                ];
            }

            $scheduleByType[$type]['transaction_count']++;
            $scheduleByType[$type]['total_gross'] += $transaction->gross_amount;
            $scheduleByType[$type]['total_wht'] += $transaction->wht_amount;
            $scheduleByType[$type]['total_net'] += $transaction->net_amount;
            $totalWHT += $transaction->wht_amount;
        }

        // Round all values
        foreach ($scheduleByType as $type => $data) {
            $scheduleByType[$type]['total_gross'] = round($data['total_gross'], 2);
            $scheduleByType[$type]['total_wht'] = round($data['total_wht'], 2);
            $scheduleByType[$type]['total_net'] = round($data['total_net'], 2);
        }

        return [
            'period' => $period,
            'business_id' => $businessId,
            'schedule' => array_values($scheduleByType),
            'total_wht_deducted' => round($totalWHT, 2),
            'transaction_count' => $transactions->count(),
        ];
    }

    /**
     * Create or update WHT return for a period
     *
     * @param int $businessId
     * @param string $period
     * @return WhtReturn
     */
    public function createOrUpdateWHTReturn(int $businessId, string $period): WhtReturn
    {
        $schedule = $this->generateWHTSchedule($businessId, $period);

        return WhtReturn::updateOrCreate(
            [
                'business_id' => $businessId,
                'period' => $period,
            ],
            [
                'total_wht_deducted' => $schedule['total_wht_deducted'],
                'transaction_count' => $schedule['transaction_count'],
                'schedule_data' => $schedule['schedule'],
                'status' => 'draft',
            ]
        );
    }

    /**
     * Get WHT summary for a business for a date range
     *
     * @param int $businessId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getWHTSummary(int $businessId, string $startDate, string $endDate): array
    {
        $transactions = WhtTransaction::where('business_id', $businessId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        $summaryByType = [];
        $totalGross = 0;
        $totalWHT = 0;
        $totalNet = 0;

        foreach ($transactions as $transaction) {
            $type = $transaction->transaction_type;

            if (!isset($summaryByType[$type])) {
                $summaryByType[$type] = [
                    'transaction_type' => $type,
                    'count' => 0,
                    'total_gross' => 0,
                    'total_wht' => 0,
                    'total_net' => 0,
                ];
            }

            $summaryByType[$type]['count']++;
            $summaryByType[$type]['total_gross'] += $transaction->gross_amount;
            $summaryByType[$type]['total_wht'] += $transaction->wht_amount;
            $summaryByType[$type]['total_net'] += $transaction->net_amount;

            $totalGross += $transaction->gross_amount;
            $totalWHT += $transaction->wht_amount;
            $totalNet += $transaction->net_amount;
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'by_type' => array_values($summaryByType),
            'totals' => [
                'total_gross' => round($totalGross, 2),
                'total_wht' => round($totalWHT, 2),
                'total_net' => round($totalNet, 2),
                'transaction_count' => $transactions->count(),
            ],
        ];
    }

    /**
     * Validate WHT calculation
     *
     * @param array $calculation
     * @return bool
     */
    public function validateCalculation(array $calculation): bool
    {
        // Check that WHT amount is not negative
        if ($calculation['wht_amount'] < 0) {
            return false;
        }

        // Check that net amount equals gross minus WHT
        $expectedNet = round($calculation['gross_amount'] - $calculation['wht_amount'], 2);
        if (abs($calculation['net_amount'] - $expectedNet) > 0.01) {
            return false;
        }

        // Check that rate is valid
        if ($calculation['wht_rate'] < 0 || $calculation['wht_rate'] > 100) {
            return false;
        }

        return true;
    }

    /**
     * Get transaction type options for forms
     *
     * @return array
     */
    public function getTransactionTypeOptions(): array
    {
        return [
            ['value' => 'dividends', 'label' => 'Dividends', 'rate' => 10.0],
            ['value' => 'interest', 'label' => 'Interest', 'rate' => 10.0],
            ['value' => 'rent', 'label' => 'Rent', 'rate' => 10.0],
            ['value' => 'royalties', 'label' => 'Royalties', 'rate' => 10.0],
            ['value' => 'commissions', 'label' => 'Commissions', 'rate' => 5.0],
            ['value' => 'consultancy', 'label' => 'Consultancy', 'rate' => 10.0],
            ['value' => 'contracts', 'label' => 'Contracts', 'rate' => 5.0],
            ['value' => 'management_fees', 'label' => 'Management Fees', 'rate' => 10.0],
            ['value' => 'directors_fees', 'label' => 'Directors Fees', 'rate' => 10.0],
            ['value' => 'professional_fees', 'label' => 'Professional Fees', 'rate' => 10.0],
        ];
    }
}
