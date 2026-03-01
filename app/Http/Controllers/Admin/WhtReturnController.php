<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhtTransaction;
use App\Models\WhtReturn;
use App\Models\Business;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WhtReturnController extends Controller
{
    /**
     * Display all WHT transactions across all businesses
     */
    public function index(Request $request)
    {
        $query = WhtTransaction::with(['business', 'vendor'])
            ->orderBy('transaction_date', 'desc');

        // Filter by business
        if ($request->filled('business_id')) {
            $query->where('business_id', $request->business_id);
        }

        // Filter by transaction type
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        // Search by vendor name
        if ($request->filled('search')) {
            $query->where('vendor_name', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->paginate(15);

        // Get stats
        $totalTransactions = WhtTransaction::count();
        $totalWhtAmount = WhtTransaction::sum('wht_amount');
        $totalGrossAmount = WhtTransaction::sum('gross_amount');

        // Get list of businesses for filter
        $businesses = Business::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/WhtReturns/Transactions', [
            'transactions' => $transactions,
            'stats' => [
                'total' => $totalTransactions,
                'totalWht' => $totalWhtAmount,
                'totalGross' => $totalGrossAmount,
            ],
            'businesses' => $businesses,
            'filters' => $request->only('business_id', 'transaction_type', 'start_date', 'end_date', 'search'),
        ]);
    }

    /**
     * Display all WHT returns across all businesses
     */
    public function returns(Request $request)
    {
        $query = WhtReturn::with(['business', 'schedules'])
            ->orderBy('period', 'desc');

        // Filter by business
        if ($request->filled('business_id')) {
            $query->where('business_id', $request->business_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by period
        if ($request->filled('period')) {
            $query->where('period', 'like', $request->period . '%');
        }

        // Search by business name
        if ($request->filled('search')) {
            $query->whereHas('business', function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%');
            });
        }

        $returns = $query->paginate(15);

        // Get stats
        $totalReturns = WhtReturn::count();
        $filedReturns = WhtReturn::where('status', 'filed')->count();
        $pendingReturns = WhtReturn::where('status', 'pending')->count();

        // Calculate total WHT from all businesses
        $totalWhtAmount = WhtReturn::sum('total_wht_amount');

        // Get list of businesses for filter
        $businesses = Business::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/WhtReturns/Returns', [
            'returns' => $returns,
            'stats' => [
                'total' => $totalReturns,
                'filed' => $filedReturns,
                'pending' => $pendingReturns,
                'totalWht' => $totalWhtAmount,
            ],
            'businesses' => $businesses,
            'filters' => $request->only('business_id', 'status', 'period', 'search'),
        ]);
    }

    /**
     * Display a specific WHT transaction
     */
    public function showTransaction(WhtTransaction $whtTransaction)
    {
        $whtTransaction->load('business', 'vendor');

        return Inertia::render('Admin/WhtReturns/ShowTransaction', [
            'transaction' => $whtTransaction,
        ]);
    }

    /**
     * Display a specific WHT return
     */
    public function showReturn(WhtReturn $whtReturn)
    {
        $whtReturn->load(['business', 'schedules', 'governmentPayments']);

        $stats = [
            'totalTransactions' => $whtReturn->schedules->sum('transaction_count'),
            'totalGrossAmount' => $whtReturn->schedules->sum('gross_amount'),
            'totalWht' => $whtReturn->total_wht_amount,
        ];

        return Inertia::render('Admin/WhtReturns/ShowReturn', [
            'whtReturn' => $whtReturn,
            'stats' => $stats,
        ]);
    }

    /**
     * Export WHT transactions
     */
    public function exportTransactions(Request $request)
    {
        $query = WhtTransaction::with('business');

        if ($request->filled('business_id')) {
            $query->where('business_id', $request->business_id);
        }

        $transactions = $query->get();

        $csv = "Date,Business,Vendor,Type,Gross Amount,WHT Amount,WHT Rate\n";

        foreach ($transactions as $transaction) {
            $csv .= implode(',', [
                $transaction->transaction_date,
                $transaction->business->name,
                $transaction->vendor_name,
                $transaction->transaction_type,
                $transaction->gross_amount,
                $transaction->wht_amount,
                $transaction->wht_rate,
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="wht-transactions-' . date('Y-m-d') . '.csv"');
    }

    /**
     * Export WHT returns
     */
    public function exportReturns(Request $request)
    {
        $query = WhtReturn::with('business');

        if ($request->filled('business_id')) {
            $query->where('business_id', $request->business_id);
        }

        $returns = $query->get();

        $csv = "Period,Business,Status,Total Transactions,Total Gross,Total WHT,Filed Date\n";

        foreach ($returns as $return) {
            $csv .= implode(',', [
                $return->period,
                $return->business->name,
                $return->status,
                $return->schedules->count(),
                $return->schedules->sum('gross_amount'),
                $return->total_wht_amount,
                $return->filed_date ?? 'N/A',
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="wht-returns-' . date('Y-m-d') . '.csv"');
    }

    /**
     * Get revenue report
     */
    public function revenueReport()
    {
        // Revenue by month
        $monthlyRevenue = WhtReturn::selectRaw('YEAR(period) as year, MONTH(period) as month, SUM(total_wht_amount) as revenue')
            ->groupByRaw('YEAR(period), MONTH(period)')
            ->orderByRaw('YEAR(period) DESC, MONTH(period) DESC')
            ->limit(12)
            ->get();

        // Top businesses by WHT collection
        $topBusinesses = WhtReturn::with('business')
            ->selectRaw('business_id, SUM(total_wht_amount) as total_wht')
            ->groupBy('business_id')
            ->orderByRaw('total_wht DESC')
            ->limit(10)
            ->get();

        // Transaction type breakdown
        $typeBreakdown = WhtTransaction::selectRaw('transaction_type, COUNT(*) as count, SUM(wht_amount) as total_wht')
            ->groupBy('transaction_type')
            ->orderByRaw('total_wht DESC')
            ->get();

        $totalRevenue = WhtReturn::sum('total_wht_amount');

        return Inertia::render('Admin/Reports/WhtRevenue', [
            'monthlyRevenue' => $monthlyRevenue,
            'topBusinesses' => $topBusinesses,
            'typeBreakdown' => $typeBreakdown,
            'totalRevenue' => $totalRevenue,
        ]);
    }
}
