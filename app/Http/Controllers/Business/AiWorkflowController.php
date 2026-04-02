<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessTaxWorkflowJob;
use App\Models\AiWorkflow;
use App\Services\TaxAiOrchestrator;
use App\Services\AiWorkflowEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AiWorkflowController extends Controller
{
    /**
     * Display listing of AI workflows
     */
    public function index(Request $request)
    {
        $business = $request->user()->defaultBusiness();

        if (!$business) {
            return redirect()->route('business.dashboard');
        }

        $workflows = AiWorkflow::where('business_id', $business->id)
            ->with(['user', 'steps'])
            ->when($request->type, function ($query, $type) {
                $query->where('workflow_type', $type);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get statistics
        $engine = new AiWorkflowEngine($business);
        $statistics = $engine->getWorkflowStatistics();

        // Get workflow types with metadata
        $workflowTypes = [
            [
                'value' => 'monthly_vat',
                'label' => 'Monthly VAT Processing',
                'description' => 'Analyze transactions, calculate VAT, and generate Form VAT 001',
                'icon' => 'fas fa-receipt text-blue-600',
                'steps' => ['Collect Transactions', 'Calculate VAT', 'Generate Return', 'Compliance Check'],
            ],
            [
                'value' => 'monthly_paye',
                'label' => 'Monthly PAYE Processing',
                'description' => 'Process payroll and generate employee tax returns',
                'icon' => 'fas fa-users text-green-600',
                'steps' => ['Collect Payroll', 'Calculate PAYE', 'Generate Return', 'Compliance Check'],
            ],
            [
                'value' => 'monthly_wht',
                'label' => 'Monthly WHT Processing',
                'description' => 'Classify transactions and generate withholding tax schedules',
                'icon' => 'fas fa-hand-holding-usd text-purple-600',
                'steps' => ['Collect Transactions', 'Classify WHT', 'Generate Schedules', 'Compliance Check'],
            ],
            [
                'value' => 'monthly_cit',
                'label' => 'Monthly CIT Self-Assessment',
                'description' => 'Calculate taxable income, apply tax rates, and generate CIT return',
                'icon' => 'fas fa-building text-orange-600',
                'steps' => ['Analyze Financials', 'Calculate Income', 'Apply Rates', 'Generate Return'],
            ],
            [
                'value' => 'compliance_assessment',
                'label' => 'Compliance Assessment',
                'description' => 'Review tax status, calculate penalties, and generate action plan',
                'icon' => 'fas fa-shield-alt text-red-600',
                'steps' => ['Assess Status', 'Calculate Penalties', 'Action Plan', 'Recommendations'],
            ],
        ];

        return Inertia::render('Business/AiWorkflows/Index', [
            'workflows' => $workflows,
            'statistics' => $statistics,
            'workflowTypes' => $workflowTypes,
            'filters' => [
                'type' => $request->type,
                'status' => $request->status,
            ],
        ]);
    }

    /**
     * Show individual workflow details
     */
    public function show(Request $request, int $workflowId)
    {
        $business = $request->user()->defaultBusiness();

        $workflow = AiWorkflow::where('business_id', $business->id)
            ->with(['user', 'steps', 'reviewer'])
            ->findOrFail($workflowId);

        return Inertia::render('Business/AiWorkflows/Show', [
            'workflow' => [
                ...$workflow->toArray(),
                'summary' => $workflow->getSummary(),
                'steps' => $workflow->steps->map(fn($step) => [
                    ...$step->toArray(),
                    'summary' => $step->getSummary(),
                ]),
            ],
        ]);
    }

    /**
     * Start a new workflow
     */
    public function store(Request $request)
    {
        $business = $request->user()->defaultBusiness();

        $validated = $request->validate([
            'workflow_type' => 'required|string|in:monthly_vat,monthly_paye,monthly_wht,monthly_cit,annual_cit,compliance_assessment',
            'tax_period' => 'required_unless:workflow_type,compliance_assessment|date_format:Y-m',
            'async' => 'boolean',
        ]);

        // Extract month and year from tax_period (format: YYYY-MM)
        $month = null;
        $year = null;
        if (isset($validated['tax_period'])) {
            [$year, $month] = explode('-', $validated['tax_period']);
            $month = (int) $month;
            $year = (int) $year;
        }

        $orchestrator = new TaxAiOrchestrator($business);

        // Check if async execution is requested
        if ($validated['async'] ?? true) {
            // Queue the workflow
            ProcessTaxWorkflowJob::dispatch(
                $business,
                $validated['workflow_type'],
                [
                    'month' => $month,
                    'year' => $year,
                ],
                $request->user()->id
            );

            return redirect()->route('business.ai-workflows.index')
                ->with('success', 'Workflow queued for processing. You will be notified when complete.');
        }

        // Execute synchronously
        try {
            $workflow = match($validated['workflow_type']) {
                'monthly_vat' => $orchestrator->executeMonthlyVATWorkflow(
                    $month,
                    $year,
                    $request->user()->id
                ),
                'monthly_paye' => $orchestrator->executeMonthlyPAYEWorkflow(
                    $month,
                    $year,
                    $request->user()->id
                ),
                'monthly_wht' => $orchestrator->executeMonthlyWHTWorkflow(
                    $month,
                    $year,
                    $request->user()->id
                ),
                'compliance_assessment' => $orchestrator->executeComplianceAssessment($request->user()->id),
            };

            return redirect()->route('business.ai-workflows.show', $workflow->id)
                ->with('success', 'Workflow completed successfully');

        } catch (\Exception $e) {
            return redirect()->route('business.ai-workflows.index')
                ->with('error', 'Workflow failed: ' . $e->getMessage());
        }
    }

    /**
     * Retry a failed workflow
     */
    public function retry(Request $request, int $workflowId)
    {
        $business = $request->user()->defaultBusiness();

        $workflow = AiWorkflow::where('business_id', $business->id)
            ->findOrFail($workflowId);

        if (!$workflow->hasFailed()) {
            return response()->json([
                'success' => false,
                'error' => 'Only failed workflows can be retried',
            ], 400);
        }

        $engine = new AiWorkflowEngine($business);
        $result = $engine->retryWorkflow($workflowId);

        return redirect()->route('business.ai-workflows.show', $workflowId)
            ->with($result['success'] ? 'success' : 'error', $result['message'] ?? 'Workflow retry initiated');
    }

    /**
     * Cancel a running workflow
     */
    public function cancel(Request $request, int $workflowId)
    {
        $business = $request->user()->defaultBusiness();

        $workflow = AiWorkflow::where('business_id', $business->id)
            ->findOrFail($workflowId);

        if (!$workflow->isRunning()) {
            return response()->json([
                'success' => false,
                'error' => 'Only running workflows can be cancelled',
            ], 400);
        }

        $engine = new AiWorkflowEngine($business);
        $cancelled = $engine->cancelWorkflow($workflowId);

        return redirect()->route('business.ai-workflows.show', $workflowId)
            ->with($cancelled ? 'success' : 'error', $cancelled ? 'Workflow cancelled' : 'Failed to cancel workflow');
    }

    /**
     * Review and approve workflow
     */
    public function review(Request $request, int $workflowId)
    {
        $business = $request->user()->defaultBusiness();

        $validated = $request->validate([
            'approved' => 'required|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $workflow = AiWorkflow::where('business_id', $business->id)
            ->findOrFail($workflowId);

        if (!$workflow->requiresReview()) {
            return redirect()->route('business.ai-workflows.show', $workflowId)
                ->with('error', 'Workflow does not require review');
        }

        if ($validated['approved']) {
            $workflow->update([
                'status' => 'completed',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'completed_at' => now(),
            ]);

            // Store notes in context if provided
            if (!empty($validated['notes'])) {
                $context = $workflow->context ?? [];
                $context['review_notes'] = $validated['notes'];
                $workflow->update(['context' => $context]);
            }

            $message = 'Workflow approved and marked as completed';
        } else {
            $workflow->update([
                'status' => 'failed',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'error_message' => 'Rejected by user: ' . ($validated['notes'] ?? 'No reason provided'),
            ]);

            $message = 'Workflow rejected';
        }

        return redirect()->route('business.ai-workflows.show', $workflowId)
            ->with('success', $message);
    }

    /**
     * Delete a workflow
     */
    public function destroy(Request $request, int $workflowId)
    {
        $business = $request->user()->defaultBusiness();

        $workflow = AiWorkflow::where('business_id', $business->id)
            ->findOrFail($workflowId);

        // Only allow deletion of completed, failed, or cancelled workflows
        if (!in_array($workflow->status, ['completed', 'failed', 'cancelled'])) {
            return back()->with('error', 'Only completed, failed, or cancelled workflows can be deleted');
        }

        $reference = $workflow->reference;
        $workflow->delete();

        return redirect()->route('business.ai-workflows.index')
            ->with('success', "Workflow {$reference} deleted successfully");
    }

    /**
     * Get workflow statistics
     */
    public function statistics(Request $request)
    {
        $business = $request->user()->defaultBusiness();
        $engine = new AiWorkflowEngine($business);

        return response()->json([
            'statistics' => $engine->getWorkflowStatistics(),
        ]);
    }

    /**
     * Get available workflow types
     */
    public function types()
    {
        return response()->json([
            'types' => [
                [
                    'value' => 'monthly_vat',
                    'label' => 'Monthly VAT Return',
                    'description' => 'Calculate and generate VAT return for a specific month',
                    'requires' => ['month', 'year'],
                    'estimated_duration' => '10-15 minutes',
                ],
                [
                    'value' => 'monthly_paye',
                    'label' => 'Monthly PAYE Return',
                    'description' => 'Calculate employee taxes and generate PAYE return',
                    'requires' => ['month', 'year'],
                    'estimated_duration' => '15-20 minutes',
                ],
                [
                    'value' => 'monthly_wht',
                    'label' => 'Monthly WHT Return',
                    'description' => 'Process withholding tax transactions and generate return',
                    'requires' => ['month', 'year'],
                    'estimated_duration' => '10-15 minutes',
                ],
                [
                    'value' => 'compliance_assessment',
                    'label' => 'Compliance Assessment',
                    'description' => 'Comprehensive review of tax compliance status',
                    'requires' => [],
                    'estimated_duration' => '20-30 minutes',
                ],
            ],
        ]);
    }

    /**
     * Check if required data is available for a workflow
     */
    public function checkAvailability(Request $request)
    {
        $business = $request->user()->defaultBusiness();

        if (!$business) {
            return response()->json([
                'available' => false,
                'error' => 'No business selected',
            ], 400);
        }

        $request->validate([
            'workflow_type' => 'required|string|in:monthly_vat,monthly_paye,monthly_wht,compliance_assessment',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2100',
        ]);

        try {
            $orchestrator = new TaxAiOrchestrator($business);

            $result = $orchestrator->checkDataAvailability(
                $request->workflow_type,
                $request->month,
                $request->year
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error checking workflow data availability: ' . $e->getMessage(), [
                'business_id' => $business->id,
                'workflow_type' => $request->workflow_type,
                'exception' => $e,
            ]);

            return response()->json([
                'available' => false,
                'missing' => ['Error: ' . $e->getMessage()],
                'requirements' => [],
                'data_counts' => [],
            ], 500);
        }
    }
}
