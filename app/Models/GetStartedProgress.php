<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class GetStartedProgress extends Model
{
    protected $table = 'get_started_progress';
    
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
        $totalSteps = 7; // Total number of onboarding steps
        $this->completion_percentage = (int) ((count($completedSteps) / $totalSteps) * 100);
        
        // Mark as completed if all steps done
        if (count($completedSteps) === $totalSteps && !$this->completed_at) {
            $this->completed_at = now();
        }
        
        $this->save();
    }
    
    public function markStepIncomplete(string $stepId)
    {
        $completedSteps = $this->completed_steps ?? [];
        $this->completed_steps = array_filter($completedSteps, fn($id) => $id !== $stepId);
        
        // Update progress percentage
        $totalSteps = 7;
        $this->completion_percentage = (int) ((count($this->completed_steps) / $totalSteps) * 100);
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
