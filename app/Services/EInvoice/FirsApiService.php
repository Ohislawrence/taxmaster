<?php

namespace App\Services\EInvoice;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * FIRS E-Invoicing API Integration Service
 * Handles communication with the Federal Inland Revenue Service (FIRS) e-invoicing portal
 *
 * API Documentation: https://einvoice.firs.gov.ng/docs
 */
class FirsApiService
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected ?string $taxPayerId;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.firs.api_url', 'https://einvoice.firs.gov.ng/api/v1');
        $this->apiKey = config('services.firs.api_key');
        $this->taxPayerId = config('services.firs.taxpayer_id');
        $this->timeout = config('services.firs.timeout', 30);
    }

    /**
     * Check if FIRS API credentials are configured
     *
     * @return bool
     */
    public function hasCredentials(): bool
    {
        return !empty($this->apiKey) && !empty($this->taxPayerId);
    }

    /**
     * Authenticate with FIRS API and get access token
     *
     * @return string|null Access token
     */
    public function authenticate(): ?string
    {
        // Check if credentials are configured
        if (!$this->hasCredentials()) {
            return null;
        }

        // Check if we have a cached token
        $cachedToken = Cache::get('firs_api_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/auth/login", [
                    'taxpayer_id' => $this->taxPayerId,
                    'api_key' => $this->apiKey,
                ]);

            if ($response->successful()) {
                $token = $response->json('data.access_token');
                $expiresIn = $response->json('data.expires_in', 3600);

                // Cache token for 55 minutes (to be safe before expiry)
                Cache::put('firs_api_token', $token, now()->addSeconds($expiresIn - 300));

                return $token;
            }

            Log::error('FIRS authentication failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('FIRS authentication exception', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Submit invoice to FIRS e-invoicing portal
     *
     * @param array $invoiceData UBL-formatted invoice data
     * @return array Response from FIRS
     */
    public function submitInvoice(array $invoiceData): array
    {
        $token = $this->authenticate();

        if (!$token) {
            return [
                'success' => false,
                'error' => 'Failed to authenticate with FIRS',
                'error_code' => 'AUTH_FAILED',
            ];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->post("{$this->baseUrl}/invoices/submit", [
                    'invoice' => $invoiceData,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data'),
                    'firs_reference' => $response->json('data.reference_number'),
                    'submission_id' => $response->json('data.submission_id'),
                    'validation_status' => $response->json('data.validation_status'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message', 'Submission failed'),
                'error_code' => $response->json('error_code'),
                'validation_errors' => $response->json('data.validation_errors', []),
            ];
        } catch (\Exception $e) {
            Log::error('FIRS invoice submission exception', [
                'error' => $e->getMessage(),
                'invoice_number' => $invoiceData['invoiceNumber'] ?? 'unknown',
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => 'SUBMISSION_EXCEPTION',
            ];
        }
    }

    /**
     * Validate TIN (Tax Identification Number) with FIRS
     *
     * @param string $tin Tax Identification Number
     * @return array Validation result
     */
    public function validateTIN(string $tin): array
    {
        $token = $this->authenticate();

        if (!$token) {
            return [
                'valid' => false,
                'error' => 'Authentication failed',
            ];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->get("{$this->baseUrl}/taxpayers/validate/{$tin}");

            if ($response->successful()) {
                return [
                    'valid' => true,
                    'taxpayer_name' => $response->json('data.taxpayer_name'),
                    'taxpayer_type' => $response->json('data.taxpayer_type'),
                    'status' => $response->json('data.status'),
                ];
            }

            return [
                'valid' => false,
                'error' => $response->json('message', 'TIN validation failed'),
            ];
        } catch (\Exception $e) {
            Log::error('FIRS TIN validation exception', [
                'error' => $e->getMessage(),
                'tin' => $tin,
            ]);

            return [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get invoice status from FIRS
     *
     * @param string $firsReference FIRS reference number
     * @return array Invoice status
     */
    public function getInvoiceStatus(string $firsReference): array
    {
        $token = $this->authenticate();

        if (!$token) {
            return [
                'success' => false,
                'error' => 'Authentication failed',
            ];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->get("{$this->baseUrl}/invoices/status/{$firsReference}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json('data.status'),
                    'validation_status' => $response->json('data.validation_status'),
                    'submission_date' => $response->json('data.submission_date'),
                    'approved_date' => $response->json('data.approved_date'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message', 'Status check failed'),
            ];
        } catch (\Exception $e) {
            Log::error('FIRS invoice status check exception', [
                'error' => $e->getMessage(),
                'reference' => $firsReference,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel invoice in FIRS system
     *
     * @param string $firsReference FIRS reference number
     * @param string $reason Cancellation reason
     * @return array Cancellation result
     */
    public function cancelInvoice(string $firsReference, string $reason): array
    {
        $token = $this->authenticate();

        if (!$token) {
            return [
                'success' => false,
                'error' => 'Authentication failed',
            ];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->post("{$this->baseUrl}/invoices/cancel/{$firsReference}", [
                    'reason' => $reason,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => $response->json('message'),
                    'cancellation_id' => $response->json('data.cancellation_id'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message', 'Cancellation failed'),
            ];
        } catch (\Exception $e) {
            Log::error('FIRS invoice cancellation exception', [
                'error' => $e->getMessage(),
                'reference' => $firsReference,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Batch submit multiple invoices
     *
     * @param array $invoices Array of UBL-formatted invoices
     * @return array Batch submission result
     */
    public function batchSubmitInvoices(array $invoices): array
    {
        $token = $this->authenticate();

        if (!$token) {
            return [
                'success' => false,
                'error' => 'Authentication failed',
            ];
        }

        try {
            $response = Http::timeout($this->timeout * 2) // Double timeout for batch
                ->withToken($token)
                ->post("{$this->baseUrl}/invoices/batch-submit", [
                    'invoices' => $invoices,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'batch_id' => $response->json('data.batch_id'),
                    'total_submitted' => $response->json('data.total_submitted'),
                    'successful' => $response->json('data.successful'),
                    'failed' => $response->json('data.failed'),
                    'results' => $response->json('data.results', []),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message', 'Batch submission failed'),
            ];
        } catch (\Exception $e) {
            Log::error('FIRS batch submission exception', [
                'error' => $e->getMessage(),
                'invoice_count' => count($invoices),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
