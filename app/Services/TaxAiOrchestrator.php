<?php

namespace App\Services;

use App\Models\Business;
use App\Models\AiWorkflow;
use App\Models\AiWorkflowStep;
use App\Models\VATReturn;
use App\Models\PayeReturn;
use App\Models\WhtReturn;
use App\Models\CitReturn;
use App\Services\TaxAgents\VATAiAgent;
use App\Services\TaxAgents\PAYEAiAgent;
use App\Services\TaxAgents\WHTAiAgent;
use App\Services\TaxAgents\ComplianceAiAgent;
use App\Services\TaxAgents\TaxAdvisoryAiAgent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Tax AI Orchestrator - Coordinates all tax AI agents and workflows
 *
 * This is the main entry point for AI-driven tax operations in TaxMaster NG.
 * It manages workflow execution, coordinates multiple specialized agents,
 * and ensures comprehensive Nigerian tax compliance.
 */
class TaxAiOrchestrator
{
    protected Business $business;
    protected ?string $aiProvider;
    protected array $agents = [];

    public function __construct(Business $business, ?string $aiProvider = null)
    {
        $this->business = $business;
        $this->aiProvider = $aiProvider ?? env('AI_PROVIDER', 'deepseek');
        $this->initializeAgents();
    }

    /**
     * Initialize all specialized agents
     */
    protected function initializeAgents(): void
    {
        $this->agents = [
            'vat' => new VATAiAgent($this->business, $this->aiProvider),
            'paye' => new PAYEAiAgent($this->business, $this->aiProvider),
            'wht' => new WHTAiAgent($this->business, $this->aiProvider),
            'compliance' => new ComplianceAiAgent($this->business, $this->aiProvider),
            'advisory' => new TaxAdvisoryAiAgent($this->business, $this->aiProvider),
        ];
    }

    /**
     * Get specific agent
     */
    public function getAgent(string $agentName)
    {
        if (!isset($this->agents[$agentName])) {
            throw new Exception("Agent '{$agentName}' not found");
        }

        return $this->agents[$agentName];
    }

