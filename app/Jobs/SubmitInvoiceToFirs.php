<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\EInvoice\FirsApiService;
use App\Services\EInvoice\EInvoiceService;
use App\Services\EInvoice\UBLInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Submit invoice to FIRS e-invoicing portal
 */
class SubmitInvoiceToFirs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Invoice $invoice,
        public bool $forceResubmit = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if FIRS e-invoicing is enabled
        if (!config('services.firs.enabled', true)) {
            Log::info('FIRS e-invoicing is disabled', [
                'invoice_id' => $this->invoice->id,
            ]);
            return;
        }

        // Check if API credentials are configured
        if (empty(config('services.firs.api_key')) || empty(config('services.firs.taxpayer_id'))) {
            Log::warning('FIRS API credentials not configured', [
                'invoice_id' => $this->invoice->id,
            ]);

            // Mark invoice as not authenticated but prepare the data
            $this->invoice->update([
                'firs_status' => 'not_authenticated',
                'firs_validation_errors' => json_encode([
                    'error' => 'FIRS API credentials not configured. Invoice can be manually submitted.',
                    'message' => 'Please configure FIRS_API_KEY and FIRS_TAXPAYER_ID in your environment or download the invoice for manual submission.',
                ]),
            ]);

            return;
        }

        // Skip if already successfully submitted (unless force resubmit)
        if (!$this->forceResubmit && $this->invoice->firs_status === 'approved') {
            Log::info('Invoice already approved by FIRS', [
                'invoice_id' => $this->invoice->id,
                'firs_reference' => $this->invoice->firs_reference,
            ]);
            return;
        }

        try {
            $firsApi = new FirsApiService();

            // Prepare invoice data for FIRS submission
            $invoiceData = $this->prepareInvoiceData();

            // Generate IRN if not exists
            if (empty($invoiceData['irn'])) {
                $nrsCredential = config('services.firs.api_key');
                $invoiceData['irn'] = \App\Services\EInvoice\IRNGenerator::generate(
                    $invoiceData['invoiceNumber'],
                    $invoiceData['sellerTIN'],
                    $nrsCredential
                );
            }

            // Sign invoice if not already signed
            if (empty($invoiceData['signature'])) {
                $invoiceData = $this->signInvoice($invoiceData);
            }

            // Create UBL invoice
            $ublInvoice = new UBLInvoice($invoiceData);

            // Validate invoice
            $validationErrors = $ublInvoice->validate();
            if (!empty($validationErrors)) {
                $this->invoice->update([
                    'firs_status' => 'validation_failed',
                    'firs_validation_errors' => json_encode($validationErrors),
                ]);

                Log::error('Invoice validation failed', [
                    'invoice_id' => $this->invoice->id,
                    'errors' => $validationErrors,
                ]);

                return;
            }

            // Submit to FIRS
            $result = $firsApi->submitInvoice($ublInvoice->toArray());

            if ($result['success']) {
                // Update invoice with FIRS response
                $this->invoice->update([
                    'firs_reference' => $result['firs_reference'],
                    'firs_submission_id' => $result['submission_id'],
                    'firs_irn' => $invoiceData['irn'],
                    'firs_status' => $result['validation_status'] === 'approved' ? 'approved' : 'submitted',
                    'firs_submitted_at' => now(),
                    'firs_approved_at' => $result['validation_status'] === 'approved' ? now() : null,
                    'firs_response' => json_encode($result['data']),
                    'digital_signature' => $invoiceData['signature'],
                ]);

                Log::info('Invoice successfully submitted to FIRS', [
                    'invoice_id' => $this->invoice->id,
                    'firs_reference' => $result['firs_reference'],
                    'status' => $result['validation_status'],
                ]);
            } else {
                // Update invoice with error
                $this->invoice->update([
                    'firs_status' => 'failed',
                    'firs_validation_errors' => json_encode([
                        'error' => $result['error'],
                        'error_code' => $result['error_code'],
                        'validation_errors' => $result['validation_errors'] ?? [],
                    ]),
                    'firs_response' => json_encode($result),
                ]);

                Log::error('Invoice submission to FIRS failed', [
                    'invoice_id' => $this->invoice->id,
                    'error' => $result['error'],
                    'error_code' => $result['error_code'],
                ]);

                // Retry on certain errors
                if (in_array($result['error_code'], ['AUTH_FAILED', 'TIMEOUT', 'CONNECTION_ERROR'])) {
                    throw new \Exception($result['error']);
                }
            }
        } catch (\Exception $e) {
            Log::error('Exception during FIRS submission', [
                'invoice_id' => $this->invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->invoice->update([
                'firs_status' => 'error',
                'firs_validation_errors' => json_encode(['error' => $e->getMessage()]),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Prepare invoice data for FIRS submission
     */
    protected function prepareInvoiceData(): array
    {
        $business = $this->invoice->business;
        $data = $this->invoice->data ?? [];

        return [
            'invoiceNumber' => $this->invoice->invoice_number,
            'issueDate' => $this->invoice->invoice_date->format('Y-m-d'),
            'dueDate' => $this->invoice->due_date?->format('Y-m-d'),
            'invoiceTypeCode' => $this->invoice->invoice_type_code ?? '380',
            'currency' => 'NGN',

            // Seller (Business)
            'sellerName' => $business->name,
            'sellerTIN' => $business->tax_identification_number,
            'sellerRegistrationNumber' => $business->registration_number,
            'sellerStreet' => $business->address,
            'sellerCity' => $business->city,
            'sellerState' => $business->state,
            'sellerCountry' => $business->country ?? 'NG',
            'sellerPostalCode' => $business->postal_code ?? '',
            'sellerEmail' => $business->email,
            'sellerPhone' => $business->phone,

            // Buyer
            'buyerName' => $data['buyer_name'] ?? '',
            'buyerTIN' => $data['buyer_tin'] ?? '',
            'buyerEmail' => $this->invoice->buyer_email,
            'buyerPhone' => $this->invoice->buyer_phone,
            'buyerStreet' => $this->invoice->buyer_address,
            'buyerCity' => $this->invoice->buyer_city,
            'buyerState' => $this->invoice->buyer_state,
            'buyerPostalCode' => $this->invoice->buyer_postal_code,
            'buyerCountry' => $this->invoice->buyer_country ?? 'NG',

            // Amounts
            'lineExtensionAmount' => $this->invoice->subtotal,
            'taxExclusiveAmount' => $this->invoice->subtotal,
            'vatAmount' => $this->invoice->tax,
            'vatRate' => $this->invoice->vat_rate ?? 7.5,
            'taxInclusiveAmount' => $this->invoice->total,
            'totalAmount' => $this->invoice->total,
            'prepaidAmount' => 0.0,
            'payableAmount' => $this->invoice->total,

            // Payment
            'paymentMeansCode' => $this->invoice->payment_means_code ?? '30',
            'paymentTerms' => $this->invoice->payment_terms,

            // Invoice lines
            'invoiceLines' => $this->prepareInvoiceLines(),

            // IRN and signature (if exist)
            'irn' => $this->invoice->firs_irn ?? '',
            'signature' => $this->invoice->digital_signature ?? '',
        ];
    }

    /**
     * Prepare invoice line items
     */
    protected function prepareInvoiceLines(): array
    {
        $data = $this->invoice->data ?? [];
        $items = $data['items'] ?? [];
        $lines = [];

        foreach ($items as $index => $item) {
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;
            $lineAmount = $quantity * $price;
            $vatAmount = $lineAmount * (($this->invoice->vat_rate ?? 7.5) / 100);

            $lines[] = [
                'id' => $index + 1,
                'invoicedQuantity' => $quantity,
                'lineExtensionAmount' => number_format($lineAmount, 2, '.', ''),
                'item' => [
                    'name' => $item['description'] ?? '',
                    'sellersItemIdentification' => [
                        'id' => $item['code'] ?? ($index + 1),
                    ],
                ],
                'price' => [
                    'priceAmount' => number_format($price, 2, '.', ''),
                ],
                'taxTotal' => [
                    'taxAmount' => number_format($vatAmount, 2, '.', ''),
                ],
            ];
        }

        return $lines;
    }

    /**
     * Sign invoice with digital signature
     */
    protected function signInvoice(array $invoiceData): array
    {
        try {
            // Get ECDSA private key
            $ecdsaPrivateKeyPem = env('ECDSA_PRIVATE_KEY');

            if (empty($ecdsaPrivateKeyPem)) {
                $ecdsaPath = storage_path('app/ecdsa_private.pem');
                if (file_exists($ecdsaPath)) {
                    $ecdsaPrivateKeyPem = file_get_contents($ecdsaPath);
                }
            }

            if (!empty($ecdsaPrivateKeyPem)) {
                $nrsCredential = config('services.firs.api_key');
                $jadesInvoice = EInvoiceService::generateJAdESInvoice(
                    $invoiceData,
                    $nrsCredential,
                    $ecdsaPrivateKeyPem
                );

                $invoiceData['signature'] = $jadesInvoice['signature'] ?? '';
            }
        } catch (\Exception $e) {
            Log::warning('Failed to sign invoice', [
                'invoice_id' => $this->invoice->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $invoiceData;
    }

    /**
     * Handle failed job
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('FIRS submission job failed permanently', [
            'invoice_id' => $this->invoice->id,
            'error' => $exception->getMessage(),
        ]);

        $this->invoice->update([
            'firs_status' => 'failed',
            'firs_validation_errors' => json_encode([
                'error' => 'Submission failed after multiple attempts',
                'message' => $exception->getMessage(),
            ]),
        ]);
    }
}
