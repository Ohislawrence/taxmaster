<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\ShopifyConnection;
use App\Services\ShopifyIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class ShopifyController extends Controller
{
    public function __construct(
        protected ShopifyIntegrationService $shopifyService
    ) {}

    /**
     * Show Shopify integration page
     */
    public function index(Request $request)
    {
        $business = $request->user()->defaultBusiness();
        if (!$business) {
            return redirect()->route('business.setup')
                ->with('error', 'Please complete your business setup first.');
        }

        $connection = $business->shopifyConnection;
        $syncLogs = $connection ? $connection->syncLogs()->latest()->take(10)->get() : collect([]);

        return Inertia::render('Business/Integrations/Shopify', [
            'connection' => $connection ? [
                'id' => $connection->id,
                'shop_domain' => $connection->shop_domain,
                'shop_name' => $connection->shop_name,
                'shop_email' => $connection->shop_email,
                'shop_currency' => $connection->shop_currency,
                'status' => $connection->status,
                'has_credentials' => $connection->has_credentials,
                'last_synced_at' => $connection->last_synced_at,
                'last_sync_status' => $connection->last_sync_status,
                'auto_sync_enabled' => $connection->auto_sync_enabled,
                'sync_frequency' => $connection->sync_frequency,
                'sync_settings' => $connection->sync_settings ?? [
                    'sync_orders' => true,
                    'sync_products' => false,
                ],
                'is_active' => $connection->isActive(),
                'admin_url' => $connection->admin_url,
                'total_orders_synced' => $connection->total_orders_synced,
                'total_products_synced' => $connection->total_products_synced,
                'first_sync_at' => $connection->first_sync_at,
                'created_at' => $connection->created_at,
            ] : null,
            'syncLogs' => $syncLogs->map(fn($log) => [
                'id' => $log->id,
                'sync_type' => $log->sync_type,
                'entity_type' => $log->entity_type,
                'status' => $log->status,
                'total_records' => $log->total_records,
                'success_count' => $log->success_count,
                'failure_count' => $log->failure_count,
                'started_at' => $log->started_at,
                'duration_seconds' => $log->duration_seconds,
                'error_message' => $log->error_message,
                'summary' => $log->summary,
            ]),
        ]);
    }

    /**
     * Save or update Shopify credentials
     */
    public function saveCredentials(Request $request)
    {
        $business = $request->user()->defaultBusiness();
        if (!$business) {
            return back()->with('error', 'Please complete your business setup first.');
        }

        $validated = $request->validate([
            'shop_domain' => 'required|string|regex:/^[a-zA-Z0-9\-]+\.myshopify\.com$/',
            'access_token' => 'required|string',
        ]);

        try {
            // Create or update connection
            $connection = ShopifyConnection::updateOrCreate(
                ['business_id' => $business->id],
                [
                    'shop_domain' => $validated['shop_domain'],
                    'access_token' => $validated['access_token'],
                    'status' => 'credentials_set',
                ]
            );

            // Verify the connection by fetching shop details
            if ($this->shopifyService->verifyConnection($connection)) {
                return back()->with('success', "Successfully connected to Shopify store: {$connection->shop_name}");
            } else {
                $connection->update(['status' => 'error']);
                return back()->with('error', 'Failed to verify Shopify connection. Please check your credentials.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to save Shopify credentials', [
                'business_id' => $business->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to connect to Shopify: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect from Shopify
     */
    public function disconnect(Request $request)
    {
        $business = $request->user()->defaultBusiness();
        $connection = $business?->shopifyConnection;

        if (!$connection) {
            return back()->with('error', 'No Shopify connection found.');
        }

        try {
            $this->shopifyService->disconnect($connection);

            return back()->with('success', 'Successfully disconnected from Shopify.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to disconnect: ' . $e->getMessage());
        }
    }

    /**
     * Manually trigger sync
     */
    public function sync(Request $request)
    {
        $business = $request->user()->defaultBusiness();
        $connection = $business?->shopifyConnection;

        if (!$connection) {
            return back()->with('error', 'No Shopify connection found.');
        }

        if (!$connection->isActive()) {
            return back()->with('error', 'Shopify connection is not active. Please reconnect.');
        }

        $request->validate([
            'date_range' => 'nullable|in:last_30_days,last_month,last_3_months,last_6_months,this_year,all_time',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        // Handle date range
        if ($request->filled('date_range')) {
            $dateRange = match($request->input('date_range')) {
                'last_30_days' => [Carbon::now()->subDays(30), Carbon::now()],
                'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
                'last_3_months' => [Carbon::now()->subMonths(3), Carbon::now()],
                'last_6_months' => [Carbon::now()->subMonths(6), Carbon::now()],
                'this_year' => [Carbon::now()->startOfYear(), Carbon::now()],
                'all_time' => [null, Carbon::now()], // Shopify doesn't have time limits
                default => [Carbon::now()->subDays(30), Carbon::now()],
            };
            [$fromDate, $toDate] = $dateRange;
        } else {
            $fromDate = $request->input('from_date') ? Carbon::parse($request->input('from_date')) : Carbon::now()->subDays(30);
            $toDate = $request->input('to_date') ? Carbon::parse($request->input('to_date')) : Carbon::now();
        }

        try {
            $syncLog = $this->shopifyService->syncOrders($connection, $fromDate, $toDate);

            if ($syncLog->wasSuccessful()) {
                return back()->with('success', "Sync completed successfully. Synced {$syncLog->success_count} orders.");
            } else {
                return back()->with('warning', "Sync completed with errors. Check sync logs for details.");
            }
        } catch (\Exception $e) {
            Log::error('Shopify manual sync failed', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Update sync settings
     */
    public function updateSettings(Request $request)
    {
        $business = $request->user()->defaultBusiness();
        $connection = $business?->shopifyConnection;

        if (!$connection) {
            return back()->with('error', 'No Shopify connection found.');
        }

        $request->validate([
            'auto_sync_enabled' => 'required|boolean',
            'sync_frequency' => 'required|in:hourly,daily,weekly',
            'sync_settings' => 'nullable|array',
            'sync_settings.sync_orders' => 'boolean',
            'sync_settings.sync_products' => 'boolean',
        ]);

        $connection->update([
            'auto_sync_enabled' => $request->input('auto_sync_enabled'),
            'sync_frequency' => $request->input('sync_frequency'),
            'sync_settings' => $request->input('sync_settings', $connection->sync_settings),
        ]);

        return back()->with('success', 'Sync settings updated successfully.');
    }

    /**
     * Get sync log details
     */
    public function getSyncLog(Request $request, $logId)
    {
        $business = $request->user()->defaultBusiness();
        $connection = $business?->shopifyConnection;

        if (!$connection) {
            return response()->json(['error' => 'No connection found'], 404);
        }

        $syncLog = $connection->syncLogs()->find($logId);

        if (!$syncLog) {
            return response()->json(['error' => 'Sync log not found'], 404);
        }

        return response()->json([
            'id' => $syncLog->id,
            'sync_type' => $syncLog->sync_type,
            'entity_type' => $syncLog->entity_type,
            'status' => $syncLog->status,
            'total_records' => $syncLog->total_records,
            'processed_records' => $syncLog->processed_records,
            'success_count' => $syncLog->success_count,
            'failure_count' => $syncLog->failure_count,
            'skipped_count' => $syncLog->skipped_count,
            'started_at' => $syncLog->started_at,
            'completed_at' => $syncLog->completed_at,
            'duration_seconds' => $syncLog->duration_seconds,
            'error_message' => $syncLog->error_message,
            'errors' => $syncLog->errors,
            'summary' => $syncLog->summary,
            'metadata' => $syncLog->metadata,
            'progress_percentage' => $syncLog->getProgressPercentage(),
            'is_complete' => $syncLog->isComplete(),
            'was_successful' => $syncLog->wasSuccessful(),
        ]);
    }
}
