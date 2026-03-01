<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transaction;
use App\Models\Business;
use App\Models\BankAccount;
use Inertia\Inertia;
use Illuminate\Http\Request;

class TransactionController
{
    /**
     * Display all transactions across all businesses
     */
    public function index(Request $request)
    {
        $transactions = Transaction::with('business', 'bankAccount')
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('counterparty', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhere('mono_transaction_id', 'like', "%{$search}%");
                });
            })
            ->when($request->business_id, function ($query, $businessId) {
                return $query->where('business_id', $businessId);
            })
            ->when($request->category, function ($query, $category) {
                if ($category === 'uncategorized') {
                    return $query->whereNull('category');
                }
                return $query->where('category', $category);
            })
            ->when($request->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->whereDate('transaction_date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->whereDate('transaction_date', '<=', $dateTo);
            })
            ->orderBy('transaction_date', 'desc')
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
                    'transaction_date' => $transaction->transaction_date->format('M d, Y'),
                    'category' => $transaction->category,
                    'category_label' => $transaction->category_label,
                    'confidence' => $transaction->confidence,
                    'user_verified' => $transaction->user_verified,
                    'business' => [
                        'id' => $transaction->business->id,
                        'name' => $transaction->business->name,
                    ],
                    'bank_account' => [
                        'id' => $transaction->bankAccount->id,
                        'bank_name' => $transaction->bankAccount->bank_name,
                        'account_number' => $transaction->bankAccount->masked_account_number,
                    ],
                ];
            });

        // Summary statistics
        $stats = [
            'total_transactions' => Transaction::count(),
            'uncategorized' => Transaction::whereNull('category')->count(),
            'total_revenue' => Transaction::where('type', 'credit')->sum('amount'),
            'total_expenses' => Transaction::where('type', 'debit')->sum('amount'),
            'vat_applicable' => Transaction::where('vat_applicable', true)->count(),
        ];

        // Get businesses for filter dropdown
        $businesses = Business::select('id', 'name')
            ->whereHas('transactions')
            ->orderBy('name')
            ->get();

        $categories = [
            'Sales/Revenue',
            'Operating Expenses',
            'Staff Salaries',
            'Utilities',
            'Transport/Logistics',
            'Marketing',
            'Professional Services',
            'Equipment Purchase',
            'Other Expense',
            'Investment',
            'Loan Repayment',
            'Personal Withdrawal',
        ];

        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $transactions,
            'stats' => $stats,
            'businesses' => $businesses,
            'categories' => $categories,
            'filters' => $request->only(['search', 'business_id', 'category', 'type', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show transaction details
     */
    public function show(Transaction $transaction)
    {
        $transaction->load('business.owner', 'bankAccount');

        return Inertia::render('Admin/Transactions/Show', [
            'transaction' => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'formatted_amount' => $transaction->formatted_amount,
                'currency' => $transaction->currency,
                'description' => $transaction->description,
                'counterparty' => $transaction->counterparty,
                'reference' => $transaction->mono_transaction_id,
                'transaction_date' => $transaction->transaction_date->format('M d, Y H:i:s'),
                'category' => $transaction->category,
                'sub_category' => $transaction->sub_category,
                'confidence' => $transaction->confidence,
                'confidence_label' => $transaction->confidence_label,
                'vat_applicable' => $transaction->vat_applicable,
                'is_business_expense' => $transaction->is_business_expense,
                'user_verified' => $transaction->user_verified,
                'created_at' => $transaction->created_at->format('M d, Y H:i:s'),
                'business' => [
                    'id' => $transaction->business->id,
                    'name' => $transaction->business->name,
                    'email' => $transaction->business->email,
                    'owner' => [
                        'name' => $transaction->business->owner->name ?? 'N/A',
                        'email' => $transaction->business->owner->email ?? 'N/A',
                    ],
                ],
                'bank_account' => [
                    'id' => $transaction->bankAccount->id,
                    'bank_name' => $transaction->bankAccount->bank_name,
                    'account_name' => $transaction->bankAccount->account_name,
                    'account_number' => $transaction->bankAccount->account_number,
                ],
            ],
        ]);
    }

    /**
     * Export transactions (admin overview)
     */
    public function export(Request $request)
    {
        $transactions = Transaction::with('business', 'bankAccount')
            ->when($request->business_id, fn($q, $id) => $q->where('business_id', $id))
            ->when($request->category, fn($q, $cat) => $q->where('category', $cat))
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->when($request->date_from, fn($q, $date) => $q->whereDate('transaction_date', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->whereDate('transaction_date', '<=', $date))
            ->orderBy('transaction_date', 'desc')
            ->get();

        $csv = "Business Name,Transaction Date,Type,Amount,Currency,Description,Counterparty,Category,Bank Account,Reference\n";

        foreach ($transactions as $transaction) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $transaction->business->name,
                $transaction->transaction_date->format('Y-m-d'),
                $transaction->type,
                $transaction->amount,
                $transaction->currency,
                str_replace(',', ' ', $transaction->description),
                str_replace(',', ' ', $transaction->counterparty ?? ''),
                $transaction->category ?? 'Uncategorized',
                $transaction->bankAccount->bank_name . ' - ' . $transaction->bankAccount->masked_account_number,
                $transaction->mono_transaction_id
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions-export-' . date('Y-m-d') . '.csv"',
        ]);
    }
}
