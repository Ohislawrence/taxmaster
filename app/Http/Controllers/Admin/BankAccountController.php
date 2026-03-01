<?php

namespace App\Http\Controllers\Admin;

use App\Models\BankAccount;
use App\Models\Business;
use Inertia\Inertia;
use Illuminate\Http\Request;

class BankAccountController
{
    /**
     * Display all bank accounts across all businesses
     */
    public function index(Request $request)
    {
        $bankAccounts = BankAccount::with('business.owner')
            ->when($request->search, function ($query, $search) {
                return $query->where('account_name', 'like', "%{$search}%")
                    ->orWhere('account_number', 'like', "%{$search}%")
                    ->orWhere('bank_name', 'like', "%{$search}%")
                    ->orWhereHas('business', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('is_active', $status === 'active');
            })
            ->when($request->business_id, function ($query, $businessId) {
                return $query->where('business_id', $businessId);
            })
            ->withCount('transactions')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($account) {
                return [
                    'id' => $account->id,
                    'bank_name' => $account->bank_name,
                    'account_name' => $account->account_name,
                    'account_number' => $account->account_number,
                    'masked_account_number' => $account->masked_account_number,
                    'balance' => $account->balance,
                    'currency' => $account->currency,
                    'is_active' => $account->is_active,
                    'auto_sync' => $account->auto_sync,
                    'last_synced_at' => $account->last_synced_at?->diffForHumans(),
                    'last_synced_at_full' => $account->last_synced_at?->format('M d, Y H:i'),
                    'created_at' => $account->created_at->format('M d, Y'),
                    'transactions_count' => $account->transactions_count,
                    'business' => [
                        'id' => $account->business->id,
                        'name' => $account->business->name,
                        'owner_name' => $account->business->owner->name ?? 'N/A',
                        'status' => $account->business->status,
                    ],
                ];
            });

        // Summary statistics
        $stats = [
            'total_accounts' => BankAccount::count(),
            'active_accounts' => BankAccount::where('is_active', true)->count(),
            'inactive_accounts' => BankAccount::where('is_active', false)->count(),
            'total_balance' => BankAccount::where('is_active', true)->sum('balance'),
            'auto_sync_enabled' => BankAccount::where('auto_sync', true)->count(),
        ];

        // Get businesses for filter dropdown
        $businesses = Business::select('id', 'name')
            ->whereHas('bankAccounts')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/BankAccounts/Index', [
            'bankAccounts' => $bankAccounts,
            'stats' => $stats,
            'businesses' => $businesses,
            'filters' => $request->only(['search', 'status', 'business_id']),
        ]);
    }

    /**
     * Show bank account details
     */
    public function show(BankAccount $bankAccount)
    {
        $bankAccount->load('business.owner', 'transactions');
        $bankAccount->loadCount('transactions');

        // Recent transactions
        $recentTransactions = $bankAccount->transactions()
            ->orderBy('transaction_date', 'desc')
            ->take(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'formatted_amount' => $transaction->formatted_amount,
                    'description' => $transaction->description,
                    'transaction_date' => $transaction->transaction_date->format('M d, Y'),
                    'category' => $transaction->category,
                ];
            });

        return Inertia::render('Admin/BankAccounts/Show', [
            'bankAccount' => [
                'id' => $bankAccount->id,
                'bank_name' => $bankAccount->bank_name,
                'account_name' => $bankAccount->account_name,
                'account_number' => $bankAccount->account_number,
                'balance' => $bankAccount->balance,
                'currency' => $bankAccount->currency,
                'is_active' => $bankAccount->is_active,
                'auto_sync' => $bankAccount->auto_sync,
                'last_synced_at' => $bankAccount->last_synced_at?->format('M d, Y H:i:s'),
                'created_at' => $bankAccount->created_at->format('M d, Y H:i:s'),
                'transactions_count' => $bankAccount->transactions_count,
                'business' => [
                    'id' => $bankAccount->business->id,
                    'name' => $bankAccount->business->name,
                    'email' => $bankAccount->business->email,
                    'owner' => [
                        'id' => $bankAccount->business->owner->id ?? null,
                        'name' => $bankAccount->business->owner->name ?? 'N/A',
                        'email' => $bankAccount->business->owner->email ?? 'N/A',
                    ],
                ],
            ],
            'recentTransactions' => $recentTransactions,
        ]);
    }

    /**
     * Deactivate a bank account
     */
    public function deactivate(BankAccount $bankAccount)
    {
        $bankAccount->update(['is_active' => false]);

        return back()->with('message', 'Bank account deactivated successfully');
    }

    /**
     * Activate a bank account
     */
    public function activate(BankAccount $bankAccount)
    {
        $bankAccount->update(['is_active' => true]);

        return back()->with('message', 'Bank account activated successfully');
    }
}
