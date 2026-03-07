<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ERP\QuickBooksConnector;
use App\Services\ERP\XeroConnector;
use App\Services\ERP\SageConnector;

class ERPController extends Controller
{
    public function syncQuickBooks(Request $request)
    {
        $connector = new QuickBooksConnector();
        $connector->syncCustomers();
        $connector->syncInvoices();
        $connector->syncPayments();
        return response()->json(['status' => 'QuickBooks sync triggered']);
    }

    public function syncXero(Request $request)
    {
        $connector = new XeroConnector();
        $connector->syncCustomers();
        $connector->syncInvoices();
        $connector->syncPayments();
        return response()->json(['status' => 'Xero sync triggered']);
    }

    public function syncSage(Request $request)
    {
        $connector = new SageConnector();
        $connector->syncCustomers();
        $connector->syncInvoices();
        $connector->syncPayments();
        return response()->json(['status' => 'Sage sync triggered']);
    }
}
