<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessActivityLog;
use App\Models\BusinessStaff;
use App\Models\BusinessSubscription;
use Illuminate\Support\Str;

class BusinessService
{
    /**
     * Create a new business
     */
    public function createBusiness(array $data): Business
    {
        $data['slug'] = Str::slug($data['name']);

        $business = Business::create($data);

        // Create activity log
        BusinessActivityLog::create([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
            'action' => 'business_created',
            'subject_type' => Business::class,
            'subject_id' => $business->id,
            'description' => "Business '{$business->name}' was created",
        ]);

        // Create default subscription
        $this->createDefaultSubscription($business);

        return $business;
    }

    /**
     * Update business information
     */
    public function updateBusiness(Business $business, array $data): Business
    {
        $originalData = $business->toArray();

        $business->update($data);

        // Log changes
        BusinessActivityLog::create([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
            'action' => 'business_updated',
            'subject_type' => Business::class,
            'subject_id' => $business->id,
            'description' => "Business information was updated",
            'changes' => $this->getChanges($originalData, $business->toArray()),
        ]);

        return $business;
    }

    /**
     * Add staff member to business
     */
    public function addStaff(Business $business, array $data): BusinessStaff
    {
        $staff = $business->staff()->create($data);

        BusinessActivityLog::create([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
            'action' => 'staff_added',
            'subject_type' => BusinessStaff::class,
            'subject_id' => $staff->id,
            'description' => "Staff member '{$staff->full_name}' was added",
        ]);

        return $staff;
    }

    /**
     * Update staff member
     */
    public function updateStaff(BusinessStaff $staff, array $data): BusinessStaff
    {
        $originalData = $staff->toArray();

        $staff->update($data);

        BusinessActivityLog::create([
            'business_id' => $staff->business_id,
            'user_id' => auth()->id(),
            'action' => 'staff_updated',
            'subject_type' => BusinessStaff::class,
            'subject_id' => $staff->id,
            'description' => "Staff member '{$staff->full_name}' was updated",
            'changes' => $this->getChanges($originalData, $staff->toArray()),
        ]);

        return $staff;
    }

    /**
     * Remove staff member
     */
    public function removeStaff(BusinessStaff $staff): void
    {
        $business = $staff->business;

        BusinessActivityLog::create([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
            'action' => 'staff_removed',
            'subject_type' => BusinessStaff::class,
            'subject_id' => $staff->id,
            'description' => "Staff member '{$staff->full_name}' was removed",
        ]);

        $staff->delete();
    }

    /**
     * Get business statistics
     */
    public function getBusinessStats(Business $business): array
    {
        return [
            'total_staff' => $business->staff()->where('status', 'active')->count(),
            'bank_balance' => $business->bankAccounts()
                ->where('is_active', true)
                ->sum('balance'),
            'pending_deadlines' => $business->complianceDeadlines()
                ->where('status', 'pending')
                ->count(),
            'overdue_deadlines' => $business->complianceDeadlines()
                ->where('status', 'overdue')
                ->count(),
            'monthly_income' => $business->transactions()
                ->where('type', 'income')
                ->whereMonth('transaction_date', now()->month)
                ->sum('amount'),
            'monthly_expenses' => $business->transactions()
                ->where('type', 'expense')
                ->whereMonth('transaction_date', now()->month)
                ->sum('amount'),
            'vat_pending' => $business->vatReturns()
                ->whereIn('status', ['draft', 'submitted'])
                ->sum('vat_due'),
            'subscription_status' => $business->subscription()
                ->latest()
                ->first()?->status ?? 'inactive',
        ];
    }

    /**
     * Create default subscription
     */
    private function createDefaultSubscription(Business $business): BusinessSubscription
    {
        $basicPlan = config('taxmaster.pricing.plans.basic');

        return BusinessSubscription::create([
            'business_id' => $business->id,
            'plan_type' => 'basic',
            'monthly_price' => $basicPlan['monthly_price'],
            'annual_price' => $basicPlan['annual_price'],
            'max_staff_members' => $basicPlan['max_staff'],
            'max_returns_per_year' => $basicPlan['max_returns_per_year'],
            'ai_analysis_included' => $basicPlan['features']['ai_analysis'],
            'payment_automation' => $basicPlan['features']['payment_automation'],
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'started_at' => now(),
            'renews_at' => now()->addMonth(),
        ]);
    }

    /**
     * Get changes between two arrays
     */
    private function getChanges(array $original, array $updated): array
    {
        $changes = [];

        foreach ($updated as $key => $value) {
            if (!isset($original[$key]) || $original[$key] != $value) {
                $changes[$key] = [
                    'from' => $original[$key] ?? null,
                    'to' => $value,
                ];
            }
        }

        return $changes;
    }
}
