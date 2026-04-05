<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IntegrationsController extends Controller
{
    /**
     * Show integrations hub page
     */
    public function index(Request $request)
    {
        $business = $request->user()->defaultBusiness();
        if (!$business) {
            return redirect()->route('business.setup')
                ->with('error', 'Please complete your business setup first.');
        }

        // Check QuickBooks connection status
        $qbConnection = $business->quickBooksConnection;

        $integrations = [
            [
                'name' => 'QuickBooks',
                'slug' => 'quickbooks',
                'description' => 'Sync invoices, bills, customers, and vendors with QuickBooks Online',
                'icon' => 'quickbooks',
                'status' => $qbConnection && $qbConnection->isActive() ? 'connected' : 'available',
                'connected_at' => $qbConnection?->created_at,
                'last_synced_at' => $qbConnection?->last_synced_at,
                'route' => '/business/integrations/quickbooks',
                'available' => true,
            ],
            [
                'name' => 'Sage',
                'slug' => 'sage',
                'description' => 'Connect with Sage Business Cloud Accounting',
                'icon' => 'sage',
                'status' => 'coming_soon',
                'connected_at' => null,
                'last_synced_at' => null,
                'route' => null,
                'available' => false,
            ],
            [
                'name' => 'Xero',
                'slug' => 'xero',
                'description' => 'Integrate with Xero accounting software',
                'icon' => 'xero',
                'status' => 'coming_soon',
                'connected_at' => null,
                'last_synced_at' => null,
                'route' => null,
                'available' => false,
            ],
        ];

        return Inertia::render('Business/Integrations/Index', [
            'integrations' => $integrations,
            'businessName' => $business->business_name,
        ]);
    }
}
