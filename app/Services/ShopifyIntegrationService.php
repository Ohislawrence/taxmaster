<?php

namespace App\Services;

use App\Models\Business;
use App\Models\ShopifyConnection;
use App\Models\ShopifySyncLog;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShopifyIntegrationService
{
    protected ?ShopifyConnection $connection = null;

    /**
     * Set the Shopify connection
     */
    public function setConnection(ShopifyConnection $connection): self
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Verify Shopify connection by testing API access
     */
    public function verifyConnection(ShopifyConnection $connection): bool
    {
        try {
            $response = $this->makeApiRequest($connection, 'GET', '/admin/api/2024-01/shop.json');

            if ($response->successful()) {
                $shopData = $response->json('shop');

                // Update connection with shop details
                $connection->update([
                    'shop_name' => $shopData['name'] ?? null,
                    'shop_email' => $shopData['email'] ?? null,
                    'shop_currency' => $shopData['currency'] ?? 'NGN',
                    'status' => 'active',
                    'metadata' => [
                        'shop_owner' => $shopData['shop_owner'] ?? null,
                        'plan_name' => $shopData['plan_name'] ?? null,
                        'timezone' => $shopData['timezone'] ?? null,
                        'verified_at' => now()->toIso8601String(),
                    ],
                ]);

                Log::info('Shopify connection verified', [
                    'business_id' => $connection->business_id,
                    'shop_domain' => $connection->shop_domain,
                    'shop_name' => $connection->shop_name,
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Shopify connection verification failed', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage(),
            ]);

            $connection->update([
                'status' => 'error',
                'last_error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Sync orders from Shopify
     */
    public function syncOrders(ShopifyConnection $connection, ?Carbon $fromDate = null, ?Carbon $toDate = null): ShopifySyncLog
    {
        $this->connection = $connection;

        // Create sync log
        $syncLog = ShopifySyncLog::create([
            'shopify_connection_id' => $connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'processing',
            'started_at' => now(),
            'sync_from_date' => $fromDate,
            'sync_to_date' => $toDate ?? now(),
        ]);

        try {
            // Build query parameters
            $params = [
                'status' => 'any',
                'limit' => 250, // Shopify max limit
            ];

            if ($fromDate) {
                $params['created_at_min'] = $fromDate->toIso8601String();
            }

            if ($toDate) {
                $params['created_at_max'] = $toDate->toIso8601String();
            }

            $orders = $this->fetchAllOrders($connection, $params);

            $syncLog->update(['total_records' => count($orders)]);

            $successCount = 0;
            $failureCount = 0;
            $errors = [];

            foreach ($orders as $order) {
                try {
                    $this->processOrder($connection->business, $order);
                    $successCount++;
                } catch (\Exception $e) {
                    $failureCount++;
                    $errors[] = [
                        'order_id' => $order['id'] ?? 'unknown',
                        'order_number' => $order['order_number'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];

                    Log::warning('Failed to process Shopify order', [
                        'order_id' => $order['id'] ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }

                $syncLog->update([
                    'processed_records' => $successCount + $failureCount,
                    'success_count' => $successCount,
                    'failure_count' => $failureCount,
                ]);
            }

            // Complete sync log
            $syncLog->update([
                'status' => $failureCount === 0 ? 'completed' : 'failed',
                'completed_at' => now(),
                'duration_seconds' => $syncLog->started_at->diffInSeconds(now()),
                'errors' => $errors,
                'summary' => [
                    'total_orders' => count($orders),
                    'successful' => $successCount,
                    'failed' => $failureCount,
                    'total_value' => collect($orders)->sum('total_price'),
                ],
            ]);

            // Update connection stats
            $connection->update([
                'last_synced_at' => now(),
                'last_sync_status' => $failureCount === 0 ? 'success' : 'failed',
            ]);

            $connection->updateSyncStats($successCount, 0, 0);

            Log::info('Shopify order sync completed', [
                'sync_log_id' => $syncLog->id,
                'total' => count($orders),
                'success' => $successCount,
                'failed' => $failureCount,
            ]);

        } catch (\Exception $e) {
            $syncLog->update([
                'status' => 'failed',
                'completed_at' => now(),
                'duration_seconds' => $syncLog->started_at->diffInSeconds(now()),
                'error_message' => $e->getMessage(),
            ]);

            $connection->update([
                'last_error' => $e->getMessage(),
            ]);

            Log::error('Shopify sync failed', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $syncLog;
    }

    /**
     * Fetch all orders with pagination
     */
    protected function fetchAllOrders(ShopifyConnection $connection, array $params): array
    {
        $allOrders = [];
        $pageInfo = null;

        do {
            $queryParams = $params;

            if ($pageInfo) {
                $queryParams['page_info'] = $pageInfo;
            }

            $response = $this->makeApiRequest($connection, 'GET', '/admin/api/2024-01/orders.json', $queryParams);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch orders from Shopify: ' . $response->body());
            }

            $orders = $response->json('orders', []);
            $allOrders = array_merge($allOrders, $orders);

            // Check for pagination
            $linkHeader = $response->header('Link');
            $pageInfo = $this->extractNextPageInfo($linkHeader);

        } while ($pageInfo);

        return $allOrders;
    }

    /**
     * Process a single order and create transaction
     */
    protected function processOrder(Business $business, array $order): Transaction
    {
        // Extract order details
        $orderNumber = $order['order_number'] ?? $order['name'] ?? 'N/A';
        $totalPrice = (float) ($order['total_price'] ?? 0);
        $currency = $order['currency'] ?? 'NGN';
        $createdAt = Carbon::parse($order['created_at']);
        $customer = $order['customer'] ?? [];

        // Determine VAT amount (Shopify includes tax details)
        $taxLines = $order['tax_lines'] ?? [];
        $vatAmount = collect($taxLines)->sum('price');

        // Create or update transaction
        $transaction = Transaction::updateOrCreate(
            [
                'business_id' => $business->id,
                'external_id' => 'shopify_order_' . $order['id'],
            ],
            [
                'description' => "Shopify Order #{$orderNumber}",
                'amount' => $totalPrice,
                'type' => 'INCOME',
                'category' => 'VAT_OUTPUT', // Sales categorized as VAT output
                'transaction_date' => $createdAt,
                'counterparty_name' => $customer['first_name'] . ' ' . $customer['last_name'] ?? 'Shopify Customer',
                'counterparty_email' => $customer['email'] ?? null,
                'vat_amount' => $vatAmount,
                'vat_rate' => $vatAmount > 0 ? ($vatAmount / ($totalPrice - $vatAmount)) * 100 : 0,
                'source' => 'shopify',
                'metadata' => [
                    'shopify_order_id' => $order['id'],
                    'order_number' => $orderNumber,
                    'currency' => $currency,
                    'financial_status' => $order['financial_status'] ?? null,
                    'fulfillment_status' => $order['fulfillment_status'] ?? null,
                    'line_items_count' => count($order['line_items'] ?? []),
                ],
            ]
        );

        return $transaction;
    }

    /**
     * Make API request to Shopify
     */
    protected function makeApiRequest(ShopifyConnection $connection, string $method, string $endpoint, array $params = [])
    {
        $shopDomain = $connection->shop_domain;
        $accessToken = $connection->access_token;

        if (empty($shopDomain) || empty($accessToken)) {
            throw new \Exception('Shopify connection not properly configured');
        }

        $url = "https://{$shopDomain}{$endpoint}";

        return Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Content-Type' => 'application/json',
        ])->send($method, $url, [
            'query' => $params,
        ]);
    }

    /**
     * Extract next page info from Link header
     */
    protected function extractNextPageInfo(?string $linkHeader): ?string
    {
        if (!$linkHeader) {
            return null;
        }

        // Parse Link header for rel="next"
        preg_match('/<[^>]*page_info=([^>&]+)[^>]*>; rel="next"/', $linkHeader, $matches);

        return $matches[1] ?? null;
    }

    /**
     * Disconnect Shopify integration
     */
    public function disconnect(ShopifyConnection $connection): void
    {
        $connection->update([
            'status' => 'revoked',
            'access_token' => null,
        ]);

        Log::info('Shopify connection disconnected', [
            'business_id' => $connection->business_id,
            'shop_domain' => $connection->shop_domain,
        ]);
    }
}
