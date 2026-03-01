<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\TransactionCategorizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CategorizeTransaction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Transaction $transaction
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TransactionCategorizationService $service): void
    {
        try {
            $service->categorize($this->transaction);

            Log::info('Transaction categorized', [
                'transaction_id' => $this->transaction->id,
                'category' => $this->transaction->category,
                'sub_category' => $this->transaction->sub_category,
                'confidence' => $this->transaction->confidence,
            ]);
        } catch (\Exception $e) {
            Log::error('Transaction categorization job failed', [
                'transaction_id' => $this->transaction->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Transaction categorization job failed permanently', [
            'transaction_id' => $this->transaction->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
