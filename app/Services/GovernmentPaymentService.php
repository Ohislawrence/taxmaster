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

    public function __construct()
    {
        $this->remitaBaseUrl = (string) config('services.remita.base_url', 'https://login.remita.net/remita/exapp/api/v1/send/api');
        $this->merchantId = (string) config('services.remita.merchant_id', '');
        $this->apiKey = (string) config('services.remita.api_key', '');
        $this->serviceTypeId = (string) config('services.remita.service_type_id', '');
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
        // Prepare payment details based on tax type
        $paymentDetails = $this->preparePaymentDetails($taxType, $return, $business);

        // For now, generate a mock RRR until Remita integration is fully set up
        if (empty($this->merchantId) || empty($this->apiKey)) {
            return $this->generateMockRRR($paymentDetails);
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'remitaConsumerKey=' . $this->merchantId . ',remitaConsumerToken=' . $this->generateHash($paymentDetails),
            ])->post($this->remitaBaseUrl . '/echannelsvc/merchant/api/paymentinit', [
                'serviceTypeId' => $this->serviceTypeId,
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
            $description = "PAYE Payment for {$return->period_label}";
        } elseif ($return instanceof WhtReturn) {
            $amount = $return->total_wht_deducted;
            $description = "WHT Payment for {$return->period_label}";
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
     * @return string
     */
    private function generateHash(array $paymentDetails): string
    {
        $hashString = $this->merchantId . $this->serviceTypeId . $paymentDetails['order_id'] . $paymentDetails['amount'] . $this->apiKey;
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
