<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\Invoice;
use App\Models\Reconciliation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoReconcileInvoices implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Find recent transactions that are business-related and not yet reconciled
        $transactions = Transaction::where('is_business_expense', true)
            ->where('transaction_date', '>=', now()->subDays(30))
            ->leftJoin('reconciliations', 'transactions.id', '=', 'reconciliations.transaction_id')
            ->whereNull('reconciliations.id')
            ->select('transactions.*')
            ->limit(500)
            ->get();

        foreach ($transactions as $transaction) {
            try {
                $matched = false;

                // 1) Exact match by payment reference (transaction.meta['reference'] or mono id)
                $metaRef = is_array($transaction->meta ?? null) ? ($transaction->meta['reference'] ?? null) : null;
                $txnRef = $transaction->mono_transaction_id ?? $metaRef;

                if ($txnRef) {
                    $invoice = Invoice::where('payment_reference', $txnRef)
                        ->orWhere('invoice_number', $txnRef)
                        ->first();

                    if ($invoice) {
                        // Mark invoice as paid and create reconciliation
                        $invoice->markPaid($txnRef);

                        Reconciliation::create([
                            'business_id' => $transaction->business_id,
                            'invoice_id' => $invoice->id,
                            'transaction_id' => $transaction->id,
                            'match_method' => 'reference',
                            'confidence' => 1.00,
                            'status' => 'matched',
                            'matched_at' => now(),
                            'data' => [
                                'transaction_meta' => $transaction->meta ?? null,
                            ],
                        ]);

                        $matched = true;
                    }
                }

                if ($matched) {
                    continue;
                }

                // 2) Fuzzy match by amount + date window
                $txnAmount = $transaction->amount;
                $txnDate = $transaction->transaction_date;

                $windowStart = $txnDate->copy()->subDays(3)->toDateString();
                $windowEnd = $txnDate->copy()->addDays(3)->toDateString();

                $invoice = Invoice::where('total', $txnAmount)
                    ->whereBetween('invoice_date', [$windowStart, $windowEnd])
                    ->where('status', '!=', 'paid')
                    ->first();

                if ($invoice) {
                    Reconciliation::create([
                        'business_id' => $transaction->business_id,
                        'invoice_id' => $invoice->id,
                        'transaction_id' => $transaction->id,
                        'match_method' => 'amount_date_fuzzy',
                        'confidence' => 0.70,
                        'status' => 'pending',
                        'matched_at' => now(),
                        'data' => [
                            'transaction_meta' => $transaction->meta ?? null,
                        ],
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('AutoReconcileInvoices error', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
