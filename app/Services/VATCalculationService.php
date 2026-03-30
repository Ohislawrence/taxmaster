<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Transaction;
use App\Models\VATReturn;
use Carbon\Carbon;

class VATCalculationService
{
    const VAT_RATE = 0.075; // 7.5%

    /**
     * Calculate VAT for a period
     */
    public function calculateForPeriod(Business $business, string $period): array
    {
        // Period format: 2026-02 (YYYY-MM)
        $startDate = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Check if business is VAT exempt
        if ($business->is_vat_exempt) {
            return [
                'period' => $period,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'vat_sales' => 0,
                'output_vat' => 0,
                'vat_expenses' => 0,
                'input_vat' => 0,
                'net_vat' => 0,
                'due_date' => $endDate->copy()->addMonth()->day(21)->toDateString(),
                'is_exempt' => true,
                'exempt_reason' => $business->vat_exempt_reason ?? 'Business registered as VAT exempt',
                'transaction_count' => ['sales' => 0, 'expenses' => 0],
            ];
        }

        // Get VATable sales (Output VAT) - exclude VAT exempt transactions
        $vatSales = Transaction::where('business_id', $business->id)
            ->where('category', 'REVENUE')
            ->where('sub_category', 'VAT_OUTPUT')
            ->where(function($query) {
                $query->where('vat_exempt', false)
                      ->orWhereNull('vat_exempt');
            })
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $outputVat = $vatSales * self::VAT_RATE;

        // Get VAT exempt sales for reporting
        $exemptSales = Transaction::where('business_id', $business->id)
            ->where('category', 'REVENUE')
            ->where('vat_exempt', true)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        // Get VATable expenses (Input VAT) - exclude VAT exempt transactions
        $vatExpenses = Transaction::where('business_id', $business->id)
            ->where('category', 'EXPENSES')
            ->where('sub_category', 'VAT_INPUT')
            ->where(function($query) {
                $query->where('vat_exempt', false)
                      ->orWhereNull('vat_exempt');
            })
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $inputVat = $vatExpenses * self::VAT_RATE;

        // Net VAT (can't be negative in Nigeria - no refunds)
        $netVat = max(0, $outputVat - $inputVat);

        return [
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'vat_sales' => (float) $vatSales,
            'output_vat' => (float) $outputVat,
            'exempt_sales' => (float) $exemptSales,
            'vat_expenses' => (float) $vatExpenses,
            'input_vat' => (float) $inputVat,
            'net_vat' => (float) $netVat,
            'is_exempt' => false,
            'due_date' => $endDate->copy()->addMonth()->day(21)->toDateString(),
            'due_date' => $endDate->copy()->addMonth()->day(21)->toDateString(),
            'transaction_count' => [
                'sales' => Transaction::where('business_id', $business->id)
                    ->where('sub_category', 'VAT_OUTPUT')
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->count(),
                'expenses' => Transaction::where('business_id', $business->id)
                    ->where('sub_category', 'VAT_INPUT')
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->count(),
            ],
        ];
    }

    /**
     * Create or update VAT return
     */
    public function createReturn(Business $business, string $period): VATReturn
    {
        $calculation = $this->calculateForPeriod($business, $period);

        return VATReturn::updateOrCreate(
            [
                'business_id' => $business->id,
                'period' => $period,
            ],
            [
                'vat_sales' => $calculation['vat_sales'],
                'output_vat' => $calculation['output_vat'],
                'vat_expenses' => $calculation['vat_expenses'],
                'input_vat' => $calculation['input_vat'],
                'net_vat' => $calculation['net_vat'],
                'due_date' => $calculation['due_date'],
                'status' => 'draft',
                'form_data' => $calculation,
            ]
        );
    }

    /**
     * Submit VAT return (mark as submitted)
     */
    public function submitReturn(VATReturn $return, ?string $reference = null): void
    {
        $return->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'form_002_reference' => $reference ?? $this->generateForm002Reference($return),
        ]);
    }

    /**
     * Mark VAT return as paid
     */
    public function markPaid(VATReturn $return, string $paymentReference, ?string $receiptPath = null): void
    {
        $return->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_reference' => $paymentReference,
            'receipt_path' => $receiptPath,
        ]);
    }

    /**
     * Generate Form 002 reference number
     */
    protected function generateForm002Reference(VATReturn $return): string
    {
        return sprintf(
            'F002-%s-%s-%s',
            $return->business_id,
            $return->period,
            now()->format('YmdHis')
        );
    }

    /**
     * Get VAT summary for year
     */
    public function getYearSummary(Business $business, int $year): array
    {
        $returns = VATReturn::where('business_id', $business->id)
            ->where('period', 'like', $year . '%')
            ->orderBy('period')
            ->get();

        $summary = [
            'year' => $year,
            'total_vat_sales' => 0,
            'total_output_vat' => 0,
            'total_vat_expenses' => 0,
            'total_input_vat' => 0,
            'total_net_vat' => 0,
            'total_paid' => 0,
            'returns_count' => $returns->count(),
            'paid_count' => $returns->where('status', 'paid')->count(),
            'pending_count' => $returns->whereIn('status', ['draft', 'submitted'])->count(),
            'overdue_count' => $returns->where('status', 'overdue')->count(),
            'monthly_returns' => [],
        ];

        foreach ($returns as $return) {
            $summary['total_vat_sales'] += $return->vat_sales;
            $summary['total_output_vat'] += $return->output_vat;
            $summary['total_vat_expenses'] += $return->vat_expenses;
            $summary['total_input_vat'] += $return->input_vat;
            $summary['total_net_vat'] += $return->net_vat;

            if ($return->status === 'paid') {
                $summary['total_paid'] += $return->net_vat;
            }

            $summary['monthly_returns'][] = [
                'period' => $return->period,
                'net_vat' => $return->net_vat,
                'status' => $return->status,
                'due_date' => $return->due_date->toDateString(),
            ];
        }

        return $summary;
    }

    /**
     * Generate Form 002 data
     */
    public function generateForm002Data(VATReturn $return): array
    {
        $business = $return->business;

        return [
            'form_type' => 'VAT Return (Form 002)',
            'period' => $return->period,
            'period_label' => Carbon::createFromFormat('Y-m', $return->period)->format('F Y'),
            'business' => [
                'name' => $business->name,
                'tin' => $business->tax_identification_number,
                'address' => implode(', ', array_filter([
                    $business->address,
                    $business->city,
                    $business->state,
                ])),
                'email' => $business->email,
                'phone' => $business->phone,
            ],
            'vat_data' => [
                'vatable_sales' => number_format($return->vat_sales, 2),
                'output_vat' => number_format($return->output_vat, 2),
                'vatable_purchases' => number_format($return->vat_expenses, 2),
                'input_vat' => number_format($return->input_vat, 2),
                'net_vat_payable' => number_format($return->net_vat, 2),
            ],
            'payment' => [
                'amount' => $return->net_vat,
                'due_date' => $return->due_date->format('d F Y'),
                'reference' => $return->form_002_reference,
            ],
            'generated_at' => now()->format('d F Y H:i:s'),
            'generated_by' => auth()->user()->name ?? 'System',
        ];
    }

    /**
     * Get current period
     */
    public function getCurrentPeriod(): string
    {
        return now()->subMonth()->format('Y-m'); // Previous month
    }

    /**
     * Check if period has transactions
     */
    public function hasTransactionsForPeriod(Business $business, string $period): bool
    {
        $startDate = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return Transaction::where('business_id', $business->id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->exists();
    }
}
