<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\BankAccount;
use App\Services\TransactionCategorizationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionCategorizationService $categorizationService
    ) {}

    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->ownedBusiness) {
            return redirect()->route('business.setup')
                ->with('error', 'Please complete your business setup first.');
        }

        $business = $user->defaultBusiness();

        $query = Transaction::where('business_id', $business->id)
            ->with('bankAccount');

        // Filters
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->has('period')) {
            $query->period($request->period);
        }

        if ($request->has('uncategorized') && $request->uncategorized) {
            $query->uncategorized();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('counterparty', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        // Pagination
        $transactions = $query->orderBy('transaction_date', 'desc')
            ->paginate(50)
            ->through(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'formatted_amount' => $transaction->formatted_amount,
                    'currency' => $transaction->currency,
                    'description' => $transaction->description,
                    'counterparty' => $transaction->counterparty,
                    'reference' => $transaction->mono_transaction_id,
                    'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
                    'transaction_date_human' => $transaction->transaction_date->format('M d, Y'),
                    'category' => $transaction->category,
                    'sub_category' => $transaction->sub_category,
                    'category_label' => $transaction->category_label,
                    'ai_confidence' => (float) $transaction->confidence,
                    'confidence' => $transaction->confidence,
                    'confidence_label' => $transaction->confidence_label,
                    'vat_applicable' => $transaction->vat_applicable,
                    'is_business_expense' => $transaction->is_business_expense,
                    'user_verified' => $transaction->user_verified,
                    'bank_account' => [
                        'id' => $transaction->bankAccount->id,
                        'bank_name' => $transaction->bankAccount->bank_name,
                        'account_number' => $transaction->bankAccount->masked_account_number,
                    ],
                ];
            });

        // Summary stats
        $stats = [
            'total_transactions' => Transaction::where('business_id', $business->id)->count(),
            'uncategorized' => Transaction::where('business_id', $business->id)->uncategorized()->count(),
            'revenue' => Transaction::where('business_id', $business->id)->revenue()->sum('amount'),
            'expenses' => Transaction::where('business_id', $business->id)->expenses()->sum('amount'),
            'vat_applicable' => Transaction::where('business_id', $business->id)->vatApplicable()->count(),
        ];

        return Inertia::render('Business/Transactions/Index', [
            'transactions' => $transactions,
            'categories' => Transaction::getCategoriesGrouped(),
            'whtCategories' => Transaction::getWHTApplicableCategories(),
            'stats' => $stats,
            'filters' => $request->only(['category', 'type', 'period', 'uncategorized', 'search']),
        ]);
    }

    /**
     * Show categorization interface for a transaction
     */
    public function categorize(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        return Inertia::render('Business/Transactions/Categorize', [
            'transaction' => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'formatted_amount' => $transaction->formatted_amount,
                'description' => $transaction->description,
                'counterparty' => $transaction->counterparty,
                'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
                'current_category' => $transaction->category,
                'current_sub_category' => $transaction->sub_category,
                'confidence' => $transaction->confidence,
            ],
            'categories' => Transaction::getCategoriesGrouped(),
            'whtCategories' => Transaction::getWHTApplicableCategories(),
        ]);
    }

    /**
     * Update transaction category
     */
    public function updateCategory(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $request->validate([
            'category' => 'required|string',
            'sub_category' => 'nullable|string',
        ]);

        $this->categorizationService->recategorize(
            $transaction,
            $request->category,
            $request->sub_category
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Transaction category updated successfully.',
            ]);
        }

        return back()->with('success', 'Transaction category updated successfully.');
    }

    /**
     * Batch categorize transactions
     */
    public function batchCategorize(Request $request)
    {
        $request->validate([
            'transaction_ids' => 'required|array',
            'transaction_ids.*' => 'exists:transactions,id',
        ]);

        $business = $request->user()->defaultBusiness();

        // Verify all transactions belong to business
        $transactions = Transaction::whereIn('id', $request->transaction_ids)
            ->where('business_id', $business->id)
            ->get();

        if ($transactions->count() !== count($request->transaction_ids)) {
            return back()->with('error', 'Some transactions do not belong to your business.');
        }

        $count = $this->categorizationService->batchCategorize($request->transaction_ids);

        return back()->with('success', "{$count} transactions categorized successfully.");
    }

    /**
     * Export transactions
     */
    public function export(Request $request)
    {
        $business = $request->user()->defaultBusiness();

        $transactions = Transaction::where('business_id', $business->id)
            ->with('bankAccount')
            ->orderBy('transaction_date', 'desc')
            ->get();

        // Create CSV
        $filename = "transactions-{$business->slug}-" . now()->format('Y-m-d') . '.csv';
        $handle = fopen('php://temp', 'r+');

        // Headers
        fputcsv($handle, [
            'Date',
            'Type',
            'Amount',
            'Currency',
            'Description',
            'Counterparty',
            'Category',
            'Sub Category',
            'VAT Applicable',
            'Bank Account',
            'Confidence',
        ]);

        // Data
        foreach ($transactions as $txn) {
            fputcsv($handle, [
                $txn->transaction_date->format('Y-m-d'),
                $txn->type,
                $txn->amount,
                $txn->currency,
                $txn->description,
                $txn->counterparty,
                $txn->category,
                $txn->sub_category,
                $txn->vat_applicable ? 'Yes' : 'No',
                $txn->bankAccount->bank_name . ' - ' . $txn->bankAccount->account_number,
                $txn->confidence,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Delete a transaction
     */
    public function destroy(Request $request, Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        $business = $request->user()->defaultBusiness();
        if ($transaction->business_id !== $business->id) {
            abort(403);
        }

        try {
            $transaction->delete();
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Failed to delete transaction', 'error' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to delete transaction');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Transaction deleted successfully']);
        }

        return back()->with('success', 'Transaction deleted successfully');
    }
}
