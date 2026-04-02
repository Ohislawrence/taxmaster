<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiWorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_workflow_id',
        'step_number',
        'step_name',
        'agent_name',
        'description',
        'status',
        'started_at',
        'completed_at',
        'execution_time_seconds',
        'prompt',
        'ai_response',
        'parsed_response',
        'confidence_score',
        'input_data',
        'output_data',
        'validations',
        'warnings',
        'error_message',
        'error_details',
        'retry_count',
        'ai_model',
        'tokens_used',
        'cost',
    ];

    protected $casts = [
        'parsed_response' => 'array',
        'input_data' => 'array',
        'output_data' => 'array',
        'validations' => 'array',
        'warnings' => 'array',
        'error_details' => 'array',
        'confidence_score' => 'decimal:2',
        'cost' => 'decimal:4',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the workflow that owns this step
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(AiWorkflow::class, 'ai_workflow_id');
    }

    /**
     * Scope for filtering by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for completed steps
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed steps
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Check if step is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if step has failed
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark step as started
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark step as completed
     */
    public function markAsCompleted(array $outputData = [], ?float $confidenceScore = null): void
    {
        $executionTime = $this->started_at ? now()->diffInSeconds($this->started_at) : null;

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'execution_time_seconds' => $executionTime,
            'output_data' => $outputData,
            'confidence_score' => $confidenceScore,
        ]);
    }

    /**
     * Mark step as failed
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
     * Add validation result
     */
    public function addValidation(string $rule, bool $passed, ?string $message = null): void
    {
        $validations = $this->validations ?? [];
        $validations[] = [
            'rule' => $rule,
            'passed' => $passed,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];
        
        $this->update(['validations' => $validations]);
    }

    /**
     * Add warning
     */
    public function addWarning(string $message, string $severity = 'medium'): void
    {
        $warnings = $this->warnings ?? [];
        $warnings[] = [
            'message' => $message,
            'severity' => $severity,
            'timestamp' => now()->toISOString(),
        ];
        
        $this->update(['warnings' => $warnings]);
    }

    /**
     * Check if step has high confidence
     */
    public function hasHighConfidence(float $threshold = 0.85): bool
    {
        return $this->confidence_score !== null && $this->confidence_score >= $threshold;
    }

    /**
     * Get step summary
     */
    public function getSummary(): array
    {
        return [
            'step_number' => $this->step_number,
            'step_name' => $this->step_name,
            'agent' => $this->agent_name,
            'status' => $this->status,
            'confidence' => $this->confidence_score,
            'execution_time' => $this->execution_time_seconds ? "{$this->execution_time_seconds}s" : null,
            'warnings_count' => count($this->warnings ?? []),
            'started_at' => $this->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
        ];
    }
}
