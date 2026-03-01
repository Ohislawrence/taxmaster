<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class MonoIntegrationService
{
    protected string $baseUrl;
    protected ?string $secretKey;
    protected ?string $publicKey;

    public function __construct()
    {
        $this->baseUrl = config('services.mono.base_url', 'https://api.withmono.com');
        $this->secretKey = config('services.mono.secret_key');
        $this->publicKey = config('services.mono.public_key');
    }

    /**
     * Verify Mono credentials are configured
     */
    private function verifyCredentials(): void
    {
        if (!$this->secretKey) {
            throw new \Exception('Mono API secret key is not configured. Set MONO_SECRET_KEY in .env file.');
        }
    }

    /**
     * Exchange authorization code for account ID and token
     * Uses Mono API v2: POST /v2/accounts/auth (plural "accounts")
     */
    public function exchangeToken(string $code): array
    {
        $this->verifyCredentials();

        $response = Http::withHeaders([
            'mono-sec-key' => $this->secretKey,
            'Content-Type' => 'application/json',
            'accept' => 'application/json',
        ])->post("{$this->baseUrl}/v2/accounts/auth", [
            'code' => $code,
        ]);

        if (!$response->successful()) {
            Log::error('Mono token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Failed to exchange Mono token: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Get account details
     * Updated for Mono API v2 endpoints
     */
    public function getAccountDetails(string $accountId): array
    {
        $this->verifyCredentials();

        // Try v2 endpoint path
        $response = Http::withHeaders([
            'mono-sec-key' => $this->secretKey,
        ])->get("{$this->baseUrl}/v2/accounts/{$accountId}");

        if (!$response->successful()) {
            Log::error('Mono account details fetch failed', [
                'account_id' => $accountId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Failed to fetch account details: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Sync transactions for a bank account
     * Updated for Mono API v2 endpoints
     */
    public function syncTransactions(BankAccount $bankAccount, ?string $startDate = null, ?string $endDate = null): int
    {
        $this->verifyCredentials();

        if (!$startDate) {
            // Default: last 6 months
            $startDate = now()->subMonths(6)->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = now()->format('Y-m-d');
        }

        try {
            // Use v2 endpoint path
            $response = Http::withHeaders([
                'mono-sec-key' => $this->secretKey,
            ])->get("{$this->baseUrl}/v2/accounts/{$bankAccount->mono_account_id}/transactions", [
                'start' => $startDate,
                'end' => $endDate,
                'paginate' => false,
            ]);

            if (!$response->successful()) {
                Log::error('Mono transaction sync failed', [
                    'account_id' => $bankAccount->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Failed to sync transactions: ' . $response->body());
            }

            $data = $response->json();
            $transactions = $data['data'] ?? [];
            $syncedCount = 0;

            foreach ($transactions as $txn) {
                $transaction = Transaction::updateOrCreate(
                    [
                        'mono_transaction_id' => $txn['_id'],
                    ],
                    [
                        'bank_account_id' => $bankAccount->id,
                        'business_id' => $bankAccount->business_id,
                        'type' => $txn['type'], // debit or credit
                        'amount' => abs($txn['amount']),
                        'currency' => $txn['currency'] ?? 'NGN',
                        'description' => $txn['narration'] ?? '',
                        'counterparty' => $txn['meta']['sender'] ?? $txn['meta']['recipient'] ?? null,
                        'transaction_date' => $txn['date'],
                        'balance' => $txn['balance'] ?? null,
                        'meta' => $txn,
                    ]
                );

                // If this is a new transaction and doesn't have a category, queue it for categorization
                if ($transaction->wasRecentlyCreated && !$transaction->category) {
                    \App\Jobs\CategorizeTransaction::dispatch($transaction);
                }

                $syncedCount++;
            }

            // Update last synced timestamp
            $bankAccount->update([
                'last_synced_at' => now(),
            ]);

            Log::info('Transactions synced successfully', [
                'account_id' => $bankAccount->id,
                'count' => $syncedCount,
            ]);

            return $syncedCount;
        } catch (\Exception $e) {
            Log::error('Transaction sync exception', [
                'account_id' => $bankAccount->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Unlink account
     * Updated for Mono API v2 endpoints
     */
    public function unlinkAccount(string $accountId): bool
    {
        $this->verifyCredentials();

        $response = Http::withHeaders([
            'mono-sec-key' => $this->secretKey,
        ])->post("{$this->baseUrl}/v2/accounts/{$accountId}/unlink");

        if (!$response->successful()) {
            Log::error('Mono account unlink failed', [
                'account_id' => $accountId,
                'status' => $response->status(),
            ]);
            return false;
        }

        return true;
    }

    /**
     * Get account statement (PDF)
     * Updated for Mono API v2 endpoints
     */
    public function getAccountStatement(string $accountId, string $startDate, string $endDate): ?string
    {
        $this->verifyCredentials();

        $response = Http::withHeaders([
            'mono-sec-key' => $this->secretKey,
        ])->get("{$this->baseUrl}/v2/accounts/{$accountId}/statement", [
            'start' => $startDate,
            'end' => $endDate,
            'output' => 'pdf',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['path'] ?? null;
        }

        return null;
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $webhookSecret = config('services.mono.webhook_secret');
        if (!$webhookSecret) {
            return false;
        }
        $computedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($computedSignature, $signature);
    }

    /**
     * Handle webhook event
     */
    public function handleWebhook(array $event): void
    {
        $eventType = $event['event'] ?? null;

        switch ($eventType) {
            case 'mono.events.account_updated':
                $this->handleAccountUpdated($event['data']);
                break;

            case 'mono.events.account_reauthorization':
                $this->handleAccountReauthorization($event['data']);
                break;

            case 'mono.events.transactions_updated':
                $this->handleTransactionsUpdated($event['data']);
                break;

            default:
                Log::info('Unhandled Mono webhook event', ['event' => $eventType]);
        }
    }

    protected function handleAccountUpdated(array $data): void
    {
        $account = BankAccount::where('mono_account_id', $data['account'])->first();
        if ($account) {
            $this->syncTransactions($account);
        }
    }

    protected function handleAccountReauthorization(array $data): void
    {
        $account = BankAccount::where('mono_account_id', $data['account'])->first();
        if ($account) {
            $account->update(['is_active' => false]);
            Log::warning('Bank account needs reauthorization', ['account_id' => $account->id]);
        }
    }

    protected function handleTransactionsUpdated(array $data): void
    {
        $account = BankAccount::where('mono_account_id', $data['account'])->first();
        if ($account && $account->auto_sync) {
            $this->syncTransactions($account);
        }
    }
}
