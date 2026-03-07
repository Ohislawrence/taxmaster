<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class GetStartedProgress extends Model
{
    protected $table = 'get_started_progress';

    public const TOTAL_STEPS = 7;

    protected $fillable = [
        'business_id',
        'completed_steps',
        'completion_percentage',
        'dismissed',
        'dismissed_at',
        'remind_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'completed_steps' => 'array',
        'dismissed' => 'boolean',
        'dismissed_at' => 'datetime',
        'remind_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Sync completed steps by inspecting actual business data.
     * Call this from any controller that renders the progress widget.
     */
    public function syncFromBusinessData(Business $business): void
    {
        $completedSteps = $this->completed_steps ?? [];
        $changed = false;

        // Step 1: Complete business profile
        if ($business->email && $business->phone && $business->address && $business->business_type) {
            if (!in_array('complete_profile', $completedSteps)) {
                $completedSteps[] = 'complete_profile';
                $changed = true;
            }
        }

        // Step 2: Link bank account
        if (BankAccount::where('business_id', $business->id)->where('is_active', true)->exists()) {
            if (!in_array('link_bank', $completedSteps)) {
                $completedSteps[] = 'link_bank';
                $changed = true;
            }
        }

        // Step 3: Choose subscription plan (non-Free)
        $subscription = $business->activeSubscription();
        if ($subscription && $subscription->plan && $subscription->plan->name !== 'Free') {
            if (!in_array('choose_plan', $completedSteps)) {
                $completedSteps[] = 'choose_plan';
                $changed = true;
            }
        }

        // Step 4: Set up staff
        if (BusinessStaff::where('business_id', $business->id)->count() > 0) {
            if (!in_array('add_staff', $completedSteps)) {
                $completedSteps[] = 'add_staff';
                $changed = true;
            }
        }

        // Step 5: File first tax return (PAYE, WHT, VAT or CIT)
        $hasReturns = PayeReturn::where('business_id', $business->id)->exists()
            || WhtReturn::where('business_id', $business->id)->exists()
            || VatReturn::where('business_id', $business->id)->exists()
            || CitReturn::where('business_id', $business->id)->exists();
        if ($hasReturns && !in_array('file_first_return', $completedSteps)) {
            $completedSteps[] = 'file_first_return';
            $changed = true;
        }

        // Step 6: Enable transaction sync (Mono bank API)
        if (BankAccount::where('business_id', $business->id)->whereNotNull('last_synced_at')->exists()) {
            if (!in_array('sync_transactions', $completedSteps)) {
                $completedSteps[] = 'sync_transactions';
                $changed = true;
            }
        }

        // Step 7: Check subscription limits
        if (BusinessStaff::where('business_id', $business->id)->count() >= 3 ||
            CitReturn::where('business_id', $business->id)->count() >= 2) {
            if (!in_array('check_limits', $completedSteps)) {
                $completedSteps[] = 'check_limits';
                $changed = true;
            }
        }

        if ($changed) {
            $this->completed_steps = $completedSteps;
            $this->completion_percentage = (int) ((count($completedSteps) / self::TOTAL_STEPS) * 100);

            if (count($completedSteps) === self::TOTAL_STEPS && !$this->completed_at) {
                $this->completed_at = now();
            }

            $this->save();
        }
    }

    // Helpers
    public function isStepCompleted(string $stepId): bool
    {
        return in_array($stepId, $this->completed_steps ?? []);
    }

    public function markStepCompleted(string $stepId)
    {
        $completedSteps = $this->completed_steps ?? [];
        if (!in_array($stepId, $completedSteps)) {
            $completedSteps[] = $stepId;
            $this->completed_steps = $completedSteps;
        }

        // Update progress percentage
        $this->completion_percentage = (int) ((count($completedSteps) / self::TOTAL_STEPS) * 100);

        // Mark as completed if all steps done
        if (count($completedSteps) === self::TOTAL_STEPS && !$this->completed_at) {
            $this->completed_at = now();
        }

        $this->save();
    }

    public function markStepIncomplete(string $stepId)
    {
        $completedSteps = $this->completed_steps ?? [];
        $this->completed_steps = array_filter($completedSteps, fn($id) => $id !== $stepId);

        // Update progress percentage
        $this->completion_percentage = (int) ((count($this->completed_steps) / self::TOTAL_STEPS) * 100);
        $this->completed_at = null;

        $this->save();
    }

    public function dismiss()
    {
        $this->dismissed = true;
        $this->dismissed_at = now();
        $this->save();
    }

    public function undismiss()
    {
        $this->dismissed = false;
        $this->dismissed_at = null;
        $this->save();
    }

    public function snoozeUntil($minutes = 60)
    {
        $this->remind_at = now()->addMinutes($minutes);
        $this->save();
    }

    public function isSnoozed(): bool
    {
        return $this->remind_at && $this->remind_at->isFuture();
    }

    public function canShowGuide(): bool
    {
        return !$this->dismissed && !$this->isSnoozed();
    }
}
