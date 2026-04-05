<?php

namespace App\Jobs;

use App\Models\QuickBooksConnection;
use App\Services\QuickBooksIntegrationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncQuickBooksData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public QuickBooksConnection $connection,
        public ?Carbon $fromDate = null,
        public ?Carbon $toDate = null,
        public string $syncType = 'all' // all, invoices, bills
    ) {}

    /**
     * Execute the job.
     */
    public function handle(QuickBooksIntegrationService $qbService): void
    {
        if (!$this->connection->isActive()) {
            Log::warning('QuickBooks connection not active, skipping sync', [
                'connection_id' => $this->connection->id,
            ]);
            return;
        }

        try {
            Log::info('Starting QuickBooks sync job', [
                'connection_id' => $this->connection->id,
                'sync_type' => $this->syncType,
                'from_date' => $this->fromDate?->toDateString(),
                'to_date' => $this->toDate?->toDateString(),
            ]);

            // Sync invoices
            if (in_array($this->syncType, ['all', 'invoices'])) {
                $qbService->syncInvoicesFromQuickBooks(
                    $this->connection,
                    $this->fromDate,
                    $this->toDate
                );
            }

            // Sync bills
            if (in_array($this->syncType, ['all', 'bills'])) {
                $qbService->syncBillsFromQuickBooks(
                    $this->connection,
                    $this->fromDate,
                    $this->toDate
                );
            }

            Log::info('QuickBooks sync job completed successfully', [
                'connection_id' => $this->connection->id,
            ]);

        } catch (\Exception $e) {
            Log::error('QuickBooks sync job failed', [
                'connection_id' => $this->connection->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update connection status
            $this->connection->markError('Sync job failed: ' . $e->getMessage());

            throw $e; // Re-throw to mark job as failed
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('QuickBooks sync job permanently failed', [
            'connection_id' => $this->connection->id,
            'error' => $exception->getMessage(),
        ]);

        $this->connection->markError('Sync failed after 3 attempts: ' . $exception->getMessage());
    }
}
