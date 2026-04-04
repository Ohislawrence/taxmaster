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

        $business = $user->defaultBusiness();

        // Check subscription feature
        if (!$this->subscriptionService->canPerformAction($business, 'link_bank_account')) {
            // Differentiate: plan doesn't support it at all vs. at limit
            $usageStats = $this->subscriptionService->getUsageStats($business);
            $limit = $usageStats['bank_accounts_limit'] ?? 0;

            if ($limit === 0) {
                return redirect()->route('business.dashboard')
                    ->with('error', 'Your current plan does not include bank account linking. Please upgrade to Basic or higher.');
            }

            // At limit — still show the page but frontend will disable the connect button
        }

        $usageStats = $this->subscriptionService->getUsageStats($business);
        $bankAccountsCount = $usageStats['bank_accounts_count'] ?? 0;
        $bankAccountsLimit = $usageStats['bank_accounts_limit'] ?? 0;
        $canLinkMore = $bankAccountsCount < $bankAccountsLimit;

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
            'monoEnabled' => config('services.mono.enabled', false),
            'monoPublicKey' => config('services.mono.public_key'),
            'customerName' => $business->name ?? $user->name,
            'customerEmail' => $business->email ?? $user->email,
            'monoCustomerId' => $business->mono_customer_id,
            'bankAccountsCount' => $bankAccountsCount,
            'bankAccountsLimit' => $bankAccountsLimit,
            'canLinkMore' => $canLinkMore,
        ]);
    }

    /**
     * Handle Mono callback after authorization
     */
    public function callback(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'mono_customer_id' => 'nullable|string',
        ]);

        $user = $request->user();

        if (!$user || !$user->ownedBusiness) {
            return response()->json([
                'error' => 'User business not configured',
            ], 422);
        }

        $business = $user->defaultBusiness();

        // Check subscription feature
        if (!$this->subscriptionService->canPerformAction($business, 'link_bank_account')) {
            $usageStats = $this->subscriptionService->getUsageStats($business);
            $limit = $usageStats['bank_accounts_limit'] ?? 0;

            if ($limit === 0) {
                return response()->json([
                    'error' => 'Your current plan does not include bank account linking. Please upgrade to Basic or higher.',
                ], 403);
            }

            return response()->json([
                'error' => "You've reached your plan's limit of {$limit} bank account(s). Please upgrade to connect more.",
            ], 403);
        }

        try {
            // Exchange code for account ID
            $authData = $this->monoService->exchangeToken($request->code);

            \Log::info('Mono exchangeToken response', ['authData' => $authData]);

            // Mono v2 may return { "id": "..." } or { "data": { "id": "..." } }
            $accountId = $authData['id']
                ?? $authData['data']['id']
                ?? $authData['data']['account_id']
                ?? $authData['account_id']
                ?? null;

            if (!$accountId) {
                \Log::error('Mono auth response missing account ID', ['authData' => $authData]);
                throw new \Exception('No account ID returned from Mono. Response: ' . json_encode($authData));
            }

            // Get account details
            $details = $this->monoService->getAccountDetails($accountId);

            \Log::info('Mono account details response', ['details' => $details]);

            // Mono v2 nests as: { data: { account: { ... }, customer: { ... }, meta: { ... } } }
            $accountData = $details['data']['account']
                ?? $details['account']
                ?? $details['data']
                ?? $details;

            \Log::info('Parsed account data', ['accountData' => $accountData]);

            // Create bank account record
            $bankAccount = BankAccount::create([
                'business_id' => $business->id,
                'bank_name' => $accountData['institution']['name'] ?? $accountData['bankName'] ?? $accountData['bank_name'] ?? 'Unknown Bank',
                'account_name' => $accountData['name'] ?? $accountData['accountName'] ?? $accountData['account_name'] ?? 'N/A',
                'account_number' => $accountData['accountNumber'] ?? $accountData['account_number'] ?? $accountData['number'] ?? 'N/A',
                'currency' => $accountData['currency'] ?? 'NGN',
                'mono_account_id' => $accountId,
                'balance' => ($accountData['balance'] ?? 0) / 100, // Mono returns balance in kobo
                'is_active' => true,
                'auto_sync' => true,
                'meta' => $accountData,
            ]);

            // Queue initial sync
            SyncBankAccount::dispatch($bankAccount);

            // Save Mono customer ID if returned
            if (!empty($request->input('mono_customer_id'))) {
                $business->update(['mono_customer_id' => $request->input('mono_customer_id')]);
            }

            return response()->json([
                'message' => 'Bank account connected successfully! Transactions are being synced.',
                'account' => $bankAccount,
            ]);
        } catch (\Exception $e) {
            \Log::error('Bank connection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'business_id' => $business->id,
            ]);

            return response()->json([
                'error' => 'Failed to connect bank account: ' . $e->getMessage(),
            ], 500);
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
