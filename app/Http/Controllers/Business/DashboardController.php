<?php

namespace App\Http\Controllers\Business;

use App\Models\Business;
use App\Models\GetStartedProgress;
use App\Services\BusinessService;
use App\Services\SubscriptionService;
use Inertia\Inertia;

class DashboardController
{
    protected $businessService;
    protected $subscriptionService;

    public function __construct(BusinessService $businessService, SubscriptionService $subscriptionService)
    {
        $this->businessService = $businessService;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Show business dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $business = $user->ownedBusiness;

        if (!$business) {
            return Inertia::render('Business/NoBusiness');
        }

        $stats = $this->businessService->getBusinessStats($business);

        $business->load(['staff', 'subscriptions']);

        $currentSubscription = $this->subscriptionService->getActiveSubscription($business);
        $usageStats = $this->subscriptionService->getUsageStats($business);

        // Upcoming compliance deadlines (next 30 days)
        $upcomingDeadlines = $business->complianceDeadlines()
            ->where('status', 'pending')
            ->whereBetween('due_date', [now(), now()->addDays(30)])
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Recent transactions
        $recentTransactions = $business->transactions()
            ->with('bankAccount')
            ->latest('transaction_date')
            ->take(5)
            ->get();

        // Bank accounts summary
        $bankAccounts = $business->bankAccounts()
            ->where('is_active', true)
            ->get(['id', 'bank_name', 'account_number', 'balance', 'currency']);

        // Recent VAT returns
        $recentVatReturns = $business->vatReturns()
            ->latest()
            ->take(3)
            ->get();

        // Get Started progress
        $progress = $business->getStartedProgress;
        if (!$progress) {
            $progress = GetStartedProgress::create([
                'business_id' => $business->id,
                'completed_steps' => [],
                'completion_percentage' => 0,
                'started_at' => now(),
            ]);
        }

        // Auto-detect completed steps from actual business data
        $progress->syncFromBusinessData($business);
        $progress->refresh();

        // Get Started steps (simplified for dashboard widget)
        $steps = $this->getStepsForDashboard($business, $progress);

        return Inertia::render('Business/Dashboard', [
            'business' => $business,
            'stats' => $stats,
            'currentSubscription' => $currentSubscription,
            'usageStats' => $usageStats,
            'upcomingDeadlines' => $upcomingDeadlines,
            'recentTransactions' => $recentTransactions,
            'bankAccounts' => $bankAccounts,
            'recentVatReturns' => $recentVatReturns,
            'getStartedProgress' => $progress,
            'completionPercentage' => $progress->completion_percentage,
            'isCompleted' => $progress->completion_percentage === 100,
            'steps' => $steps,
        ]);
    }

    /**
     * Get simplified steps data for dashboard widget
     */
    private function getStepsForDashboard(Business $business, GetStartedProgress $progress): array
    {
        return [
            [
                'id' => 'complete_profile',
                'order' => 1,
                'title' => 'Complete Your Business Profile',
                'is_completed' => $progress->isStepCompleted('complete_profile'),
                'priority' => 'high',
            ],
            [
                'id' => 'link_bank',
                'order' => 2,
                'title' => 'Link Your Bank Account',
                'is_completed' => $progress->isStepCompleted('link_bank'),
                'priority' => 'high',
            ],
            [
                'id' => 'choose_plan',
                'order' => 3,
                'title' => 'Choose Your Subscription Plan',
                'is_completed' => $progress->isStepCompleted('choose_plan'),
                'priority' => 'high',
            ],
            [
                'id' => 'add_staff',
                'order' => 4,
                'title' => 'Add Your Team Members',
                'is_completed' => $progress->isStepCompleted('add_staff'),
                'priority' => 'medium',
            ],
            [
                'id' => 'file_first_return',
                'order' => 5,
                'title' => 'File Your First Tax Return',
                'is_completed' => $progress->isStepCompleted('file_first_return'),
                'priority' => 'high',
            ],
            [
                'id' => 'sync_transactions',
                'order' => 6,
                'title' => 'Enable Transaction Sync',
                'is_completed' => $progress->isStepCompleted('sync_transactions'),
                'priority' => 'medium',
            ],
            [
                'id' => 'check_limits',
                'order' => 7,
                'title' => 'Check Your Usage & Limits',
                'is_completed' => $progress->isStepCompleted('check_limits'),
                'priority' => 'low',
            ],
        ];
    }
}
