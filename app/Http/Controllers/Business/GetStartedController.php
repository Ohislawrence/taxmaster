<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\GetStartedProgress;
use App\Models\BankAccount;
use App\Models\CitReturn;
use App\Models\PayeReturn;
use App\Models\VatReturn;
use App\Models\WhtReturn;
use App\Models\BusinessStaff;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GetStartedController extends Controller
{
    /**
     * Display the Get Started guide
     */
    public function index(Request $request)
    {
        $business = $request->user()->ownedBusiness;

        if (!$business) {
            return redirect()->route('business.setup');
        }

        // Get or create progress tracking
        $progress = $business->getStartedProgress ?? GetStartedProgress::create([
            'business_id' => $business->id,
            'completed_steps' => [],
            'completion_percentage' => 0,
            'started_at' => now(),
        ]);

        // Mark steps as completed based on actual usage
        $progress->syncFromBusinessData($business);

        // Prepare step data
        $steps = $this->getStepsData($business, $progress);

        // Refresh after sync
        $progress->refresh();

        return Inertia::render('Business/GetStarted/Index', [
            'progress' => $progress,
            'steps' => $steps,
            'completionPercentage' => $progress->completion_percentage,
            'isCompleted' => $progress->completion_percentage === 100,
        ]);
    }

    /**
     * Mark a step as completed
     */
    public function completeStep(Request $request)
    {
        $business = $request->user()->ownedBusiness;
        $progress = $business->getStartedProgress ?? GetStartedProgress::create([
            'business_id' => $business->id,
        ]);

        $stepId = $request->input('step_id');
        $progress->markStepCompleted($stepId);

        return response()->json([
            'success' => true,
            'completion_percentage' => $progress->completion_percentage,
            'completed_steps' => $progress->completed_steps,
        ]);
    }

    /**
     * Mark a step as incomplete
     */
    public function incompleteStep(Request $request)
    {
        $business = $request->user()->ownedBusiness;
        $progress = $business->getStartedProgress;

        if (!$progress) {
            return response()->json(['success' => false, 'message' => 'Progress not found'], 404);
        }

        $stepId = $request->input('step_id');
        $progress->markStepIncomplete($stepId);

        return response()->json([
            'success' => true,
            'completion_percentage' => $progress->completion_percentage,
        ]);
    }

    /**
     * Dismiss the Get Started guide
     */
    public function dismiss(Request $request)
    {
        $business = $request->user()->ownedBusiness;
        $progress = $business->getStartedProgress ?? GetStartedProgress::create([
            'business_id' => $business->id,
        ]);

        $snoozeMinutes = $request->input('snooze_minutes', 0);

        if ($snoozeMinutes > 0) {
            $progress->snoozeUntil($snoozeMinutes);
        } else {
            $progress->dismiss();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Undismiss the Get Started guide
     */
    public function undismiss(Request $request)
    {
        $business = $request->user()->ownedBusiness;
        $progress = $business->getStartedProgress;

        if (!$progress) {
            return response()->json(['success' => false, 'message' => 'Progress not found'], 404);
        }

        $progress->undismiss();

        return response()->json(['success' => true]);
    }

    /**
     * Get structured step data for frontend
     */
    private function getStepsData(Business $business, GetStartedProgress $progress): array
    {
        $bankAccountCount = BankAccount::where('business_id', $business->id)->count();
        $staffCount = BusinessStaff::where('business_id', $business->id)->count();
        $planName = $business->activeSubscription()?->plan?->name ?? 'Free';
        $hasReturns = PayeReturn::where('business_id', $business->id)->exists() ||
                     WhtReturn::where('business_id', $business->id)->exists() ||
                     CitReturn::where('business_id', $business->id)->exists() ||
                     VatReturn::where('business_id', $business->id)->exists();

        return [
            [
                'id' => 'complete_profile',
                'order' => 1,
                'title' => 'Complete Your Business Profile',
                'description' => 'Set up your business information, contact details, and settings',
                'benefits' => [
                    'Professional business appearance',
                    'Accurate tax filings',
                    'Better record keeping',
                ],
                'action_label' => 'Complete Profile',
                'action_url' => route('business.settings.index'),
                'is_completed' => $progress->isStepCompleted('complete_profile'),
                'icon' => 'building',
                'estimated_time' => '5 min',
                'priority' => 'high',
                'progress_indicators' => [
                    'Email: ' . ($business->email ? '✓' : '✗'),
                    'Phone: ' . ($business->phone ? '✓' : '✗'),
                    'Address: ' . ($business->address ? '✓' : '✗'),
                    'Business Type: ' . ($business->business_type ? '✓' : '✗'),
                ],
            ],
            [
                'id' => 'link_bank',
                'order' => 2,
                'title' => 'Link Your Bank Account',
                'description' => 'Connect your bank account for automatic transaction tracking and categorization',
                'benefits' => [
                    'Automatic transaction imports',
                    'Real-time account balances',
                    'Better expense tracking',
                    'Save time on data entry',
                ],
                'action_label' => 'Link Bank Account',
                'action_url' => route('business.banks.index'),
                'is_completed' => $progress->isStepCompleted('link_bank'),
                'icon' => 'university',
                'estimated_time' => '3 min',
                'priority' => 'high',
                'progress_indicators' => [
                    'Connected accounts: ' . $bankAccountCount,
                ],
            ],
            [
                'id' => 'choose_plan',
                'order' => 3,
                'title' => 'Choose Your Subscription Plan',
                'description' => 'Upgrade to unlock more features like VAT filing, AI analysis, and team management',
                'benefits' => [
                    'Access more tax forms (VAT, CIT, CGT)',
                    'AI-powered tax optimization',
                    'More staff members allowed',
                    'Advanced reporting features',
                ],
                'action_label' => 'View Plans',
                'action_url' => route('business.plans.index'),
                'is_completed' => $progress->isStepCompleted('choose_plan'),
                'icon' => 'crown',
                'estimated_time' => '5 min',
                'priority' => 'high',
                'progress_indicators' => [
                    'Current plan: ' . $planName,
                ],
            ],
            [
                'id' => 'add_staff',
                'order' => 4,
                'title' => 'Add Your Team Members',
                'description' => 'Invite accountants, bookkeepers, or team members to manage taxes collaboratively',
                'benefits' => [
                    'Distribute workload',
                    'Better collaboration',
                    'Easy review process',
                    'Assign specific roles',
                ],
                'action_label' => 'Add Staff',
                'action_url' => route('business.staff.index'),
                'is_completed' => $progress->isStepCompleted('add_staff'),
                'icon' => 'users',
                'estimated_time' => '5 min',
                'priority' => 'medium',
                'progress_indicators' => [
                    'Team members: ' . $staffCount,
                ],
            ],
            [
                'id' => 'file_first_return',
                'order' => 5,
                'title' => 'File Your First Tax Return',
                'description' => 'Start with a simple PAYE or WHT return to get comfortable with the filing process',
                'benefits' => [
                    'Meet compliance deadlines',
                    'Understand the filing workflow',
                    'Avoid penalties',
                    'Track tax obligations',
                ],
                'action_label' => 'File PAYE Return',
                'action_url' => route('business.paye.index'),
                'is_completed' => $progress->isStepCompleted('file_first_return') || $hasReturns,
                'icon' => 'file-alt',
                'estimated_time' => '10 min',
                'priority' => 'high',
                'progress_indicators' => [
                    'Returns filed: ' . (PayeReturn::where('business_id', $business->id)->count() +
                                         WhtReturn::where('business_id', $business->id)->count() +
                                         CitReturn::where('business_id', $business->id)->count() +
                                         VatReturn::where('business_id', $business->id)->count()),
                ],
            ],
            [
                'id' => 'sync_transactions',
                'order' => 6,
                'title' => 'Enable Transaction Sync',
                'description' => 'Sync your bank transactions in real-time for accurate expense and income tracking',
                'benefits' => [
                    'Real-time transaction data',
                    'Automatic categorization',
                    'Complete audit trail',
                    'Accurate financial reports',
                ],
                'action_label' => 'Setup Auto-Sync',
                'action_url' => route('business.banks.index'),
                'is_completed' => $progress->isStepCompleted('sync_transactions'),
                'icon' => 'sync',
                'estimated_time' => '3 min',
                'priority' => 'medium',
                'requires_step' => 'link_bank',
                'progress_indicators' => [
                    'Synced accounts: ' . BankAccount::where('business_id', $business->id)
                        ->where('last_synced_at', '!=', null)->count(),
                ],
            ],
            [
                'id' => 'check_limits',
                'order' => 7,
                'title' => 'Check Your Usage & Limits',
                'description' => 'Review your subscription limits and plan accordingly as your business grows',
                'benefits' => [
                    'Understand your plan benefits',
                    'Avoid usage surprises',
                    'Plan upgrades in advance',
                    'Optimize your subscription',
                ],
                'action_label' => 'View Subscription',
                'action_url' => route('business.subscription'),
                'is_completed' => $progress->isStepCompleted('check_limits'),
                'icon' => 'chart-line',
                'estimated_time' => '5 min',
                'priority' => 'low',
                'progress_indicators' => [
                    'Staff: ' . $staffCount . ' of ' . ($business->activeSubscription()?->plan?->max_staff_members ?? 1),
                ],
            ],
        ];
    }
}
