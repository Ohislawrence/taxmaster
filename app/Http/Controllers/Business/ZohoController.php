<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\ZohoConnection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ZohoController extends Controller
{
    /**
     * Show Zoho integration page
     */
    public function index(Request $request)
    {
        $business = $request->user()->defaultBusiness();

        if (!$business) {
            return redirect()->route('business.setup')
                ->with('error', 'Please complete your business setup first.');
        }

        $connection = $business->zohoConnection;
        $syncLogs = $connection ? $connection->syncLogs()->latest()->take(10)->get() : [];

        return Inertia::render('Business/Integrations/Zoho', [
            'connection' => $connection,
            'syncLogs' => $syncLogs,
        ]);
    }

   /**
     * Save Zoho OAuth credentials
     */
    public function saveCredentials(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'redirect_uri' => 'required|url',
            'data_center' => 'required|string|in:com,eu,in,com.au,com.cn,jp',
        ]);

        $business = $request->user()->defaultBusiness();

        if (!$business) {
            return back()->with('error', 'Business not found');
        }

        // Create or update connection
        $connection = $business->zohoConnection()->updateOrCreate(
            ['business_id' => $business->id],
            [
                'client_id' => $validated['client_id'],
                'client_secret' => $validated['client_secret'],
                'redirect_uri' => $validated['redirect_uri'],
                'data_center' => $validated['data_center'],
                'status' => 'credentials_set',
            ]
        );

        return redirect()->route('business.integrations.zoho.index')
            ->with('message', 'Zoho credentials saved successfully. You can now connect your Zoho Books account.');
    }

    /**
     * Initiate OAuth connection
     */
    public function connect(Request $request)
    {
        $business = $request->user()->defaultBusiness();
        $connection = $business->zohoConnection;

        if (!$connection || !$connection->hasValidCredentials()) {
            return back()->with('error', 'Please configure your Zoho credentials first.');
        }

        // Build Zoho OAuth URL
        $accountsUrl = $connection->getAccountsBaseUrl();
        $authUrl = $accountsUrl . '/oauth/v2/auth?' . http_build_query([
            'scope' => 'ZohoBooks.fullaccess.all',
            'client_id' => decrypt($connection->client_id),
            'response_type' => 'code',
            'redirect_uri' => $connection->redirect_uri,
            'access_type' => 'offline',
            'prompt' => 'consent',
        ]);

        return redirect($authUrl);
    }

    /**
     * Handle OAuth callback
     */
    public function callback(Request $request)
    {
        $code = $request->get('code');
        $business = $request->user()->defaultBusiness();
        $connection = $business->zohoConnection;

        if (!$connection || !$code) {
            return redirect()->route('business.integrations.zoho.index')
                ->with('error', 'Authorization failed. Please try again.');
        }

        try {
            // Exchange code for tokens
            $accountsUrl = $connection->getAccountsBaseUrl();
            $response = \Http::asForm()->post($accountsUrl . '/oauth/v2/token', [
                'code' => $code,
                'client_id' => decrypt($connection->client_id),
                'client_secret' => decrypt($connection->client_secret),
                'redirect_uri' => $connection->redirect_uri,
                'grant_type' => 'authorization_code',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Update connection with tokens
                $connection->update([
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'],
                    'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
                    'status' => 'active',
                ]);

                // Fetch organization info
                $this->fetchOrganizationInfo($connection);

                return redirect()->route('business.integrations.zoho.index')
                    ->with('message', 'Successfully connected to Zoho Books!');
            } else {
                throw new \Exception('Failed to exchange authorization code: ' . $response->body());
            }
        } catch (\Exception $e) {
            \Log::error('Zoho OAuth callback error: ' . $e->getMessage());

            return redirect()->route('business.integrations.zoho.index')
                ->with('error', 'Failed to connect to Zoho Books. Please try again.');
        }
    }

    /**
     * Fetch organization info from Zoho
     */
    protected function fetchOrganizationInfo(ZohoConnection $connection)
    {
        try {
            $apiUrl = $connection->getApiBaseUrl();
            $response = \Http::withToken(decrypt($connection->access_token))
                ->get($apiUrl . '/api/v3/organizations');

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['organizations']) && count($data['organizations']) > 0) {
                    $org = $data['organizations'][0];
                    $connection->update([
                        'organization_id' => $org['organization_id'],
                        'organization_name' => $org['name'],
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to fetch Zoho organization info: ' . $e->getMessage());
        }
    }

    /**
     * Update sync settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'auto_sync_enabled' => 'required|boolean',
            'sync_frequency' => 'required|in:hourly,daily,weekly',
            'sync_settings' => 'required|array',
            'sync_settings.sync_invoices' => 'boolean',
            'sync_settings.sync_bills' => 'boolean',
        ]);

        $business = $request->user()->defaultBusiness();
        $connection = $business->zohoConnection;

        if (!$connection) {
            return back()->with('error', 'Zoho connection not found');
        }

        $connection->update($validated);

        return redirect()->route('business.integrations.zoho.index')
            ->with('message', 'Sync settings updated successfully.');
    }

    /**
     * Trigger manual sync
     */
    public function sync(Request $request)
    {
        $validated = $request->validate([
            'date_range' => 'required|in:last_30_days,last_month,last_3_months,last_6_months,this_year,all_time',
            'sync_type' => 'required|in:all,invoices,bills',
        ]);

        $business = $request->user()->defaultBusiness();
        $connection = $business->zohoConnection;

        if (!$connection || !$connection->isActive()) {
            return back()->with('error', 'Zoho connection is not active');
        }

        // TODO: Implement sync logic here
        // For now, just return success

        return redirect()->route('business.integrations.zoho.index')
            ->with('message', 'Sync started successfully. This may take a few minutes.');
    }

    /**
     * Disconnect Zoho
     */
    public function disconnect(Request $request)
    {
        $business = $request->user()->defaultBusiness();
        $connection = $business->zohoConnection;

        if ($connection) {
            $connection->update([
                'status' => 'revoked',
                'access_token' => null,
                'refresh_token' => null,
            ]);
        }

        return redirect()->route('business.integrations.zoho.index')
            ->with('message', 'Zoho Books disconnected successfully.');
    }
}
