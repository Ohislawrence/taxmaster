<?php

namespace App\Services;

use App\Models\Business;
use App\Models\TaxPayment;
use App\Models\TaxReturn;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentService
{
    protected $client;
    protected $secretKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('taxmaster.paystack.secret_key');
        $this->baseUrl = config('taxmaster.paystack.base_url');
        $this->client = new Client();
    }

    /**
     * Initialize Paystack payment
     */
    public function initializePayment(Business $business, TaxReturn $taxReturn, float $amount): array
    {
        try {
            $paymentRef = TaxPayment::generateReference();

            $response = $this->client->post("{$this->baseUrl}/transaction/initialize", [
                'headers' => [
                    'Authorization' => "Bearer {$this->secretKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'email' => $business->email,
                    'amount' => (int)($amount * 100), // Paystack expects amount in cents
                    'reference' => $paymentRef,
                    'metadata' => [
                        'business_id' => $business->id,
                        'tax_return_id' => $taxReturn->id,
                        'business_name' => $business->name,
                    ],
                ],
            ]);

            $result = json_decode($response->getBody(), true);

            if ($result['status']) {
                // Create payment record
                TaxPayment::create([
                    'business_id' => $business->id,
                    'tax_return_id' => $taxReturn->id,
                    'payment_reference' => $paymentRef,
                    'amount' => $amount,
                    'payment_method' => 'paystack',
                    'status' => 'pending',
                    'currency' => 'NGN',
                    'metadata' => [
                        'authorization_url' => $result['data']['authorization_url'],
                        'access_code' => $result['data']['access_code'],
                    ],
                ]);

                return [
                    'success' => true,
                    'authorization_url' => $result['data']['authorization_url'],
                    'access_code' => $result['data']['access_code'],
                    'reference' => $paymentRef,
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to initialize payment',
            ];
        } catch (Exception $e) {
            Log::error('Paystack initialization error', [
                'business_id' => $business->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Payment initialization failed',
            ];
        }
    }

    /**
     * Verify Paystack payment
     */
    public function verifyPayment(string $reference): array
    {
        try {
            $response = $this->client->get("{$this->baseUrl}/transaction/verify/{$reference}", [
                'headers' => [
                    'Authorization' => "Bearer {$this->secretKey}",
                ],
            ]);

            $result = json_decode($response->getBody(), true);

            if ($result['status'] && $result['data']['status'] === 'success') {
                // Update payment record
                $payment = TaxPayment::where('payment_reference', $reference)->first();

                if ($payment) {
                    $payment->update([
                        'status' => 'completed',
                        'paystack_reference' => $result['data']['reference'],
                        'payment_date' => now(),
                        'verified_at' => now(),
                        'paystack_response' => $result['data'],
                    ]);

                    // Update tax return balance
                    if ($payment->taxReturn) {
                        $payment->taxReturn->update([
                            'total_tax_paid' => $payment->taxReturn->total_tax_paid + $payment->amount,
                            'balance' => $payment->taxReturn->total_tax_due - ($payment->taxReturn->total_tax_paid + $payment->amount),
                            'status' => ($payment->taxReturn->total_tax_due <= $payment->taxReturn->total_tax_paid + $payment->amount) ? 'paid' : 'submitted',
                        ]);
                    }
                }

                return [
                    'success' => true,
                    'message' => 'Payment verified successfully',
                    'payment_data' => $result['data'],
                ];
            }

            return [
                'success' => false,
                'message' => 'Payment verification failed',
            ];
        } catch (Exception $e) {
            Log::error('Paystack verification error', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Payment verification failed',
            ];
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $reference): array
    {
        $payment = TaxPayment::where('payment_reference', $reference)->first();

        if (!$payment) {
            return [
                'success' => false,
                'message' => 'Payment not found',
            ];
        }

        return [
            'success' => true,
            'status' => $payment->status,
            'amount' => $payment->amount,
            'payment_date' => $payment->payment_date,
            'reference' => $reference,
        ];
    }

    /**
     * Handle Paystack webhook
     */
    public function handleWebhook(array $data): void
    {
        try {
            if ($data['event'] === 'charge.success') {
                $this->verifyPayment($data['data']['reference']);
            }
        } catch (Exception $e) {
            Log::error('Paystack webhook error', ['error' => $e->getMessage()]);
        }
    }
}
