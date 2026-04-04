<?php

namespace App\Jobs;

use App\Models\AiWorkflow;
use App\Models\Business;
use App\Services\TaxAiOrchestrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Process Tax Workflow Job - Executes AI tax workflows asynchronously
 *
 * This job handles long-running AI tax workflow execution in the background,
 * allowing the user to continue working while the AI processes their tax obligations.
 */
class ProcessTaxWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Business $business;
    public string $workflowType;
    public array $parameters;
    public ?int $userId;

    /**
     * Maximum number of retries
     */
    public int $tries = 3;

    /**
     * Timeout in seconds (10 minutes)
     */
    public int $timeout = 600;

    /**
     * Create a new job instance
     */
    public function __construct(
        Business $business,
        string $workflowType,
        array $parameters = [],
        ?int $userId = null
    ) {
        $this->business = $business;
        $this->workflowType = $workflowType;
        $this->parameters = $parameters;
        $this->userId = $userId;
    }

    /**
     * Execute the job
     */
    public function handle(): void
    {
        Log::info('Processing tax workflow job', [
            'business_id' => $this->business->id,
            'workflow_type' => $this->workflowType,
            'parameters' => $this->parameters,
        ]);

        try {
            $orchestrator = new TaxAiOrchestrator($this->business);

            // Validate data availability before starting workflow
            $dataCheck = $orchestrator->checkDataAvailability(
                $this->workflowType,
                $this->parameters['month'] ?? null,
                $this->parameters['year'] ?? null
            );

            if (!$dataCheck['available']) {
                $errorMessage = 'Workflow cannot run: ';

                if (!empty($dataCheck['missing'])) {
                    $errorMessage .= implode(' ', $dataCheck['missing']);
                } else {
                    $errorMessage .= 'Required data is not available.';
                }

                Log::warning('Workflow aborted - data not available', [
                    'business_id' => $this->business->id,
                    'workflow_type' => $this->workflowType,
                    'missing_data' => $dataCheck['missing'],
                    'data_counts' => $dataCheck['data_counts'],
                ]);

                throw new Exception($errorMessage);
            }

            $workflow = match($this->workflowType) {
                'monthly_vat' => $orchestrator->executeMonthlyVATWorkflow(
                    $this->parameters['month'],
                    $this->parameters['year'],
                    $this->userId
                ),
                'monthly_paye' => $orchestrator->executeMonthlyPAYEWorkflow(
                    $this->parameters['month'],
                    $this->parameters['year'],
                    $this->userId
                ),
                'monthly_wht' => $orchestrator->executeMonthlyWHTWorkflow(
                    $this->parameters['month'],
                    $this->parameters['year'],
                    $this->userId
                ),
                'compliance_assessment' => $orchestrator->executeComplianceAssessment($this->userId),
                default => throw new Exception("Unknown workflow type: {$this->workflowType}"),
            };

            Log::info('Tax workflow completed successfully', [
                'workflow_id' => $workflow->id,
                'status' => $workflow->status,
                'reference' => $workflow->reference,
            ]);

            // TODO: Send notification to user about completion
            // Notification::send($user, new WorkflowCompletedNotification($workflow));

        } catch (Exception $e) {
            Log::error('Tax workflow job failed', [
                'business_id' => $this->business->id,
                'workflow_type' => $this->workflowType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger job retry
            throw $e;
        }
    }

    /**
     * Handle a job failure
     */
    public function failed(Exception $exception): void
    {
        Log::error('Tax workflow job failed permanently', [
            'business_id' => $this->business->id,
            'workflow_type' => $this->workflowType,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // TODO: Send notification to user about failure
        // Notification::send($user, new WorkflowFailedNotification($exception));
    }

    /**
     * Get the tags that should be assigned to this job
     */
    public function tags(): array
    {
        return [
            'business:' . $this->business->id,
            'workflow:' . $this->workflowType,
            'tax-automation',
        ];
    }
}
