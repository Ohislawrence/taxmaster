<?php

namespace App\Jobs;

use App\Models\BankAccount;
use App\Notifications\BankSyncFailedNotification;
use App\Services\MonoIntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncBankAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public BankAccount $bankAccount
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MonoIntegrationService $service): void
    {
        try {
            if (!$this->bankAccount->is_active) {
                Log::info('Skipping sync for inactive bank account', [
                    'account_id' => $this->bankAccount->id,
                ]);
                return;
            }

            $syncedCount = $service->syncTransactions($this->bankAccount);

            Log::info('Bank account synced successfully', [
                'account_id' => $this->bankAccount->id,
                'transactions_synced' => $syncedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Bank account sync job failed', [
                'account_id' => $this->bankAccount->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Mark account as inactive if unauthorized
            if (str_contains($e->getMessage(), 'unauthorized') || str_contains($e->getMessage(), 'reauthorization')) {
                $this->bankAccount->update(['is_active' => false]);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Bank account sync job failed permanently', [
            'account_id' => $this->bankAccount->id,
            'error' => $exception->getMessage(),
        ]);

        // Send notification to business owner
        if ($this->bankAccount->business && $this->bankAccount->business->owner) {
            $this->bankAccount->business->owner->notify(
                new BankSyncFailedNotification(
                    $this->bankAccount,
                    $exception->getMessage(),
                    $this->tries
                )
            );
        }
    }
}

