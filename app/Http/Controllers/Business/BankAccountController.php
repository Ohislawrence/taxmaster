<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Services\MonoIntegrationService;
use App\Services\SubscriptionService;
use App\Jobs\SyncBankAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BankAccountController extends Controller
{
    public function __construct(
        protected MonoIntegrationService $monoService,
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * Display a listing of bank accounts.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->ownedBusiness) {
            return redirect()->route('business.setup')
                ->with('error', 'Please complete your business setup first.');
        }

        $business = $user->ownedBusiness;

        // Check subscription feature
        if (!$this->subscriptionService->canPerformAction($business, 'link_bank_account')) {
            return redirect()->route('business.dashboard')
                ->with('error', 'Your current plan does not include bank account linking. Please upgrade to Basic or higher.');
        }

        $accounts = BankAccount::where('business_id', $business->id)
            ->with('transactions')
            ->withCount('transactions')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'bank_name' => $account->bank_name,
                    'account_name' => $account->account_name,
                    'account_number' => $account->masked_account_number,
                    'account_number_full' => $account->account_number,
                    'balance' => $account->balance,
                    'currency' => $account->currency,
                    'is_active' => $account->is_active,
                    'auto_sync' => $account->auto_sync,
                    'last_synced_at' => $account->last_synced_at?->diffForHumans(),
                    'transactions_count' => $account->transactions_count,
                    'needs_sync' => $account->needsSync(),
                ];
            });

        return Inertia::render('Business/BankAccounts/Index', [
            'accounts' => $accounts,
            'monoPublicKey' => config('services.mono.public_key'),
        ]);
    }

    /**
     * Handle Mono callback after authorization
     */
    public function callback(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();

        if (!$user || !$user->ownedBusiness) {
            return response()->json([
                'error' => 'User business not configured',
            ], 422);
        }

        $business = $user->ownedBusiness;

        // Check subscription feature
        if (!$this->subscriptionService->canPerformAction($business, 'link_bank_account')) {
            return response()->json([
                'error' => 'Your current plan does not include bank account linking. Please upgrade to Basic or higher.',
            ], 403);
        }

        try {
            // Exchange code for account ID
            $authData = $this->monoService->exchangeToken($request->code);
            $accountId = $authData['id'];

            // Get account details
            $details = $this->monoService->getAccountDetails($accountId);
            $accountData = $details['account'];

            // Create bank account record
            $bankAccount = BankAccount::create([
                'business_id' => $business->id,
                'bank_name' => $accountData['institution']['name'] ?? 'Unknown Bank',
                'account_name' => $accountData['name'] ?? $accountData['accountName'] ?? 'N/A',
                'account_number' => $accountData['accountNumber'] ?? $accountData['number'] ?? 'N/A',
                'currency' => $accountData['currency'] ?? 'NGN',
                'mono_account_id' => $accountId,
                'balance' => $accountData['balance'] ?? 0,
                'is_active' => true,
                'auto_sync' => true,
                'meta' => $accountData,
            ]);

            // Queue initial sync
            SyncBankAccount::dispatch($bankAccount);

            return redirect()->route('business.banks.index')
                ->with('success', 'Bank account connected successfully! Transactions are being synced.');
        } catch (\Exception $e) {
            \Log::error('Bank connection failed', [
                'error' => $e->getMessage(),
                'business_id' => $business->id,
            ]);

            return redirect()->route('business.banks.index')
                ->with('error', 'Failed to connect bank account. Please try again.');
        }
    }

    /**
     * Manually trigger sync for a bank account
     */
    public function sync(Request $request, BankAccount $bankAccount)
    {
        $this->authorize('update', $bankAccount);

        try {
            SyncBankAccount::dispatch($bankAccount);

            return back()->with('success', 'Bank account sync started. This may take a few moments.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to start sync: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect (unlink) a bank account
     */
    public function destroy(Request $request, BankAccount $bankAccount)
    {
        $this->authorize('delete', $bankAccount);

        try {
            // Unlink from Mono
            $this->monoService->unlinkAccount($bankAccount->mono_account_id);

            // Mark as inactive instead of deleting (preserve transaction history)
            $bankAccount->update(['is_active' => false]);

            return redirect()->route('business.banks.index')
                ->with('success', 'Bank account disconnected successfully.');
        } catch (\Exception $e) {
            \Log::error('Bank disconnection failed', [
                'error' => $e->getMessage(),
                'account_id' => $bankAccount->id,
            ]);

            return back()->with('error', 'Failed to disconnect bank account.');
        }
    }

    /**
     * Toggle auto-sync for a bank account
     */
    public function toggleAutoSync(Request $request, BankAccount $bankAccount)
    {
        $this->authorize('update', $bankAccount);

        $bankAccount->update([
            'auto_sync' => !$bankAccount->auto_sync,
        ]);

        return back()->with('success', 'Auto-sync ' . ($bankAccount->auto_sync ? 'enabled' : 'disabled') . '.');
    }
}
