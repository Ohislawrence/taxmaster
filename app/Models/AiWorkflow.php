<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiWorkflow extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'user_id',
        'workflow_type',
        'tax_period',
        'reference',
        'status',
        'total_steps',
        'completed_steps',
        'current_step',
        'progress_percentage',
        'ai_decisions',
        'confidence_scores',
        'warnings',
        'recommendations',
        'input_data',
        'output_data',
        'context',
        'started_at',
        'completed_at',
        'execution_time_seconds',
        'ai_provider',
        'error_message',
        'error_details',
        'retry_count',
        'requires_human_review',
        'auto_submitted',
        'reviewed_by',
        'reviewed_at',
        'related_entity_type',
        'related_entity_id',
    ];

    protected $casts = [
        'ai_decisions' => 'array',
        'confidence_scores' => 'array',
        'warnings' => 'array',
        'recommendations' => 'array',
        'input_data' => 'array',
        'output_data' => 'array',
        'context' => 'array',
        'error_details' => 'array',
        'requires_human_review' => 'boolean',
        'auto_submitted' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected $appends = [
        'average_confidence',
    ];

    /**
     * Get the business that owns the workflow
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the user who initiated the workflow
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who reviewed the workflow
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get all workflow steps
     */
    public function steps(): HasMany
    {
        return $this->hasMany(AiWorkflowStep::class)->orderBy('step_number');
    }

    /**
     * Get the VAT return created by this workflow
     */
    public function vatReturn(): HasOne
    {
        return $this->hasOne(VATReturn::class, 'ai_workflow_id');
    }

    /**
     * Get the PAYE return created by this workflow
     */
    public function payeReturn(): HasOne
    {
        return $this->hasOne(PayeReturn::class, 'ai_workflow_id');
    }

    /**
     * Get the WHT return created by this workflow
     */
    public function whtReturn(): HasOne
    {
        return $this->hasOne(WhtReturn::class, 'ai_workflow_id');
    }

    /**
     * Get the CIT return created by this workflow
     */
    public function citReturn(): HasOne
    {
        return $this->hasOne(CitReturn::class, 'ai_workflow_id');
    }

    /**
     * Get the related entity (polymorphic)
     */
    public function relatedEntity(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for filtering by workflow type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('workflow_type', $type);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by tax period
     */
    public function scopeForPeriod($query, string $period)
    {
        return $query->where('tax_period', $period);
    }

    /**
     * Scope for pending workflows
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for running workflows
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Scope for completed workflows
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed workflows
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for workflows requiring review
     */
    public function scopeRequiringReview($query)
    {
        return $query->where('status', 'awaiting_review')
            ->where('requires_human_review', true);
    }

    /**
     * Check if workflow is running
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Check if workflow is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if workflow has failed
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if workflow requires review
     */
    public function requiresReview(): bool
    {
        return $this->requires_human_review && $this->status === 'awaiting_review';
    }

    /**
     * Update workflow progress
     */
    public function updateProgress(int $completedSteps, ?string $currentStep = null): void
    {
        $this->completed_steps = $completedSteps;
        $this->current_step = $currentStep;

        if ($this->total_steps > 0) {
            $this->progress_percentage = round(($completedSteps / $this->total_steps) * 100);
        }

        $this->save();
    }

    /**
     * Mark workflow as started
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark workflow as completed
     */
    public function markAsCompleted(array $outputData = []): void
    {
        $executionTime = $this->started_at ? now()->diffInSeconds($this->started_at) : null;

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'execution_time_seconds' => $executionTime,
            'output_data' => $outputData,
            'progress_percentage' => 100,
        ]);
    }

    /**
     * Mark workflow as failed
     */
    public function markAsFailed(string $errorMessage, ?array $errorDetails = null): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'error_details' => $errorDetails,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark workflow as awaiting review
     */
    public function markAsAwaitingReview(array $recommendations = []): void
    {
        $this->update([
            'status' => 'awaiting_review',
            'requires_human_review' => true,
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Add AI decision
     */
    public function addAiDecision(string $step, array $decision): void
    {
        $decisions = $this->ai_decisions ?? [];
        $decisions[$step] = $decision;

        $this->update(['ai_decisions' => $decisions]);
    }

    /**
     * Add warning
     */
    public function addWarning(string $message, string $severity = 'medium', ?array $details = null): void
    {
        $warnings = $this->warnings ?? [];
        $warnings[] = [
            'message' => $message,
            'severity' => $severity,
            'details' => $details,
            'timestamp' => now()->toISOString(),
        ];

        $this->update(['warnings' => $warnings]);
    }

    /**
     * Add recommendation
     */
    public function addRecommendation(string $message, string $priority = 'medium', ?array $details = null): void
    {
        $recommendations = $this->recommendations ?? [];
        $recommendations[] = [
            'message' => $message,
            'priority' => $priority,
            'details' => $details,
            'timestamp' => now()->toISOString(),
        ];

        $this->update(['recommendations' => $recommendations]);
    }

    /**
     * Get average confidence score accessor
     */
    public function getAverageConfidenceAttribute(): ?float
    {
        // First try from confidence_scores array
        if (!empty($this->confidence_scores)) {
            $scores = array_values($this->confidence_scores);
            if (count($scores) > 0) {
                return round(array_sum($scores) / count($scores) * 100, 1);
            }
        }

        // Otherwise calculate from steps
        if ($this->relationLoaded('steps') && $this->steps->count() > 0) {
            $stepScores = $this->steps
                ->filter(fn($step) => $step->confidence_score !== null)
                ->pluck('confidence_score');

            if ($stepScores->count() > 0) {
                return round($stepScores->avg() * 100, 1);
            }
        }

        return null;
    }

    /**
     * Get average confidence score (legacy method)
     */
    public function getAverageConfidence(): ?float
    {
        return $this->average_confidence;
    }

    /**
     * Check if workflow has high confidence
     */
    public function hasHighConfidence(float $threshold = 0.85): bool
    {
        $avgConfidence = $this->getAverageConfidence();
        return $avgConfidence !== null && $avgConfidence >= $threshold;
    }

    /**
     * Generate unique reference
     */
    public static function generateReference(string $workflowType, string $taxPeriod): string
    {
        // Use specific codes for each workflow type
        $typeCodes = [
            'monthly_vat' => 'VAT',
            'monthly_paye' => 'PAYE',
            'monthly_wht' => 'WHT',
            'monthly_cit' => 'CIT',
            'annual_cit' => 'ACIT',
            'compliance_assessment' => 'COMP',
        ];

        $typeCode = $typeCodes[$workflowType] ?? strtoupper(substr($workflowType, 0, 4));
        $periodCode = str_replace('-', '', $taxPeriod);

        // Get the highest existing sequence number for this type and period (including soft-deleted)
        // Then increment by 1 to ensure uniqueness
        $maxSequence = static::withTrashed()
            ->where('workflow_type', $workflowType)
            ->where('tax_period', $taxPeriod)
            ->where('reference', 'LIKE', "WF-{$typeCode}-{$periodCode}-%")
            ->lockForUpdate()
            ->get()
            ->map(function ($workflow) {
                // Extract sequence number from reference (e.g., "WF-VAT-20263-002" -> 2)
                $parts = explode('-', $workflow->reference);
                return (int) end($parts);
            })
            ->max() ?? 0;

        $sequence = str_pad($maxSequence + 1, 3, '0', STR_PAD_LEFT);

        return "WF-{$typeCode}-{$periodCode}-{$sequence}";
    }

    /**
     * Get workflow summary
     */
    public function getSummary(): array
    {
        return [
            'reference' => $this->reference,
            'type' => $this->workflow_type,
            'period' => $this->tax_period,
            'status' => $this->status,
            'progress' => $this->progress_percentage,
            'current_step' => $this->current_step,
            'steps_completed' => "{$this->completed_steps}/{$this->total_steps}",
            'confidence' => $this->getAverageConfidence(),
            'warnings_count' => count($this->warnings ?? []),
            'requires_review' => $this->requires_human_review,
            'started_at' => $this->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'execution_time' => $this->execution_time_seconds ? "{$this->execution_time_seconds}s" : null,
        ];
    }
}
