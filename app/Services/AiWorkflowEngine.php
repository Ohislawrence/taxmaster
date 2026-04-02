<?php

namespace App\Services;

use App\Models\Business;
use App\Models\AiWorkflow;
use App\Services\TaxAgents\BaseTaxAgent;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * AI Workflow Engine - Generic workflow execution engine
 *
 * Provides a flexible framework for executing multi-agent AI workflows
 * with dependency management, error handling, and state tracking.
 */
class AiWorkflowEngine
{
    protected Business $business;
    protected array $agents = [];
    protected array $workflowDefinitions = [];

    public function __construct(Business $business)
    {
        $this->business = $business;
        $this->loadWorkflowDefinitions();
    }

    /**
     * Load workflow definitions from configuration
     */
    protected function loadWorkflowDefinitions(): void
    {
        $this->workflowDefinitions = config('ai-automation.workflows', []);
    }

    /**
     * Register an agent for use in workflows
     */
    public function registerAgent(string $name, BaseTaxAgent $agent): void
    {
        $this->agents[$name] = $agent;
    }

    /**
     * Execute a predefined workflow
     */
    public function executeWorkflow(string $workflowName, array $parameters = []): array
    {
        if (!isset($this->workflowDefinitions[$workflowName])) {
            throw new Exception("Workflow '{$workflowName}' not defined");
        }

        $definition = $this->workflowDefinitions[$workflowName];

        Log::info("Executing workflow: {$workflowName}", [
            'business_id' => $this->business->id,
            'parameters' => $parameters,
        ]);

        $context = array_merge([
            'business' => $this->business,
            'started_at' => now(),
        ], $parameters);

        $results = [];
        $success = true;

        foreach ($definition['steps'] as $step) {
            try {
                $stepResult = $this->executeWorkflowStep($step, $context, $results);
                $results[$step['name']] = $stepResult;

                // Update context with step results
                $context[$step['name'] . '_result'] = $stepResult;

                // Check if step failed
                if (isset($stepResult['success']) && !$stepResult['success']) {
                    $success = false;

                    // Check if we should continue on failure
                    if (!($step['continue_on_failure'] ?? false)) {
                        break;
                    }
                }

            } catch (Exception $e) {
                Log::error("Workflow step failed", [
                    'workflow' => $workflowName,
                    'step' => $step['name'],
                    'error' => $e->getMessage(),
                ]);

                $results[$step['name']] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];

                $success = false;

                if (!($step['continue_on_failure'] ?? false)) {
                    break;
                }
            }
        }