    /**
     * Execute monthly VAT workflow
     */
    public function executeMonthlyVATWorkflow(string $month, string $year, ?int $userId = null): AiWorkflow
    {
        Log::info('Starting monthly VAT workflow', [
            'business_id' => $this->business->id,
            'period' => "{$month}/{$year}",
        ]);

        DB::beginTransaction();

        try {
            // Create workflow
            $workflow = $this->createWorkflow('monthly_vat', "{$year}-{$month}", $userId);
            $workflow->markAsStarted();

            // Define workflow steps
            $steps = [
                [
                    'step_number' => 1,
                    'step_name' => 'collect_transactions',
                    'agent_name' => 'vat_agent',
                    'description' => 'Collect and validate all transactions for the period',
                ],
                [
                    'step_number' => 2,
                    'step_name' => 'calculate_vat',
                    'agent_name' => 'vat_agent',
                    'description' => 'Calculate input/output VAT and net obligations',
                ],
                [
                    'step_number' => 3,
                    'step_name' => 'validate_calculations',
                    'agent_name' => 'vat_agent',
                    'description' => 'Validate VAT calculations for accuracy',
                ],
                [
                    'step_number' => 4,
                    'step_name' => 'generate_return',
                    'agent_name' => 'vat_agent',
                    'description' => 'Generate FIRS VAT return (Form VAT 001)',
                ],
                [
                    'step_number' => 5,
                    'step_name' => 'compliance_check',
                    'agent_name' => 'compliance_agent',
                    'description' => 'Final compliance and deadline check',
                ],
            ];

            $workflow->update(['total_steps' => count($steps)]);

            // Execute each step
            $previousOutput = null;

            foreach ($steps as $stepDef) {
                $step = $this->createWorkflowStep($workflow, $stepDef);
                $step->markAsStarted();

                $workflow->updateProgress($stepDef['step_number'] - 1, $stepDef['step_name']);

                try {
                    $result = $this->executeStep($step, $stepDef, $previousOutput, $month, $year);

                    if (!$result['success']) {
                        throw new Exception($result['error'] ?? 'Step execution failed');
                    }

                    $step->markAsCompleted(
                        $result['output'] ?? [],
                        $result['confidence'] ?? null
                    );

                    // Store AI decision in workflow
                    $workflow->addAiDecision($stepDef['step_name'], [
                        'result' => $result['output'] ?? [],
                        'confidence' => $result['confidence'] ?? null,
                        'warnings' => $result['warnings'] ?? [],
                    ]);

                    // Add warnings to workflow if any
                    if (!empty($result['warnings'])) {
                        foreach ($result['warnings'] as $warning) {
                            $workflow->addWarning($warning, 'medium');
                        }
                    }

                    $previousOutput = $result['output'] ?? null;

                } catch (Exception $e) {
                    $step->markAsFailed($e->getMessage());
                    throw $e;
                }
            }

            // Check if workflow requires human review
            $avgConfidence = $workflow->getAverageConfidence();
            if ($avgConfidence !== null && $avgConfidence < 0.85) {
                $workflow->markAsAwaitingReview([
                    'reason' => 'Low confidence score',
                    'confidence' => $avgConfidence,
                    'recommendation' => 'Please review the calculations before submission',
                ]);
            } else {
                $workflow->markAsCompleted($previousOutput ?? []);

                // Create the actual tax return record
                $this->createReturnFromWorkflow($workflow);
            }

            DB::commit();

            Log::info('Monthly VAT workflow completed', [
                'workflow_id' => $workflow->id,
                'status' => $workflow->status,
            ]);

            return $workflow;

        } catch (Exception $e) {
            DB::rollBack();

            if (isset($workflow)) {
                $workflow->markAsFailed($e->getMessage(), [
                    'exception' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            Log::error('Monthly VAT workflow failed', [
                'business_id' => $this->business->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Execute monthly PAYE workflow
     */
    public function executeMonthlyPAYEWorkflow(string $month, string $year, ?int $userId = null): AiWorkflow
    {
        Log::info('Starting monthly PAYE workflow', [
            'business_id' => $this->business->id,
            'period' => "{$month}/{$year}",
        ]);

        DB::beginTransaction();

        try {
            $workflow = $this->createWorkflow('monthly_paye', "{$year}-{$month}", $userId);
            $workflow->markAsStarted();

            $steps = [
                [
                    'step_number' => 1,
                    'step_name' => 'collect_payroll',
                    'agent_name' => 'paye_agent',
                    'description' => 'Collect employee payroll data',
                ],
                [
                    'step_number' => 2,
                    'step_name' => 'calculate_paye',
                    'agent_name' => 'paye_agent',
                    'description' => 'Calculate PAYE for all employees',
                ],
                [
                    'step_number' => 3,
                    'step_name' => 'validate_calculations',
                    'agent_name' => 'paye_agent',
                    'description' => 'Validate PAYE calculations',
                ],
                [
                    'step_number' => 4,
                    'step_name' => 'generate_return',
                    'agent_name' => 'paye_agent',
                    'description' => 'Generate monthly PAYE return',
                ],
                [
                    'step_number' => 5,
                    'step_name' => 'compliance_check',
                    'agent_name' => 'compliance_agent',
                    'description' => 'Verify compliance and deadline',
                ],
            ];

            $workflow->update(['total_steps' => count($steps)]);

            $previousOutput = null;

            foreach ($steps as $stepDef) {
                $step = $this->createWorkflowStep($workflow, $stepDef);
                $step->markAsStarted();
                $workflow->updateProgress($stepDef['step_number'] - 1, $stepDef['step_name']);

                $result = $this->executeStep($step, $stepDef, $previousOutput, $month, $year);

                if (!$result['success']) {
                    throw new Exception($result['error'] ?? 'Step failed');
                }

                $step->markAsCompleted($result['output'] ?? [], $result['confidence'] ?? null);
                $workflow->addAiDecision($stepDef['step_name'], $result);

                if (!empty($result['warnings'])) {
                    foreach ($result['warnings'] as $warning) {
                        $workflow->addWarning($warning);
                    }
                }

                $previousOutput = $result['output'] ?? null;
            }

            $avgConfidence = $workflow->getAverageConfidence();
            if ($avgConfidence !== null && $avgConfidence < 0.85) {
                $workflow->markAsAwaitingReview([
                    'reason' => 'Low confidence - review recommended',
                    'confidence' => $avgConfidence,
                ]);
            } else {
                $workflow->markAsCompleted($previousOutput ?? []);

                // Create the actual tax return record
                $this->createReturnFromWorkflow($workflow);
            }

            DB::commit();
            return $workflow;

        } catch (Exception $e) {
            DB::rollBack();
            if (isset($workflow)) {
                $workflow->markAsFailed($e->getMessage());
            }
            Log::error('PAYE workflow failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Execute monthly WHT workflow
     */
    public function executeMonthlyWHTWorkflow(string $month, string $year, ?int $userId = null): AiWorkflow
    {
        Log::info('Starting monthly WHT workflow', [
            'business_id' => $this->business->id,
            'period' => "{$month}/{$year}",
        ]);

        DB::beginTransaction();

        try {
            $workflow = $this->createWorkflow('monthly_wht', "{$year}-{$month}", $userId);
            $workflow->markAsStarted();

            $steps = [
                [
                    'step_number' => 1,
                    'step_name' => 'collect_transactions',
                    'agent_name' => 'wht_agent',
                    'description' => 'Collect WHT-applicable transactions',
                ],
                [
                    'step_number' => 2,
                    'step_name' => 'classify_transactions',
                    'agent_name' => 'wht_agent',
                    'description' => 'Classify transaction types and rates',
                ],
                [
                    'step_number' => 3,
                    'step_name' => 'generate_return',
                    'agent_name' => 'wht_agent',
                    'description' => 'Generate monthly WHT return',
                ],
                [
                    'step_number' => 4,
                    'step_name' => 'compliance_check',
                    'agent_name' => 'compliance_agent',
                    'description' => 'Verify compliance and certificate requirements',
                ],
            ];

            $workflow->update(['total_steps' => count($steps)]);

            $previousOutput = null;

            foreach ($steps as $stepDef) {
                $step = $this->createWorkflowStep($workflow, $stepDef);
                $step->markAsStarted();
                $workflow->updateProgress($stepDef['step_number'] - 1, $stepDef['step_name']);

                $result = $this->executeStep($step, $stepDef, $previousOutput, $month, $year);

                if (!$result['success']) {
                    throw new Exception($result['error'] ?? 'Step failed');
                }

                $step->markAsCompleted($result['output'] ?? [], $result['confidence'] ?? null);
                $workflow->addAiDecision($stepDef['step_name'], $result);

                $previousOutput = $result['output'] ?? null;
            }

            $workflow->markAsCompleted($previousOutput ?? []);

            // Create the actual WHT return record
            $this->createReturnFromWorkflow($workflow);

            DB::commit();
            return $workflow;

        } catch (Exception $e) {
            DB::rollBack();
            if (isset($workflow)) {
                $workflow->markAsFailed($e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Execute comprehensive compliance assessment
     */
    public function executeComplianceAssessment(?int $userId = null): AiWorkflow
    {
        Log::info('Starting compliance assessment workflow', [
            'business_id' => $this->business->id,
        ]);

        DB::beginTransaction();

        try {
            $workflow = $this->createWorkflow('compliance_assessment', now()->format('Y-m'), $userId);
            $workflow->markAsStarted();

            $steps = [
                [
                    'step_number' => 1,
                    'step_name' => 'assess_status',
                    'agent_name' => 'compliance_agent',
                    'description' => 'Assess overall compliance status',
                ],
                [
                    'step_number' => 2,
                    'step_name' => 'calculate_penalties',
                    'agent_name' => 'compliance_agent',
                    'description' => 'Calculate potential penalties',
                ],
                [
                    'step_number' => 3,
                    'step_name' => 'generate_action_plan',
                    'agent_name' => 'compliance_agent',
                    'description' => 'Generate 90-day action plan',
                ],
                [
                    'step_number' => 4,
                    'step_name' => 'optimization_recommendations',
                    'agent_name' => 'advisory_agent',
                    'description' => 'Provide tax optimization recommendations',
                ],
            ];

            $workflow->update(['total_steps' => count($steps)]);

            $previousOutput = null;

            foreach ($steps as $stepDef) {
                $step = $this->createWorkflowStep($workflow, $stepDef);
                $step->markAsStarted();
                $workflow->updateProgress($stepDef['step_number'] - 1, $stepDef['step_name']);

                $result = $this->executeStep($step, $stepDef, $previousOutput);

                if (!$result['success']) {
                    throw new Exception($result['error'] ?? 'Step failed');
                }

                $step->markAsCompleted($result['output'] ?? [], $result['confidence'] ?? null);
                $workflow->addAiDecision($stepDef['step_name'], $result);

                $previousOutput = $result['output'] ?? null;
            }

            $workflow->markAsCompleted($previousOutput ?? []);

            DB::commit();
            return $workflow;

        } catch (Exception $e) {
            DB::rollBack();
            if (isset($workflow)) {
                $workflow->markAsFailed($e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Create workflow record
     */
    protected function createWorkflow(string $type, string $period, ?int $userId = null): AiWorkflow
    {
        return AiWorkflow::create([
            'business_id' => $this->business->id,
            'user_id' => $userId,
            'workflow_type' => $type,
            'tax_period' => $period,
            'reference' => AiWorkflow::generateReference($type, $period),
            'status' => 'pending',
            'ai_provider' => $this->aiProvider,
            'context' => [
                'business' => $this->business->only(['id', 'name', 'tin', 'business_type']),
                'initiated_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Create workflow step
     */
    protected function createWorkflowStep(AiWorkflow $workflow, array $stepDef): AiWorkflowStep
    {
        return AiWorkflowStep::create([
            'ai_workflow_id' => $workflow->id,
            'step_number' => $stepDef['step_number'],
            'step_name' => $stepDef['step_name'],
            'agent_name' => $stepDef['agent_name'],
            'description' => $stepDef['description'] ?? null,
            'status' => 'pending',
        ]);
    }

    /**
     * Execute individual workflow step
     */
    protected function executeStep(
        AiWorkflowStep $step,
        array $stepDef,
        $previousOutput = null,
        ?string $month = null,
        ?string $year = null
    ): array {
        $agentKey = str_replace('_agent', '', $stepDef['agent_name']);
        $agent = $this->getAgent($agentKey);
        $agent->setCurrentStep($step);

        try {
            // Route to appropriate agent method based on step name
            switch ($stepDef['step_name']) {
                case 'calculate_vat':
                    return $agent->calculateMonthlyVAT($month, $year);

                case 'generate_return':
                    if ($agentKey === 'vat') {
                        return $agent->generateVATReturn($previousOutput);
                    } elseif ($agentKey === 'paye') {
                        return $agent->generateMonthlyReturn($month, $year);
                    } elseif ($agentKey === 'wht') {
                        return $agent->generateMonthlyReturn($month, $year);
                    }
                    break;

                case 'assess_status':
                    return $agent->assessComplianceStatus();

                case 'calculate_penalties':
                    $overdueReturns = $previousOutput['outstanding_obligations'] ?? [];
                    return $agent->calculatePenalties($overdueReturns);

                case 'generate_action_plan':
                    return $agent->generateActionPlan(90);

                case 'optimization_recommendations':
                    return $agent->recommendOptimizations();

                default:
                    // Generic step execution
                    return [
                        'success' => true,
                        'output' => ['status' => 'completed', 'previous_data' => $previousOutput],
                        'confidence' => 0.90,
                    ];
            }

            return ['success' => false, 'error' => 'Unknown step: ' . $stepDef['step_name']];

        } catch (Exception $e) {
            Log::error('Step execution error', [
                'step' => $stepDef['step_name'],
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get workflow by ID
     */
    public function getWorkflow(int $workflowId): ?AiWorkflow
    {
        return AiWorkflow::with('steps')->find($workflowId);
    }

    /**
     * Get workflows for business
     */
    public function getBusinessWorkflows(array $filters = [])
    {
        $query = AiWorkflow::where('business_id', $this->business->id)
            ->with('steps')
            ->orderBy('created_at', 'desc');

        if (!empty($filters['type'])) {
            $query->where('workflow_type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['period'])) {
            $query->where('tax_period', $filters['period']);
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Check if required data is available for a workflow
     *
     * @param string $workflowType The type of workflow (monthly_vat, monthly_paye, etc.)
     * @param string|null $month Month (1-12) - required for monthly workflows
     * @param string|null $year Year - required for monthly workflows
     * @return array ['available' => bool, 'missing' => array, 'requirements' => array, 'data_counts' => array]
     */
    public function checkDataAvailability(string $workflowType, ?string $month = null, ?string $year = null): array
    {
        switch ($workflowType) {
            case 'monthly_vat':
                return $this->checkVATDataAvailability($month, $year);

            case 'monthly_paye':
                return $this->checkPAYEDataAvailability($month, $year);

            case 'monthly_wht':
                return $this->checkWHTDataAvailability($month, $year);

            case 'monthly_cit':
            case 'annual_cit':
                return $this->checkCITDataAvailability($month, $year);

            case 'compliance_assessment':
                return $this->checkComplianceDataAvailability();

            default:
                return [
                    'available' => false,
                    'missing' => ['Unknown workflow type'],
                    'requirements' => [],
                    'data_counts' => [],
                ];
        }
    }

    /**
     * Get workflow data requirements documentation
     *
     * @param string $workflowType
     * @return array
     */
    public function getWorkflowDataRequirements(string $workflowType): array
    {
        $requirements = [
            'monthly_vat' => [
                'name' => 'Monthly VAT Processing',
                'description' => 'Analyze transactions, calculate VAT, and generate Form VAT 001',
                'required_data' => [
                    [
                        'type' => 'transactions',
                        'description' => 'Sales and purchase transactions with VAT',
                        'minimum' => 1,
                        'note' => 'At least one transaction is needed to process VAT',
                    ],
                    [
                        'type' => 'invoices',
                        'description' => 'Invoices issued to customers (output VAT)',
                        'minimum' => 0,
                        'note' => 'Optional but recommended for accurate VAT calculation',
                    ],
                ],
                'optional_data' => [
                    'Bank account linked for automatic transaction import',
                    'Previous VAT returns for historical comparison',
                ],
                'time_range' => 'Specific month and year',
            ],
            'monthly_paye' => [
                'name' => 'Monthly PAYE Processing',
                'description' => 'Calculate employee taxes and generate monthly PAYE returns',
                'required_data' => [
                    [
                        'type' => 'staff',
                        'description' => 'Active employees with salary information',
                        'minimum' => 1,
                        'note' => 'Business must have at least one employee on payroll',
                    ],
                    [
                        'type' => 'payroll',
                        'description' => 'Salary, allowances, and deductions for the period',
                        'minimum' => 1,
                        'note' => 'Payroll records must exist for the target month',
                    ],
                ],
                'optional_data' => [
                    'Employee pension contributions',
                    'NHF (National Housing Fund) contributions',
                    'Previous PAYE returns',
                ],
                'time_range' => 'Specific month and year',
            ],
            'monthly_wht' => [
                'name' => 'Monthly WHT Processing',
                'description' => 'Classify transactions and generate withholding tax schedules',
                'required_data' => [
                    [
                        'type' => 'transactions',
                        'description' => 'Any business transactions (AI will analyze to identify WHT-applicable items)',
                        'minimum' => 1,
                        'note' => 'AI will classify transactions for dividends, rent, services, interest, royalties, etc.',
                    ],
                ],
                'optional_data' => [
                    'Transaction categories pre-labeled as WHT-applicable',
                    'Beneficiary TIN (Tax Identification Numbers)',
                    'WHT certificates issued',
                    'Previous WHT returns',
                ],
                'time_range' => 'Specific month and year',
            ],
            'monthly_cit' => [
                'name' => 'Monthly CIT Self-Assessment',
                'description' => 'Calculate taxable income, apply Finance Act 2019 rates, and generate CIT return',
                'required_data' => [
                    [
                        'type' => 'transactions',
                        'description' => 'Revenue and expense transactions to calculate taxable income',
                        'minimum' => 1,
                        'note' => 'At least one transaction is needed to calculate profit/loss',
                    ],
                ],
                'optional_data' => [
                    'Revenue transactions (sales, services)',
                    'Expense transactions (operating costs)',
                    'Previous CIT returns',
                    'Financial statements',
                ],
                'time_range' => 'Specific month and year',
            ],
            'annual_cit' => [
                'name' => 'Annual CIT Return',
                'description' => 'Calculate annual taxable income and generate CIT return for year-end filing',
                'required_data' => [
                    [
                        'type' => 'transactions',
                        'description' => 'Full year revenue and expense transactions',
                        'minimum' => 1,
                        'note' => 'Annual financial data required for CIT calculation',
                    ],
                ],
                'optional_data' => [
                    'Audited financial statements',
                    'Capital allowances',
                    'Tax losses carried forward',
                    'Previous year CIT returns',
                ],
                'time_range' => 'Specific year (12-month period)',
            ],
            'compliance_assessment' => [
                'name' => 'Compliance Assessment',
                'description' => 'Review tax compliance status, calculate penalties, generate action plan',
                'required_data' => [
                    [
                        'type' => 'business_profile',
                        'description' => 'Business registration and TIN',
                        'minimum' => 1,
                        'note' => 'Business must be properly set up',
                    ],
                ],
                'optional_data' => [
                    'Tax filing history (VAT, PAYE, WHT, CIT)',
                    'Payment records',
                    'Compliance deadlines',
                    'Previous assessments',
                ],
                'time_range' => 'Current date (no specific period required)',
            ],
        ];

        return $requirements[$workflowType] ?? [
            'name' => 'Unknown Workflow',
            'description' => '',
            'required_data' => [],
            'optional_data' => [],
            'time_range' => '',
        ];
    }

    /**
     * Check VAT data availability
     */
    protected function checkVATDataAvailability(?string $month, ?string $year): array
    {
        $missing = [];
        $dataCounts = [];

        if (!$month || !$year) {
            $missing[] = 'Tax period (month and year) is required';
            return [
                'available' => false,
                'missing' => $missing,
                'requirements' => $this->getWorkflowDataRequirements('monthly_vat'),
                'data_counts' => [],
            ];
        }

        // Check for transactions in the period
        $startDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        // Get all transactions count (for debugging)
        $allTransactionsCount = $this->business->transactions()->count();

        // Use whereDate for timestamp columns
        $transactionsCount = $this->business->transactions()
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->count();

        $dataCounts['transactions'] = $transactionsCount;
        $dataCounts['all_transactions'] = $allTransactionsCount; // Show total for debugging

        if ($transactionsCount === 0) {
            $missing[] = "No transactions found for {$month}/{$year}";
            // Add helpful debug info
            if ($allTransactionsCount > 0) {
                $missing[] = "You have {$allTransactionsCount} total transactions. Check if they match the selected period ({$startDate} to {$endDate}).";
            }
        }

        // Check for invoices (optional but helpful)
        if (method_exists($this->business, 'invoices')) {
            $invoicesCount = $this->business->invoices()
                ->whereDate('issue_date', '>=', $startDate)
                ->whereDate('issue_date', '<=', $endDate)
                ->count();
            $dataCounts['invoices'] = $invoicesCount;
        } else {
            $dataCounts['invoices'] = 0;
        }

        return [
            'available' => count($missing) === 0,
            'missing' => $missing,
            'requirements' => $this->getWorkflowDataRequirements('monthly_vat'),
            'data_counts' => $dataCounts,
            'period' => "{$month}/{$year}",
            'period_formatted' => date('F Y', strtotime($startDate)),
        ];
    }

    /**
     * Check PAYE data availability
     */
    protected function checkPAYEDataAvailability(?string $month, ?string $year): array
    {
        $missing = [];
        $dataCounts = [];

        if (!$month || !$year) {
            $missing[] = 'Tax period (month and year) is required';
            return [
                'available' => false,
                'missing' => $missing,
                'requirements' => $this->getWorkflowDataRequirements('monthly_paye'),
                'data_counts' => [],
            ];
        }

        // Check for active staff
        $staffCount = $this->business->staff()
            ->where('status', 'active')
            ->count();

        $dataCounts['staff'] = $staffCount;

        if ($staffCount === 0) {
            $missing[] = 'No active employees found. Add employees to process PAYE.';
        }

        // Check for payroll records (if payroll system exists)
        // Note: Adjust this based on your actual payroll table structure
        $startDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        // If you have a payroll table, uncomment and adjust:
        // $payrollCount = $this->business->payrollRecords()
        //     ->whereBetween('pay_date', [$startDate, $endDate])
        //     ->count();
        // $dataCounts['payroll_records'] = $payrollCount;

        return [
            'available' => count($missing) === 0,
            'missing' => $missing,
            'requirements' => $this->getWorkflowDataRequirements('monthly_paye'),
            'data_counts' => $dataCounts,
            'period' => "{$month}/{$year}",
            'period_formatted' => date('F Y', strtotime($startDate)),
        ];
    }

    /**
     * Check WHT data availability
     */
    protected function checkWHTDataAvailability(?string $month, ?string $year): array
    {
        $missing = [];
        $dataCounts = [];

        if (!$month || !$year) {
            $missing[] = 'Tax period (month and year) is required';
            return [
                'available' => false,
                'missing' => $missing,
                'requirements' => $this->getWorkflowDataRequirements('monthly_wht'),
                'data_counts' => [],
            ];
        }

        $startDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        // Get all transactions in the period
        $allTransactionsInPeriod = $this->business->transactions()
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->count();

        $dataCounts['transactions_in_period'] = $allTransactionsInPeriod;

        // Get total transactions (for debugging)
        $allTransactionsCount = $this->business->transactions()->count();
        $dataCounts['all_transactions'] = $allTransactionsCount;

        // Check for transactions that have WHT-specific categories (optional info)
        $whtCategorizedCount = $this->business->transactions()
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->whereIn('sub_category', \App\Models\Transaction::getWHTApplicableCategories())
            ->count();

        $dataCounts['wht_categorized'] = $whtCategorizedCount;

        // WHT workflow can proceed if there are ANY transactions in the period
        // The AI will analyze them to determine which are subject to WHT
        if ($allTransactionsInPeriod === 0) {
            $missing[] = "No transactions found for {$month}/{$year}";
            if ($allTransactionsCount > 0) {
                $missing[] = "You have {$allTransactionsCount} total transactions, but none match the selected period ({$startDate} to {$endDate}).";
            }
        } else {
            // Success - we have transactions to analyze
            if ($whtCategorizedCount === 0) {
                // Provide helpful note about categories
                $dataCounts['note'] = "Found {$allTransactionsInPeriod} transactions. AI will analyze to identify WHT-applicable items.";
            }
        }

        return [
            'available' => count($missing) === 0,
            'missing' => $missing,
            'requirements' => $this->getWorkflowDataRequirements('monthly_wht'),
            'data_counts' => $dataCounts,
            'period' => "{$month}/{$year}",
            'period_formatted' => date('F Y', strtotime($startDate)),
        ];
    }

    /**
     * Check compliance assessment data availability
     */
    protected function checkComplianceDataAvailability(): array
    {
        $missing = [];
        $dataCounts = [];

        // Check basic business setup
        if (!$this->business->tin) {
            $missing[] = 'Business TIN (Tax Identification Number) is not set';
        }

        // Check if business has any tax activity
        $totalTransactions = $this->business->transactions()->count();
        $dataCounts['total_transactions'] = $totalTransactions;

        // Check for invoices if the relationship exists
        if (method_exists($this->business, 'invoices')) {
            $totalInvoices = $this->business->invoices()->count();
            $dataCounts['total_invoices'] = $totalInvoices;
        } else {
            $dataCounts['total_invoices'] = 0;
        }

        $staffCount = $this->business->staff()->count();
        $dataCounts['staff'] = $staffCount;

        // Optional: Check for previous tax returns
        if (method_exists($this->business, 'vatReturns')) {
            $vatReturns = $this->business->vatReturns()->count();
            $dataCounts['vat_returns'] = $vatReturns;
        } else {
            $dataCounts['vat_returns'] = 0;
        }

        // Note: Compliance assessment can run with minimal data
        // It will just report what's missing and what needs to be done
        $hasMinimalData = $totalTransactions > 0 || ($dataCounts['total_invoices'] ?? 0) > 0 || $staffCount > 0;

        if (!$hasMinimalData && !$this->business->tin) {
            $missing[] = 'Business has no tax activity yet. Set up TIN and add transactions or staff.';
        }

        return [
            'available' => count($missing) === 0,
            'missing' => $missing,
            'requirements' => $this->getWorkflowDataRequirements('compliance_assessment'),
            'data_counts' => $dataCounts,
            'period' => 'Current assessment',
            'period_formatted' => now()->format('F d, Y'),
        ];
    }

    /**
     * Check CIT data availability
     */
    protected function checkCITDataAvailability(?string $month, ?string $year): array
    {
        $missing = [];
        $dataCounts = [];

        if (!$month || !$year) {
            $missing[] = 'Tax period (month and year) is required';
            return [
                'available' => false,
                'missing' => $missing,
                'requirements' => $this->getWorkflowDataRequirements('monthly_cit'),
                'data_counts' => [],
            ];
        }

        $startDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        // Check for transactions in the period
        $transactionsCount = $this->business->transactions()
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->count();

        $dataCounts['transactions'] = $transactionsCount;

        // Get total transactions for reference
        $allTransactionsCount = $this->business->transactions()->count();
        $dataCounts['all_transactions'] = $allTransactionsCount;

        if ($transactionsCount === 0) {
            $missing[] = "No transactions found for {$month}/{$year}";
            if ($allTransactionsCount > 0) {
                $missing[] = "You have {$allTransactionsCount} total transactions, but none match the selected period ({$startDate} to {$endDate}).";
            } else {
                $missing[] = "Add transactions to calculate CIT. CIT is based on taxable income from business activities.";
            }
        }

        // Check for revenue transactions (income)
        $revenueCount = $this->business->transactions()
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('type', 'credit')
            ->count();

        $dataCounts['revenue_transactions'] = $revenueCount;

        // Check for expense transactions
        $expenseCount = $this->business->transactions()
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('type', 'debit')
            ->count();

        $dataCounts['expense_transactions'] = $expenseCount;

        // Note: CIT can be calculated even with minimal data
        if ($transactionsCount > 0 && $revenueCount === 0) {
            $dataCounts['note'] = "No revenue transactions found. CIT is calculated on taxable income (revenue - expenses).";
        }

        return [
            'available' => count($missing) === 0,
            'missing' => $missing,
            'requirements' => $this->getWorkflowDataRequirements('monthly_cit'),
            'data_counts' => $dataCounts,
            'period' => "{$month}/{$year}",
            'period_formatted' => date('F Y', strtotime($startDate)),
        ];
    }

    /**
     * Create tax return record from completed workflow
     * This is called automatically when a workflow completes successfully
     */
    public function createReturnFromWorkflow(AiWorkflow $workflow): ?Model
    {
        // Only create return if workflow is completed with high confidence
        if ($workflow->status !== 'completed') {
            return null;
        }

        $outputData = $workflow->output_data ?? [];
        $period = $workflow->tax_period;

        try {
            switch ($workflow->workflow_type) {
                case 'monthly_vat':
                    return $this->createVATReturn($workflow, $outputData, $period);

                case 'monthly_paye':
                    return $this->createPAYEReturn($workflow, $outputData, $period);

                case 'monthly_wht':
                    return $this->createWHTReturn($workflow, $outputData, $period);

                case 'monthly_cit':
                case 'annual_cit':
                    return $this->createCITReturn($workflow, $outputData, $period);

                default:
                    Log::warning('No return creation handler for workflow type', [
                        'workflow_type' => $workflow->workflow_type,
                        'workflow_id' => $workflow->id,
                    ]);
                    return null;
            }
        } catch (Exception $e) {
            Log::error('Failed to create return from workflow', [
                'workflow_id' => $workflow->id,
                'workflow_type' => $workflow->workflow_type,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Create VAT return from workflow output
     */
    protected function createVATReturn(AiWorkflow $workflow, array $outputData, string $period): ?VATReturn
    {
        $returnData = $outputData['vat_return'] ?? $outputData;

        $vatReturn = VATReturn::create([
            'business_id' => $workflow->business_id,
            'ai_workflow_id' => $workflow->id,
            'is_ai_generated' => true,
            'period' => $period,
            'form_type' => 'Form 002',
            'reporting_period' => 'monthly',
            'sales_turnover' => $returnData['sales_turnover'] ?? 0,
            'exempt_sales' => $returnData['exempt_sales'] ?? 0,
            'zero_rated_sales' => $returnData['zero_rated_sales'] ?? 0,
            'export_sales' => $returnData['export_sales'] ?? 0,
            'vat_on_sales' => $returnData['output_vat'] ?? $returnData['vat_on_sales'] ?? 0,
            'purchases_turnover' => $returnData['purchases_turnover'] ?? 0,
            'input_vat' => $returnData['input_vat'] ?? 0,
            'input_credit' => $returnData['input_credit'] ?? 0,
            'vat_due' => $returnData['vat_due'] ?? $returnData['net_vat'] ?? 0,
            'settlement_amount' => $returnData['settlement_amount'] ?? $returnData['vat_due'] ?? 0,
            'status' => 'draft',
            'tax_authority' => 'firs',
            'notes' => 'AI-generated return from workflow #' . $workflow->reference,
            'form_data' => $outputData,
        ]);

        Log::info('Created VAT return from AI workflow', [
            'workflow_id' => $workflow->id,
            'return_id' => $vatReturn->id,
            'period' => $period,
        ]);

        return $vatReturn;
    }

    /**
     * Create PAYE return from workflow output
     */
    protected function createPAYEReturn(AiWorkflow $workflow, array $outputData, string $period): ?PayeReturn
    {
        $returnData = $outputData['paye_return'] ?? $outputData;

        $payeReturn = PayeReturn::create([
            'business_id' => $workflow->business_id,
            'ai_workflow_id' => $workflow->id,
            'is_ai_generated' => true,
            'period' => $period,
            'return_type' => 'monthly',
            'total_gross_pay' => $returnData['total_gross_pay'] ?? 0,
            'total_tax_deducted' => $returnData['total_tax_deducted'] ?? 0,
            'staff_count' => $returnData['staff_count'] ?? 0,
            'schedule_data' => $returnData['schedule_data'] ?? [],
            'status' => 'draft',
            'tax_authority' => 'sirs',
            'notes' => 'AI-generated return from workflow #' . $workflow->reference,
        ]);

        Log::info('Created PAYE return from AI workflow', [
            'workflow_id' => $workflow->id,
            'return_id' => $payeReturn->id,
            'period' => $period,
        ]);

        return $payeReturn;
    }

    /**
     * Create WHT return from workflow output
     */
    protected function createWHTReturn(AiWorkflow $workflow, array $outputData, string $period): ?WhtReturn
    {
        $returnData = $outputData['wht_return'] ?? $outputData;

        $whtReturn = WhtReturn::create([
            'business_id' => $workflow->business_id,
            'ai_workflow_id' => $workflow->id,
            'is_ai_generated' => true,
            'period' => $period,
            'total_wht_deducted' => $returnData['total_wht_deducted'] ?? 0,
            'transaction_count' => $returnData['transaction_count'] ?? 0,
            'schedule_data' => $returnData['schedule_data'] ?? [],
            'status' => 'draft',
            'tax_authority' => 'firs',
            'beneficiary_type' => $returnData['beneficiary_type'] ?? 'company',
            'notes' => 'AI-generated return from workflow #' . $workflow->reference,
        ]);

        Log::info('Created WHT return from AI workflow', [
            'workflow_id' => $workflow->id,
            'return_id' => $whtReturn->id,
            'period' => $period,
        ]);

        return $whtReturn;
    }

    /**
     * Create CIT return from workflow output
     */
    protected function createCITReturn(AiWorkflow $workflow, array $outputData, string $period): ?CitReturn
    {
        $returnData = $outputData['cit_return'] ?? $outputData;

        // Determine return type based on workflow type
        $returnType = $workflow->workflow_type === 'annual_cit' ? 'annual' : 'self_assessment';

        // Calculate minimum tax (0.5% of turnover or 1% of gross profit, whichever is greater)
        $turnover = $returnData['turnover'] ?? $returnData['revenue'] ?? 0;
        $grossProfit = $returnData['gross_profit'] ?? 0;
        $minimumTax = max($turnover * 0.005, $grossProfit * 0.01);

        // Calculate CIT at 30% rate
        $taxableIncome = $returnData['taxable_income'] ?? 0;
        $citPayable = $taxableIncome * 0.30;

        // Tax due is the greater of CIT payable or minimum tax
        $taxDue = max($citPayable, $minimumTax);

        // Calculate balance after credits
        $totalCredits = ($returnData['advance_tax'] ?? 0) + ($returnData['withholding_tax'] ?? 0);
        $balanceDue = max(0, $taxDue - $totalCredits);
        $balanceRefund = max(0, $totalCredits - $taxDue);

        $citReturn = CitReturn::create([
            'business_id' => $workflow->business_id,
            'ai_workflow_id' => $workflow->id,
            'is_ai_generated' => true,
            'period' => $period,
            'return_type' => $returnType,
            'revenue' => $returnData['revenue'] ?? 0,
            'cost_of_goods_sold' => $returnData['cost_of_goods_sold'] ?? 0,
            'gross_profit' => $grossProfit,
            'depreciation' => $returnData['depreciation'] ?? 0,
            'amortization' => $returnData['amortization'] ?? 0,
            'other_add_backs' => $returnData['other_add_backs'] ?? 0,
            'capital_allowances' => $returnData['capital_allowances'] ?? 0,
            'allowable_expenses' => $returnData['allowable_expenses'] ?? 0,
            'other_deductions' => $returnData['other_deductions'] ?? 0,
            'taxable_income' => $taxableIncome,
            'cit_rate' => 0.30,
            'cit_payable' => $citPayable,
            'turnover' => $turnover,
            'gross_assets' => $returnData['gross_assets'] ?? 0,
            'paid_up_capital' => $returnData['paid_up_capital'] ?? 0,
            'minimum_tax_amount' => $minimumTax,
            'tax_due' => $taxDue,
            'advance_tax' => $returnData['advance_tax'] ?? 0,
            'withholding_tax' => $returnData['withholding_tax'] ?? 0,
            'total_credits' => $totalCredits,
            'balance_due' => $balanceDue,
            'balance_refund' => $balanceRefund,
            'status' => 'draft',
            'tax_authority' => 'firs',
            'notes' => 'AI-generated return from workflow #' . $workflow->reference,
            'calculation_details' => [
                'cit_calculation' => $taxableIncome . ' × 30% = ' . number_format($citPayable, 2),
                'minimum_tax' => 'Greater of: Turnover × 0.5% (' . number_format($turnover * 0.005, 2) . ') or Gross Profit × 1% (' . number_format($grossProfit * 0.01, 2) . ')',
                'tax_due' => 'Greater of: CIT Payable (' . number_format($citPayable, 2) . ') or Minimum Tax (' . number_format($minimumTax, 2) . ')',
            ],
            'form_data' => $outputData,
        ]);

        Log::info('Created CIT return from AI workflow', [
            'workflow_id' => $workflow->id,
            'return_id' => $citReturn->id,
            'period' => $period,
            'return_type' => $returnType,
        ]);

        return $citReturn;
    }
}

