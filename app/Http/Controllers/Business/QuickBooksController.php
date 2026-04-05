<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\QuickBooksConnection;
use App\Services\QuickBooksIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class QuickBooksController extends Controller
{
    public function __construct(
        protected QuickBooksIntegrationService $qbService
    ) {}

    /**
     * Show QuickBooks integration page
     */
    public function index(Request $request)
    {
        $business = $request->user()->defaultBusiness();
        if (!$business) {
            return redirect()->route('business.setup')
                ->with('error', 'Please complete your business setup first.');
        }

        $connection = $business->quickBooksConnection;
        $syncLogs = $connection ? $connection->syncLogs()->latest()->take(10)->get() : collect([]);

        return Inertia::render('Business/Integrations/QuickBooks', [
            'connection' => $connection ? [
                'id' => $connection->id,
                'company_name' => $connection->company_name,
                'status' => $connection->status,
                'environment' => $connection->environment,
                'has_credentials' => $connection->hasValidCredentials(),
                'last_synced_at' => $connection->last_synced_at,
                'last_sync_status' => $connection->last_sync_status,
                'auto_sync_enabled' => $connection->auto_sync_enabled,
                'sync_frequency' => $connection->sync_frequency,
                'sync_settings' => $connection->sync_settings ?? [
                    'sync_invoices' => true,
                    'sync_bills' => true,
                ],
                'is_active' => $connection->isActive(),
                'is_sync_due' => $connection->isSyncDue(),
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
            ]),
            'qbEnabled' => config('services.quickbooks.enabled', false),
        ]);
    }

    /**
     * Save or update QuickBooks credentials
     */
    public function saveCredentials(Request $request)
    {
        $business = $request->user()->defaultBusiness();
        if (!$business) {
            return back()->with('error', 'Please complete your business setup first.');
        }

        $validated = $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'redirect_uri' => 'required|url',
            'environment' => 'required|in:sandbox,production',
        ]);

        // Create or update connection with credentials only (no OAuth yet)
        $connection = QuickBooksConnection::updateOrCreate(
            ['business_id' => $business->id],
            [
                'client_id' => $validated['client_id'],
                'client_secret' => $validated['client_secret'],
                'redirect_uri' => $validated['redirect_uri'],
                'environment' => $validated['environment'],
                'status' => 'credentials_set',
            ]
        );

        return back()->with('success', 'QuickBooks credentials saved successfully. You can now connect your QuickBooks account.');
    }

    /**
     * Redirect to QuickBooks OAuth authorization
     */
    public function connect(Request $request)
    {
        $business = $request->user()->defaultBusiness();
        if (!$business) {
            return redirect()->route('business.setup')
                ->with('error', 'Please complete your business setup first.');
        }

        $connection = $business->quickBooksConnection;

        // Check if credentials are configured
        if (!$connection || !$connection->hasValidCredentials()) {
            return back()->with('error', 'Please configure your QuickBooks credentials first.');
        }

        // Store connection ID in session for callback
        session(['qb_connecting_connection_id' => $connection->id]);

        try {
            $authUrl = $this->qbService->getAuthorizationUrl($connection);
            return redirect($authUrl);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to initiate OAuth: ' . $e->getMessage());
        }
    }

    /**
     * Handle OAuth callback from QuickBooks
     */
    public function callback(Request $request)
    {
        $code = $request->get('code');
        $realmId = $request->get('realmId');
        $error = $request->get('error');

        if ($error) {
            return redirect()->route('business.integrations.quickbooks')
                ->with('error', "QuickBooks authorization failed: {$error}");
        }

        if (!$code || !$realmId) {
            return redirect()->route('business.integrations.quickbooks')
                ->with('error', 'Missing authorization code or company ID.');
        }

        $connectionId = session('qb_connecting_connection_id');
        if (!$connectionId) {
            return redirect()->route('business.integrations.quickbooks')
                ->with('error', 'Session expired. Please try again.');
        }

        $connection = QuickBooksConnection::find($connectionId);
        if (!$connection) {
            return redirect()->route('business.integrations.quickbooks')
                ->with('error', 'Connection not found.');
        }

        try {
            $connection = $this->qbService->exchangeCodeForTokens($code, $realmId, $connection);

            session()->forget('qb_connecting_connection_id');

            return redirect()->route('business.integrations.quickbooks')
                ->with('success', "Successfully connected to QuickBooks: {$connection->company_name}");
        } catch (\Exception $e) {
            Log::error('QuickBooks OAuth callback failed', [
                'error' => $e->getMessage(),
                'connection_id' => $connectionId,
            ]);

            return redirect()->route('business.integrations.quickbooks')
                ->with('error', 'Failed to connect to QuickBooks: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect from QuickBooks
     */
    public function disconnect(Request $request)
    {
        $business = $request->user()->defaultBusiness();
        $connection = $business?->quickBooksConnection;

        if (!$connection) {
            return back()->with('error', 'No QuickBooks connection found.');
        }

        try {
            $this->qbService->disconnect($connection);

            return back()->with('success', 'Successfully disconnected from QuickBooks.');
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
        $connection = $business?->quickBooksConnection;

        if (!$connection) {
            return back()->with('error', 'No QuickBooks connection found.');
        }

        if (!$connection->isActive()) {
            return back()->with('error', 'QuickBooks connection is not active. Please reconnect.');
        }

        $request->validate([
            'sync_type' => 'required|in:invoices,bills,all',
            'date_range' => 'nullable|in:last_30_days,last_month,last_3_months,last_6_months,this_year,all_time',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $syncType = $request->input('sync_type');

        // Handle date range
        if ($request->filled('date_range')) {
            $dateRange = match($request->input('date_range')) {
                'last_30_days' => [Carbon::now()->subDays(30), Carbon::now()],
                'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
                'last_3_months' => [Carbon::now()->subMonths(3), Carbon::now()],
                'last_6_months' => [Carbon::now()->subMonths(6), Carbon::now()],
                'this_year' => [Carbon::now()->startOfYear(), Carbon::now()],
                'all_time' => [Carbon::now()->subYears(5), Carbon::now()], // QB limits to ~5 years
                default => [Carbon::now()->subDays(30), Carbon::now()],
            };
            [$fromDate, $toDate] = $dateRange;
        } else {
            $fromDate = $request->input('from_date') ? Carbon::parse($request->input('from_date')) : Carbon::now()->subDays(30);
            $toDate = $request->input('to_date') ? Carbon::parse($request->input('to_date')) : Carbon::now();
        }

        try {
            if ($syncType === 'invoices' || $syncType === 'all') {
                $this->qbService->syncInvoicesFromQuickBooks($connection, $fromDate, $toDate);
            }

            if ($syncType === 'bills' || $syncType === 'all') {
                $this->qbService->syncBillsFromQuickBooks($connection, $fromDate, $toDate);
            }

            return back()->with('success', 'Sync completed successfully. Check sync logs for details.');
        } catch (\Exception $e) {
            Log::error('QuickBooks manual sync failed', [
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
        $connection = $business?->quickBooksConnection;

        if (!$connection) {
            return back()->with('error', 'No QuickBooks connection found.');
        }

        $request->validate([
            'auto_sync_enabled' => 'required|boolean',
            'sync_frequency' => 'required|in:hourly,daily,weekly',
            'sync_settings' => 'nullable|array',
            'sync_settings.sync_invoices' => 'boolean',
            'sync_settings.sync_bills' => 'boolean',
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
        $connection = $business?->quickBooksConnection;

        if (!$connection) {
            return response()->json(['error' => 'No connection found'], 404);
        }

        $log = $connection->syncLogs()->findOrFail($logId);

        return response()->json([
            'id' => $log->id,
            'sync_type' => $log->sync_type,
            'entity_type' => $log->entity_type,
            'status' => $log->status,
            'total_records' => $log->total_records,
            'processed_records' => $log->processed_records,
            'success_count' => $log->success_count,
            'failure_count' => $log->failure_count,
            'skipped_count' => $log->skipped_count,
            'started_at' => $log->started_at,
            'completed_at' => $log->completed_at,
            'duration_seconds' => $log->duration_seconds,
            'error_message' => $log->error_message,
            'errors' => $log->errors,
            'summary' => $log->summary,
        ]);
    }
}
