<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\EInvoice\FirsApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Batch submit invoices to FIRS e-invoicing portal
 */
class BatchSubmitInvoicesToFirs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $invoiceIds = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if FIRS e-invoicing is enabled
        if (!config('services.firs.enabled', true)) {
            Log::info('FIRS e-invoicing is disabled');
            return;
        }

        // Check if API credentials are configured
        if (empty(config('services.firs.api_key')) || empty(config('services.firs.taxpayer_id'))) {
            Log::warning('FIRS API credentials not configured. Cannot perform batch submission.');
            // Dispatch individual jobs which will mark each invoice as not_authenticated
            foreach ($this->invoiceIds as $invoiceId) {
                $invoice = Invoice::find($invoiceId);
                if ($invoice) {
                    SubmitInvoiceToFirs::dispatch($invoice);
                }
            }
            return;
        }

        // Check if batch submission is enabled
        if (!config('services.firs.batch_submit', false)) {
            Log::info('FIRS batch submission is disabled. Use individual submission.');
            // Dispatch individual jobs instead
            foreach ($this->invoiceIds as $invoiceId) {
                $invoice = Invoice::find($invoiceId);
                if ($invoice) {
                    SubmitInvoiceToFirs::dispatch($invoice);
                }
            }
            return;
        }

        try {
            // Fetch invoices
            $invoices = Invoice::with('business')
                ->whereIn('id', $this->invoiceIds)
                ->whereIn('firs_status', ['pending', 'failed'])
                ->get();

            if ($invoices->isEmpty()) {
                Log::info('No invoices found for batch submission');
                return;
            }

            $firsApi = new FirsApiService();
            $batchSize = config('services.firs.batch_size', 50);

            // Process in chunks
            $invoices->chunk($batchSize)->each(function ($chunk) use ($firsApi) {
                $this->processBatch($chunk, $firsApi);
            });

        } catch (\Exception $e) {
            Log::error('Exception during batch FIRS submission', [
                'error' => $e->getMessage(),
                'invoice_ids' => $this->invoiceIds,
            ]);

            throw $e;
        }
    }

    /**
     * Process a batch of invoices
     */
    protected function processBatch($invoices, FirsApiService $firsApi): void
    {
        $invoicesData = [];
        $invoiceMap = [];

        foreach ($invoices as $invoice) {
            try {
                // Prepare invoice data (similar to SubmitInvoiceToFirs)
                $jobInstance = new SubmitInvoiceToFirs($invoice);
                $reflection = new \ReflectionClass($jobInstance);
                $method = $reflection->getMethod('prepareInvoiceData');
                $method->setAccessible(true);
                $invoiceData = $method->invoke($jobInstance);

                // Generate IRN if needed
                if (empty($invoiceData['irn'])) {
                    $nrsCredential = config('services.firs.api_key');
                    $invoiceData['irn'] = \App\Services\EInvoice\IRNGenerator::generate(
                        $invoiceData['invoiceNumber'],
                        $invoiceData['sellerTIN'],
                        $nrsCredential
                    );
                }

                $ublInvoice = new \App\Services\EInvoice\UBLInvoice($invoiceData);
                $invoicesData[] = $ublInvoice->toArray();
                $invoiceMap[$invoiceData['invoiceNumber']] = $invoice;

            } catch (\Exception $e) {
                Log::error('Failed to prepare invoice for batch', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);

                $invoice->update([
                    'firs_status' => 'error',
                    'firs_validation_errors' => json_encode(['error' => $e->getMessage()]),
                ]);
            }
        }

        if (empty($invoicesData)) {
            Log::info('No valid invoices to submit in this batch');
            return;
        }

        // Submit batch to FIRS
        $result = $firsApi->batchSubmitInvoices($invoicesData);

        if ($result['success']) {
            Log::info('Batch submission successful', [
                'batch_id' => $result['batch_id'],
                'total' => $result['total_submitted'],
                'successful' => $result['successful'],
                'failed' => $result['failed'],
            ]);

            // Update individual invoices based on results
            foreach ($result['results'] as $invoiceResult) {
                $invoiceNumber = $invoiceResult['invoice_number'] ?? null;
                if (!$invoiceNumber || !isset($invoiceMap[$invoiceNumber])) {
                    continue;
                }

                $invoice = $invoiceMap[$invoiceNumber];

                if ($invoiceResult['success']) {
                    $invoice->update([
                        'firs_reference' => $invoiceResult['firs_reference'],
                        'firs_submission_id' => $invoiceResult['submission_id'],
                        'firs_status' => 'submitted',
                        'firs_submitted_at' => now(),
                        'firs_response' => json_encode($invoiceResult),
                    ]);
                } else {
                    $invoice->update([
                        'firs_status' => 'failed',
                        'firs_validation_errors' => json_encode($invoiceResult['errors'] ?? []),
                    ]);
                }
            }
        } else {
            Log::error('Batch submission failed', [
                'error' => $result['error'],
            ]);

            // Mark all invoices as failed
            foreach ($invoiceMap as $invoice) {
                $invoice->update([
                    'firs_status' => 'failed',
                    'firs_validation_errors' => json_encode(['error' => $result['error']]),
                ]);
            }
        }
    }

    /**
     * Handle failed job
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Batch FIRS submission job failed', [
            'error' => $exception->getMessage(),
            'invoice_ids' => $this->invoiceIds,
        ]);
    }
}
