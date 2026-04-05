<?php

namespace App\Services;

use App\Models\Business;
use App\Models\QuickBooksConnection;
use App\Models\QuickBooksSyncLog;
use App\Models\Transaction;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Purchase;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QuickBooksIntegrationService
{
    protected ?DataService $dataService = null;
    protected ?QuickBooksConnection $connection = null;

    /**
     * Initialize DataService with credentials
     */
    public function initializeDataService(?QuickBooksConnection $connection = null): DataService
    {
        $this->connection = $connection;

        // Use connection's credentials if available, otherwise fall back to global config
        $clientId = $connection?->client_id ?? config('services.quickbooks.client_id');
        $clientSecret = $connection?->client_secret ?? config('services.quickbooks.client_secret');
        $redirectUri = $connection?->redirect_uri ?? config('services.quickbooks.redirect_uri');
        $environment = $connection?->environment ?? config('services.quickbooks.environment', 'sandbox');

        if (empty($clientId) || empty($clientSecret)) {
            throw new \Exception('QuickBooks credentials not configured. Please provide your QuickBooks app credentials.');
        }

        $config = [
            'auth_mode' => 'oauth2',
            'ClientID' => $clientId,
            'ClientSecret' => $clientSecret,
            'RedirectURI' => $redirectUri,
            'scope' => 'com.intuit.quickbooks.accounting',
            'baseUrl' => $environment === 'production'
                ? 'Production'
                : 'Development'
        ];

        $this->dataService = DataService::Configure($config);

        // If connection exists, set access token
        if ($connection) {
            $this->dataService->updateOAuth2Token($connection->access_token);

            $this->dataService->setCompanyID($connection->realm_id);

            // Refresh token if expired
            if ($connection->isTokenExpired()) {
                $this->refreshAccessToken($connection);
            }
        }

        return $this->dataService;
    }

    /**
     * Get OAuth 2.0 authorization URL
     */
    public function getAuthorizationUrl(QuickBooksConnection $connection): string
    {
        if (!$connection->hasValidCredentials()) {
            throw new \Exception('Please configure your QuickBooks credentials first.');
        }

        $dataService = $this->initializeDataService($connection);
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

        return $OAuth2LoginHelper->getAuthorizationCodeURL();
    }

    /**
     * Exchange authorization code for tokens
     */
    public function exchangeCodeForTokens(string $code, string $realmId, QuickBooksConnection $connection): QuickBooksConnection
    {
        $dataService = $this->initializeDataService($connection);
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

        $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
        $dataService->updateOAuth2Token($accessTokenObj);

        // Get company info
        $companyInfo = $dataService->getCompanyInfo();

        // Update connection with tokens and company info
        $connection->update([
            'realm_id' => $realmId,
            'company_name' => $companyInfo->CompanyName ?? null,
            'company_country' => $companyInfo->Country ?? 'NG',
            'access_token' => $accessTokenObj->getAccessToken(),
            'refresh_token' => $accessTokenObj->getRefreshToken(),
            'token_expires_at' => now()->addSeconds($accessTokenObj->getAccessTokenExpiresAt()),
            'refresh_token_expires_at' => now()->addSeconds($accessTokenObj->getRefreshTokenExpiresAt()),
            'status' => 'active',
            'metadata' => [
                'company_info' => $companyInfo,
                'connected_at' => now()->toIso8601String(),
            ],
        ]);

        Log::info('QuickBooks connection established', [
            'business_id' => $connection->business_id,
            'realm_id' => $realmId,
            'company' => $companyInfo->CompanyName ?? 'Unknown',
        ]);

        return $connection;
    }

    /**
     * Refresh access token
     */
    public function refreshAccessToken(QuickBooksConnection $connection): void
    {
        try {
            $dataService = $this->initializeDataService($connection);
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

            $accessTokenObj = $OAuth2LoginHelper->refreshToken();
            $dataService->updateOAuth2Token($accessTokenObj);

            $connection->updateTokens(
                $accessTokenObj->getAccessToken(),
                $accessTokenObj->getRefreshToken(),
                $accessTokenObj->getAccessTokenExpiresAt(),
                $accessTokenObj->getRefreshTokenExpiresAt()
            );

            Log::info('QuickBooks token refreshed', ['connection_id' => $connection->id]);
        } catch (\Exception $e) {
            Log::error('Failed to refresh QuickBooks token', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage(),
            ]);

            $connection->markError('Token refresh failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Disconnect and revoke tokens
     */
    public function disconnect(QuickBooksConnection $connection): void
    {
        try {
            $dataService = $this->initializeDataService($connection);
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

            $OAuth2LoginHelper->revokeToken($connection->access_token);

            $connection->update(['status' => 'revoked']);

            Log::info('QuickBooks connection revoked', ['connection_id' => $connection->id]);
        } catch (\Exception $e) {
            Log::warning('Failed to revoke QuickBooks token', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage(),
            ]);

            // Still mark as revoked locally
            $connection->update(['status' => 'revoked']);
        }
    }

    /**
     * Sync invoices from QuickBooks to TaxMaster
     */
    public function syncInvoicesFromQuickBooks(QuickBooksConnection $connection, ?Carbon $fromDate = null, ?Carbon $toDate = null): QuickBooksSyncLog
    {
        $syncLog = QuickBooksSyncLog::create([
            'quickbooks_connection_id' => $connection->id,
            'sync_type' => $fromDate ? 'incremental' : 'full',
            'entity_type' => 'invoice',
            'status' => 'queued',
            'sync_from_date' => $fromDate,
            'sync_to_date' => $toDate ?? now(),
        ]);

        try {
            $syncLog->markStarted();
            $dataService = $this->initializeDataService($connection);

            // Build query
            $fromDateStr = $fromDate ? $fromDate->format('Y-m-d') : '1970-01-01';
            $toDateStr = $toDate ? $toDate->format('Y-m-d') : now()->format('Y-m-d');

            $query = "SELECT * FROM Invoice WHERE TxnDate >= '$fromDateStr' AND TxnDate <= '$toDateStr' MAXRESULTS 1000";

            $invoices = $dataService->Query($query);
            $syncLog->update(['total_records' => count($invoices)]);

            $business = $connection->business;
            $successCount = 0;
            $failureCount = 0;

            foreach ($invoices as $invoice) {
                try {
                    // Convert QuickBooks invoice to TaxMaster transaction
                    $transaction = $this->convertInvoiceToTransaction($invoice, $business, $connection);

                    if ($transaction) {
                        $syncLog->incrementProcessed('success');
                        $successCount++;
                    } else {
                        $syncLog->incrementProcessed('skipped');
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to sync QB invoice', [
                        'invoice_id' => $invoice->Id,
                        'error' => $e->getMessage(),
                    ]);

                    $syncLog->incrementProcessed('failure');
                    $failureCount++;
                }
            }

            $syncLog->markCompleted([
                'invoices_synced' => $successCount,
                'failures' => $failureCount,
                'from_date' => $fromDateStr,
                'to_date' => $toDateStr,
            ]);

            $connection->updateSyncStatus('success');

        } catch (\Exception $e) {
            Log::error('QuickBooks invoice sync failed', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage(),
            ]);

            $syncLog->markFailed($e->getMessage());
            $connection->updateSyncStatus('failed', $e->getMessage());
        }

        return $syncLog;
    }

    /**
     * Sync bills/expenses from QuickBooks to TaxMaster
     */
    public function syncBillsFromQuickBooks(QuickBooksConnection $connection, ?Carbon $fromDate = null, ?Carbon $toDate = null): QuickBooksSyncLog
    {
        $syncLog = QuickBooksSyncLog::create([
            'quickbooks_connection_id' => $connection->id,
            'sync_type' => $fromDate ? 'incremental' : 'full',
            'entity_type' => 'bill',
            'status' => 'queued',
            'sync_from_date' => $fromDate,
            'sync_to_date' => $toDate ?? now(),
        ]);

        try {
            $syncLog->markStarted();
            $dataService = $this->initializeDataService($connection);

            $fromDateStr = $fromDate ? $fromDate->format('Y-m-d') : '1970-01-01';
            $toDateStr = $toDate ? $toDate->format('Y-m-d') : now()->format('Y-m-d');

            $query = "SELECT * FROM Purchase WHERE TxnDate >= '$fromDateStr' AND TxnDate <= '$toDateStr' MAXRESULTS 1000";

            $bills = $dataService->Query($query);
            $syncLog->update(['total_records' => count($bills)]);

            $business = $connection->business;

            foreach ($bills as $bill) {
                try {
                    $this->convertBillToTransaction($bill, $business, $connection);
                    $syncLog->incrementProcessed('success');
                } catch (\Exception $e) {
                    Log::error('Failed to sync QB bill', [
                        'bill_id' => $bill->Id,
                        'error' => $e->getMessage(),
                    ]);

                    $syncLog->incrementProcessed('failure');
                }
            }

            $syncLog->markCompleted([
                'bills_synced' => $syncLog->success_count,
                'from_date' => $fromDateStr,
                'to_date' => $toDateStr,
            ]);

            $connection->updateSyncStatus('success');

        } catch (\Exception $e) {
            $syncLog->markFailed($e->getMessage());
            $connection->updateSyncStatus('failed', $e->getMessage());
        }

        return $syncLog;
    }

    /**
     * Convert QuickBooks invoice to TaxMaster transaction
     */
    protected function convertInvoiceToTransaction($invoice, Business $business, QuickBooksConnection $connection): ?Transaction
    {
        return Transaction::updateOrCreate(
            [
                'quickbooks_id' => $invoice->Id,
                'business_id' => $business->id,
            ],
            [
                'type' => 'credit',
                'amount' => (float) $invoice->TotalAmt,
                'description' => "Invoice {$invoice->DocNumber}" . ($invoice->CustomerRef ? " - {$invoice->CustomerRef}" : ''),
                'transaction_date' => Carbon::parse($invoice->TxnDate),
                'counterparty' => $invoice->CustomerRef ?? null,
                'reference' => $invoice->DocNumber,
                'category' => 'REVENUE',
                'sub_category' => 'VAT_OUTPUT', // AI will refine this
                'quickbooks_synced_at' => now(),
                'quickbooks_sync_enabled' => true,
            ]
        );
    }

    /**
     * Convert QuickBooks bill to TaxMaster transaction
     */
    protected function convertBillToTransaction($bill, Business $business, QuickBooksConnection $connection): ?Transaction
    {
        return Transaction::updateOrCreate(
            [
                'quickbooks_id' => $bill->Id,
                'business_id' => $business->id,
            ],
            [
                'type' => 'debit',
                'amount' => (float) $bill->TotalAmt,
                'description' => "Bill {$bill->DocNumber}" . ($bill->VendorRef ? " - {$bill->VendorRef}" : ''),
                'transaction_date' => Carbon::parse($bill->TxnDate),
                'counterparty' => $bill->VendorRef ?? null,
                'reference' => $bill->DocNumber,
                'category' => 'EXPENSES',
                'sub_category' => null, // AI will categorize
                'quickbooks_synced_at' => now(),
                'quickbooks_sync_enabled' => true,
            ]
        );
    }

    /**
     * Get QuickBooks company info
     */
    public function getCompanyInfo(QuickBooksConnection $connection): array
    {
        $dataService = $this->initializeDataService($connection);
        $companyInfo = $dataService->getCompanyInfo();

        return [
            'company_name' => $companyInfo->CompanyName ?? null,
            'legal_name' => $companyInfo->LegalName ?? null,
            'country' => $companyInfo->Country ?? null,
            'fiscal_year_start' => $companyInfo->FiscalYearStartMonth ?? null,
            'currency' => $companyInfo->Currency ?? null,
        ];
    }
}
