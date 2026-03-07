<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Transaction;
use App\Services\MonoIntegrationService;
use Illuminate\Http\Request;

class TestMonoController extends Controller
{
    /**
     * Test the Mono integration
     */
    public function testMono(Request $request)
    {
        // Check authentication
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $results = [
            'timestamp' => now()->toIso8601String(),
            'user' => auth()->user()->name,
        ];

        // 1. Check Mono Configuration
        $results['mono_config'] = [
            'secret_key_configured' => (bool) config('services.mono.secret_key'),
            'public_key_configured' => (bool) config('services.mono.public_key'),
            'redirect_url' => config('services.mono.redirect_url'),
        ];

        // 2. Check Bank Accounts
        $bankAccounts = BankAccount::with('transactions')->get();
        $results['bank_accounts'] = [
            'total' => $bankAccounts->count(),
            'details' => $bankAccounts->map(function ($account) {
                return [
                    'id' => $account->id,
                    'bank_name' => $account->bank_name,
                    'account_number' => $account->masked_account_number,
                    'is_active' => $account->is_active,
                    'auto_sync' => $account->auto_sync,
                    'transactions_count' => $account->transactions()->count(),
                    'last_synced_at' => $account->last_synced_at?->toIso8601String(),
                    'mono_account_id' => $account->mono_account_id,
                ];
            })->toArray(),
        ];

        // 3. Check Transactions
        $results['transactions'] = [
            'total' => Transaction::count(),
            'by_account' => BankAccount::with('transactions')
                ->get()
                ->mapWithKeys(function ($account) {
                    return [
                        $account->bank_name => $account->transactions()->count(),
                    ];
                })
                ->toArray(),
        ];

        // 4. Try to sync a bank account if one exists
        if ($bankAccounts->isNotEmpty()) {
            $accountToSync = $bankAccounts->first();
            try {
                $monoService = app(MonoIntegrationService::class);
                $syncCount = $monoService->syncTransactions($accountToSync);
                $results['test_sync'] = [
                    'account_name' => $accountToSync->bank_name,
                    'synced_transactions' => $syncCount,
                    'status' => 'success',
                ];
            } catch (\Exception $e) {
                $results['test_sync'] = [
                    'account_name' => $accountToSync->bank_name,
                    'error' => $e->getMessage(),
                    'status' => 'failed',
                ];
            }
        }

        return response()->json($results, 200);
    }
}
