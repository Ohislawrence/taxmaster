<?php

namespace App\Http\Controllers\Admin;

use App\Models\BankAccount;
use App\Models\Business;
use App\Jobs\SyncBankAccount;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class SyncFailureController
{
    /**
     * Display all sync failures from notifications
     */
    public function index(Request $request)
    {
        // Get sync failure notifications for all users
        $failures = DatabaseNotification::where('type', 'App\\Notifications\\BankSyncFailedNotification')
            ->when($request->search, function ($query, $search) {
                return $query->whereHas('notifiable', function ($q) use ($search) {
                    $q->whereHas('business', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->when($request->resolved, function ($query, $resolved) {
                if ($resolved === 'unresolved') {
                    return $query->whereNull('read_at');
                } elseif ($resolved === 'resolved') {
                    return $query->whereNotNull('read_at');
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_failures' => DatabaseNotification::where('type', 'App\\Notifications\\BankSyncFailedNotification')->count(),
            'unresolved' => DatabaseNotification::where('type', 'App\\Notifications\\BankSyncFailedNotification')
                ->whereNull('read_at')->count(),
            'resolved' => DatabaseNotification::where('type', 'App\\Notifications\\BankSyncFailedNotification')
                ->whereNotNull('read_at')->count(),
        ];

        // Format notification data
        $formattedFailures = $failures->map(function ($notification) {
            return [
                'id' => $notification->id,
                'bank_account_id' => $notification->data['bank_account_id'] ?? null,
                'bank_name' => $notification->data['bank_name'] ?? 'Unknown',
                'account_number' => $notification->data['account_number'] ?? '****',
                'error_message' => $notification->data['error_message'] ?? 'No error message',
                'attempt_count' => $notification->data['attempt_count'] ?? 3,
                'business_name' => $notification->notifiable->business->name ?? 'Unknown',
                'owner_name' => $notification->notifiable->name ?? 'Unknown',
                'created_at' => $notification->created_at->toIso8601String(),
                'created_at_formatted' => $notification->created_at->format('Y-m-d H:i:s'),
                'is_read' => !is_null($notification->read_at),
            ];
        })->values();

        return Inertia::render('Admin/SyncFailures/Index', [
            'failures' => $formattedFailures,
            'failureStats' => $stats,
            'filters' => $request->only(['search', 'resolved']),
        ]);
    }

    /**
     * Show sync failure details
     */
    public function show($notificationId)
    {
        $notification = DatabaseNotification::findOrFail($notificationId);

        if ($notification->type !== 'App\\Notifications\\BankSyncFailedNotification') {
            abort(404);
        }

        $bankAccount = BankAccount::find($notification->data['bank_account_id'] ?? null);

        return Inertia::render('Admin/SyncFailures/Show', [
            'notification' => [
                'id' => $notification->id,
                'data' => $notification->data,
                'created_at' => $notification->created_at->toIso8601String(),
                'is_read' => !is_null($notification->read_at),
            ],
            'bankAccount' => $bankAccount ? [
                'id' => $bankAccount->id,
                'bank_name' => $bankAccount->bank_name,
                'account_number' => $bankAccount->account_number,
                'is_active' => $bankAccount->is_active,
                'last_sync_at' => $bankAccount->last_sync_at?->toIso8601String(),
            ] : null,
        ]);
    }

    /**
     * Retry sync for a specific bank account
     */
    public function retry($notificationId)
    {
        try {
            $notification = DatabaseNotification::findOrFail($notificationId);

            if ($notification->type !== 'App\\Notifications\\BankSyncFailedNotification') {
                abort(404);
            }

            $bankAccountId = $notification->data['bank_account_id'] ?? null;

            if (!$bankAccountId) {
                return back()->with('error', 'Bank account information not found.');
            }

            $bankAccount = BankAccount::findOrFail($bankAccountId);

            // Queue the sync job
            SyncBankAccount::dispatch($bankAccount);

            // Mark notification as read/resolved
            $notification->markAsRead();

            Log::info('Bank sync retry queued by admin', [
                'bank_account_id' => $bankAccount->id,
                'notification_id' => $notificationId,
                'admin_id' => auth()->id(),
            ]);

            return back()->with('success', 'Sync retry has been queued for ' . $bankAccount->bank_name);
        } catch (\Exception $e) {
            Log::error('Error retrying sync', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to queue retry: ' . $e->getMessage());
        }
    }

    /**
     * Mark failure as resolved
     */
    public function resolve($notificationId)
    {
        try {
            $notification = DatabaseNotification::findOrFail($notificationId);

            if ($notification->type !== 'App\\Notifications\\BankSyncFailedNotification') {
                abort(404);
            }

            $notification->markAsRead();

            Log::info('Sync failure marked as resolved by admin', [
                'notification_id' => $notificationId,
                'admin_id' => auth()->id(),
            ]);

            return back()->with('success', 'Sync failure marked as resolved.');
        } catch (\Exception $e) {
            Log::error('Error resolving sync failure', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to resolve failure.');
        }
    }

    /**
     * Get bank account sync status
     */
    public function bankAccountStatus(BankAccount $bankAccount)
    {
        $recentFailures = DatabaseNotification::where('type', 'App\\Notifications\\BankSyncFailedNotification')
            ->where('data->bank_account_id', $bankAccount->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'bank_account' => [
                'id' => $bankAccount->id,
                'bank_name' => $bankAccount->bank_name,
                'is_active' => $bankAccount->is_active,
                'last_sync_at' => $bankAccount->last_sync_at?->toIso8601String(),
                'sync_status' => $bankAccount->is_active ? 'active' : 'inactive',
            ],
            'recent_failures' => $recentFailures->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'error_message' => $notification->data['error_message'] ?? null,
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            })->values(),
        ]);
    }
}