        return [
            'success' => $success,
            'workflow' => $workflowName,
            'results' => $results,
            'execution_time' => now()->diffInSeconds($context['started_at']),
        ];
    }

    /**
     * Execute a workflow step
     */
    protected function executeWorkflowStep(array $step, array $context, array $previousResults): array
    {
        $agentName = $step['agent'];
        $action = $step['action'];

        if (!isset($this->agents[$agentName])) {
            throw new Exception("Agent '{$agentName}' not registered");
        }

        $agent = $this->agents[$agentName];

        // Check dependencies
        if (!empty($step['depends_on'])) {
            foreach ($step['depends_on'] as $dependency) {
                if (!isset($previousResults[$dependency]) ||
                    (isset($previousResults[$dependency]['success']) && !$previousResults[$dependency]['success'])) {
                    return [
                        'success' => false,
                        'error' => "Dependency '{$dependency}' not satisfied",
                        'skipped' => true,
                    ];
                }
            }
        }

        // Prepare input data
        $inputData = $this->prepareStepInput($step, $context, $previousResults);

        // Execute agent action
        if (!method_exists($agent, $action)) {
            throw new Exception("Action '{$action}' not found on agent '{$agentName}'");
        }

        $result = call_user_func_array([$agent, $action], $inputData);

        return $result;
    }

    /**
     * Prepare input data for a step
     */
    protected function prepareStepInput(array $step, array $context, array $previousResults): array
    {
        $input = $step['input'] ?? [];
        $preparedInput = [];

        foreach ($input as $key => $source) {
            // Support different input sources
            if (strpos($source, 'context.') === 0) {
                $contextKey = substr($source, 8);
                $preparedInput[] = $context[$contextKey] ?? null;
            } elseif (strpos($source, 'result.') === 0) {
                $resultParts = explode('.', substr($source, 7));
                $value = $previousResults;

                foreach ($resultParts as $part) {
                    $value = $value[$part] ?? null;
                    if ($value === null) break;
                }

                $preparedInput[] = $value;
            } else {
                $preparedInput[] = $source;
            }
        }

        return $preparedInput;
    }

    /**
     * Execute workflow with parallel steps
     */
    public function executeParallelWorkflow(string $workflowName, array $parameters = []): array
    {
        // TODO: Implement parallel execution using Laravel queues
        // For now, execute sequentially
        return $this->executeWorkflow($workflowName, $parameters);
    }

    /**
     * Get workflow status
     */
    public function getWorkflowStatus(int $workflowId): ?array
    {
        $workflow = AiWorkflow::with('steps')->find($workflowId);

        if (!$workflow) {
            return null;
        }

        return [
            'id' => $workflow->id,
            'reference' => $workflow->reference,
            'status' => $workflow->status,
            'progress' => $workflow->progress_percentage,
            'current_step' => $workflow->current_step,
            'steps' => $workflow->steps->map(fn($step) => $step->getSummary()),
            'confidence' => $workflow->getAverageConfidence(),
            'warnings' => $workflow->warnings,
            'requires_review' => $workflow->requires_human_review,
        ];
    }

    /**
     * Retry failed workflow
     */
    public function retryWorkflow(int $workflowId): array
    {
        $workflow = AiWorkflow::with('steps')->find($workflowId);

        if (!$workflow || !$workflow->hasFailed()) {
            throw new Exception('Workflow not found or not in failed state');
        }

        Log::info('Retrying workflow', [
            'workflow_id' => $workflowId,
            'type' => $workflow->workflow_type,
        ]);

        // Get the workflow orchestrator
        $orchestrator = new TaxAiOrchestrator($workflow->business);

        // Extract period from tax_period (e.g., "2026-03" => month: "03", year: "2026")
        $periodParts = explode('-', $workflow->tax_period);
        $year = $periodParts[0] ?? null;
        $month = $periodParts[1] ?? null;

        // Retry based on workflow type
        try {
            $newWorkflow = match($workflow->workflow_type) {
                'monthly_vat' => $orchestrator->executeMonthlyVATWorkflow($month, $year, $workflow->user_id),
                'monthly_paye' => $orchestrator->executeMonthlyPAYEWorkflow($month, $year, $workflow->user_id),
                'monthly_wht' => $orchestrator->executeMonthlyWHTWorkflow($month, $year, $workflow->user_id),
                'compliance_assessment' => $orchestrator->executeComplianceAssessment($workflow->user_id),
                default => throw new Exception('Unsupported workflow type for retry'),
            };

            return [
                'success' => true,
                'new_workflow_id' => $newWorkflow->id,
                'status' => $newWorkflow->status,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel running workflow
     */
    public function cancelWorkflow(int $workflowId): bool
    {
        $workflow = AiWorkflow::find($workflowId);

        if (!$workflow || !$workflow->isRunning()) {
            return false;
        }

        $workflow->update([
            'status' => 'cancelled',
            'completed_at' => now(),
            'error_message' => 'Cancelled by user',
        ]);

        Log::info('Workflow cancelled', ['workflow_id' => $workflowId]);

        return true;
    }

    /**
     * Get workflow statistics for business
     */
    public function getWorkflowStatistics(): array
    {
        $workflows = AiWorkflow::where('business_id', $this->business->id)->get();

        // Check AI configuration
        $aiProvider = env('AI_PROVIDER', 'deepseek');
        $aiConfigured = !empty(env('DEEPSEEK_API_KEY')) || !empty(env('GEMINI_API_KEY'));
        $totalWorkflows = $workflows->count();
        $completedWorkflows = $workflows->where('status', 'completed')->count();

        return [
            'total_workflows' => $totalWorkflows,
            'completed' => $completedWorkflows,
            'failed' => $workflows->where('status', 'failed')->count(),
            'running' => $workflows->where('status', 'running')->count(),
            'pending' => $workflows->where('status', 'pending')->count(),
            'requiring_review' => $workflows->where('requires_human_review', true)->where('reviewed_at', null)->count(),
            'completion_rate' => $totalWorkflows > 0 ? round(($completedWorkflows / $totalWorkflows) * 100, 1) : 0,
            'average_confidence' => round($workflows->filter(fn($w) => $w->average_confidence !== null)->avg('average_confidence') ?? 0, 1),
            'total_execution_time' => $workflows->sum('execution_time_seconds'),
            'by_type' => $workflows->groupBy('workflow_type')->map->count(),
            'ai_provider' => $aiProvider,
            'ai_configured' => $aiConfigured,
        ];
    }
}
