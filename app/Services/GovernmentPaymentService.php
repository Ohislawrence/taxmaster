<?php

namespace App\Services;

use App\Models\GovernmentPayment;
use App\Models\PayeReturn;
use App\Models\WhtReturn;
use App\Models\Business;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GovernmentPaymentService
{
    /**
     * Remita API configuration
     */
    private string $remitaBaseUrl;
    private string $merchantId;
    private string $apiKey;
    private string $serviceTypeId;
    private string $environment;

    /**
     * Remita sandbox credentials for testing
     */
    private const SANDBOX_BASE_URL = 'https://remitademo.net/remita/exapp/api/v1/send/api';
    private const PRODUCTION_BASE_URL = 'https://login.remita.net/remita/exapp/api/v1/send/api';

    /**
     * Tax types that are always paid to FIRS (federal)
     */
    private const FEDERAL_TAX_TYPES = ['VAT', 'CIT'];

    public function __construct()
    {
        $this->environment = (string) config('services.remita.environment', 'sandbox');
        $this->merchantId = (string) config('services.remita.merchant_id', '');
        $this->apiKey = (string) config('services.remita.api_key', '');
        $this->serviceTypeId = (string) config('services.remita.service_type_id', '');

        // Auto-select base URL based on environment if not explicitly set
        $defaultUrl = $this->environment === 'production'
            ? self::PRODUCTION_BASE_URL
            : self::SANDBOX_BASE_URL;

        $this->remitaBaseUrl = (string) config('services.remita.base_url', $defaultUrl);
    }

    /**
     * Resolve the correct Remita Service Type ID based on tax type and state.
     *
     * - VAT and CIT always go to FIRS (federal).
     * - PAYE always goes to the staff's state SIRS.
     * - WHT goes to FIRS for companies, or state SIRS for individuals.
     *
     * @param string $taxType  PAYE|VAT|CIT|WHT
     * @param string|null $stateCode  Two-letter state code (e.g., 'LA' for Lagos)
     * @param string|null $beneficiaryType  'company'|'individual' (only for WHT)
     * @return string
     */
    public function resolveServiceTypeId(string $taxType, ?string $stateCode = null, ?string $beneficiaryType = null): string
    {
        // Federal taxes always go to FIRS
        if (in_array($taxType, self::FEDERAL_TAX_TYPES, true)) {
            return (string) config('nigerian_states.remita_service_types.firs', $this->serviceTypeId);
        }

        // WHT: route based on beneficiary type
        if ($taxType === 'WHT') {
            if ($beneficiaryType === 'individual' && $stateCode) {
                $stateServiceType = config("nigerian_states.remita_service_types.{$stateCode}");
                return $stateServiceType ?: (string) config('nigerian_states.remita_service_types.firs', $this->serviceTypeId);
            }
            // Companies → FIRS
            return (string) config('nigerian_states.remita_service_types.firs', $this->serviceTypeId);
        }

        // PAYE: always goes to the state SIRS
        if ($taxType === 'PAYE' && $stateCode) {
            $stateServiceType = config("nigerian_states.remita_service_types.{$stateCode}");
            return $stateServiceType ?: $this->serviceTypeId;
        }

        // Fallback to default
        return $this->serviceTypeId;
    }

    /**
     * Check if Remita is configured with real credentials
     */
    public function isConfigured(): bool
    {
        return !empty($this->merchantId) && !empty($this->apiKey);
    }

    /**
     * Check if running in sandbox/demo mode
     */
    public function isSandbox(): bool
    {
        return $this->environment !== 'production' || !$this->isConfigured();
    }

    /**
     * Generate Remita RRR for a tax payment
     *
     * @param string $taxType
     * @param mixed $return
     * @param Business $business
     * @return array
     */
    public function generateRRR(string $taxType, $return, Business $business): array
    {
        // RRR generation is disabled for now. Return a clear failure so callers
        // do not attempt to use Remita until credentials / integration are re-enabled.
        Log::warning('RRR generation disabled: generateRRR called', [
            'tax_type' => $taxType,
            'return_id' => $return->id ?? null,
            'business_id' => $business->id ?? null,
        ]);

        return [
            'success' => false,
            'message' => 'RRR generation is currently disabled. Enable Remita integration to generate RRRs.',
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'remitaConsumerKey=' . $this->merchantId . ',remitaConsumerToken=' . $this->generateHash($paymentDetails, $resolvedServiceTypeId),
            ])->post($this->remitaBaseUrl . '/echannelsvc/merchant/api/paymentinit', [
                'serviceTypeId' => $resolvedServiceTypeId,
                'amount' => $paymentDetails['amount'],
                'orderId' => $paymentDetails['order_id'],
                'payerName' => $paymentDetails['payer_name'],
                'payerEmail' => $paymentDetails['payer_email'],
                'payerPhone' => $paymentDetails['payer_phone'],
                'description' => $paymentDetails['description'],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'rrr' => $data['RRR'] ?? null,
                    'order_id' => $paymentDetails['order_id'],
                    'amount' => $paymentDetails['amount'],
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to generate RRR: ' . $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('Remita RRR generation failed', [
                'error' => $e->getMessage(),
                'payment_details' => $paymentDetails,
            ]);

            return [
                'success' => false,
                'message' => 'Error generating RRR: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Create a government payment record
     *
     * @param Business $business
     * @param string $taxType
     * @param mixed $return
     * @param float $amount
     * @param string $paymentMethod
     * @param string|null $remitaRRR
     * @return GovernmentPayment
     */
    public function createPayment(
        Business $business,
        string $taxType,
        $return,
        float $amount,
        string $paymentMethod = 'remita',
        ?string $remitaRRR = null
    ): GovernmentPayment {
        return GovernmentPayment::create([
            'business_id' => $business->id,
            'tax_type' => $taxType,
            'return_type' => get_class($return),
            'return_id' => $return->id,
            'period' => $return->period,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'remita_rrr' => $remitaRRR,
            'status' => $paymentMethod === 'remita' ? 'pending' : 'processing',
        ]);
    }

    /**
     * Verify Remita payment status
     *
     * @param string $rrr
     * @return array
     */
    public function verifyPayment(string $rrr): array
    {
        if (empty($this->merchantId) || empty($this->apiKey)) {
            return [
                'success' => true,
                'status' => 'completed',
                'message' => 'Mock verification - Remita not configured',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'remitaConsumerKey=' . $this->merchantId . ',remitaConsumerToken=' . $this->generateVerificationHash($rrr),
            ])->get($this->remitaBaseUrl . '/echannelsvc/' . $this->merchantId . '/' . $rrr . '/status.reg');

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['status'] ?? 'unknown';

                return [
                    'success' => true,
                    'status' => $this->mapRemitaStatus($status),
                    'amount' => $data['amount'] ?? null,
                    'payment_date' => $data['transactiontime'] ?? null,
                    'raw_response' => $data,
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to verify payment: ' . $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('Remita payment verification failed', [
                'rrr' => $rrr,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error verifying payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update payment status
     *
     * @param GovernmentPayment $payment
     * @param string $status
     * @param array $additionalData
     * @return GovernmentPayment
     */
    public function updatePaymentStatus(
        GovernmentPayment $payment,
        string $status,
        array $additionalData = []
    ): GovernmentPayment {
        $payment->update(array_merge([
            'status' => $status,
        ], $additionalData));

        // If payment is completed, update the associated return
        if ($status === 'completed' && $payment->return) {
            $payment->return->update(['status' => 'paid']);
        }

        return $payment->fresh();
    }

    /**
     * Get pending payments for a business
     *
     * @param int $businessId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingPayments(int $businessId)
    {
        return GovernmentPayment::where('business_id', $businessId)
            ->whereIn('status', ['pending', 'processing'])
            ->with('return', 'business')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get payment history for a business
     *
     * @param int $businessId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPaymentHistory(int $businessId, int $limit = 50)
    {
        return GovernmentPayment::where('business_id', $businessId)
            ->with('return', 'business')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Prepare payment details for Remita
     *
     * @param string $taxType
     * @param mixed $return
     * @param Business $business
     * @return array
     */
    private function preparePaymentDetails(string $taxType, $return, Business $business): array
    {
        $orderId = $taxType . '_' . $return->id . '_' . time();
        $amount = 0;
        $description = '';

        if ($return instanceof PayeReturn) {
            $amount = $return->total_tax_deducted;
            $stateCode = $return->tax_state ?? $business->state;
            $stateName = config("nigerian_states.state_options.{$stateCode}", $stateCode);
            $description = "PAYE Payment for {$return->period_label} — {$stateName} SIRS";
        } elseif ($return instanceof WhtReturn) {
            $amount = $return->total_wht_deducted;
            $authority = ($return->beneficiary_type === 'individual') ? 'SIRS' : 'FIRS';
            $stateCode = $return->tax_state ?? $business->state;
            $stateName = config("nigerian_states.state_options.{$stateCode}", $stateCode);
            $description = "WHT Payment for {$return->period_label} — {$authority}";
            if ($authority === 'SIRS') {
                $description .= " ({$stateName})";
            }
        } else {
            // CIT, VAT or other return types
            $amount = $return->total_tax ?? $return->amount ?? 0;
            $description = "{$taxType} Payment for " . ($return->period_label ?? $return->period ?? 'N/A') . ' — FIRS';
        }

        return [
            'order_id' => $orderId,
            'amount' => $amount,
            'payer_name' => $business->business_name,
            'payer_email' => $business->user->email ?? 'noreply@taxmaster.ng',
            'payer_phone' => $business->phone ?? '',
            'description' => $description,
            'tax_type' => $taxType,
        ];
    }

    /**
     * Generate hash for Remita API authentication
     *
     * @param array $paymentDetails
     * @param string|null $serviceTypeId  Resolved service type ID (state-aware)
     * @return string
     */
    private function generateHash(array $paymentDetails, ?string $serviceTypeId = null): string
    {
        $effectiveServiceTypeId = $serviceTypeId ?: $this->serviceTypeId;
        $hashString = $this->merchantId . $effectiveServiceTypeId . $paymentDetails['order_id'] . $paymentDetails['amount'] . $this->apiKey;
        return hash('sha512', $hashString);
    }

    /**
     * Generate hash for verification
     *
     * @param string $rrr
     * @return string
     */
    private function generateVerificationHash(string $rrr): string
    {
        $hashString = $rrr . $this->apiKey . $this->merchantId;
        return hash('sha512', $hashString);
    }

    /**
     * Map Remita status to internal status
     *
     * @param string $remitaStatus
     * @return string
     */
    private function mapRemitaStatus(string $remitaStatus): string
    {
        return match(strtolower($remitaStatus)) {
            'successful', '00', '01' => 'completed',
            'pending', '021' => 'processing',
            'failed', 'error' => 'failed',
            default => 'pending',
        };
    }

    /**
     * Generate mock RRR for testing (when Remita is not configured)
     *
     * @param array $paymentDetails
     * @return array
     */
    private function generateMockRRR(array $paymentDetails): array
    {
        $mockRRR = 'RRR' . str_pad(rand(100000000, 999999999), 12, '0', STR_PAD_LEFT);

        return [
            'success' => true,
            'rrr' => $mockRRR,
            'order_id' => $paymentDetails['order_id'],
            'amount' => $paymentDetails['amount'],
            'is_mock' => true,
        ];
    }
}
