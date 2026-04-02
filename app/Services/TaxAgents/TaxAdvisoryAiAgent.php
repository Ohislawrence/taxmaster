<?php

namespace App\Services\TaxAgents;

/**
 * Specialized AI Agent for Tax Advisory and Q&A in Nigeria
 * 
 * Handles:
 * - Tax law questions and interpretations
 * - Strategy recommendations
 * - Tax optimization suggestions
 * - Scenario analysis
 * - Industry-specific tax advice
 */
class TaxAdvisoryAiAgent extends BaseTaxAgent
{
    public function getName(): string
    {
        return 'Tax Advisory Agent';
    }

    public function getDescription(): string
    {
        return 'Nigerian tax advisory specialist - answers tax questions, provides strategic recommendations, and offers compliance guidance';
    }

    protected function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert Nigerian tax advisor AI agent with deep knowledge of:
- Nigerian tax laws (FIRS regulations, Tax Acts)
- VAT Act, Companies Income Tax Act (CITA), Personal Income Tax Act (PITA)
- Finance Acts (latest amendments)
- FIRS guidelines and circulars
- Tax planning strategies
- Industry-specific tax treatments

ADVISORY PRINCIPLES:
- Always cite relevant tax laws and sections
- Provide practical, actionable advice
- Consider business context and size
- Highlight compliance requirements
- Warn about potential risks
- Suggest tax-efficient approaches within legal bounds

DISCLAIMERS:
- Emphasize when professional tax consultant should be engaged
- Note when ruling from FIRS may be needed
- Indicate areas of tax law uncertainty

Respond in clear, professional language with specific Nigerian examples.
PROMPT;
    }

    /**
     * Answer tax question
     */
    public function answerQuestion(string $question, ?array $context = null): array
    {
        $this->logActivity('answer_question', ['question' => substr($question, 0, 100)]);

        $contextInfo = $context ? $this->formatForJson($context) : "No additional context provided";

        $prompt = <<<PROMPT
Answer this Nigerian tax question with expert advice:

Question: {$question}

Business Context:
{$this->formatBusinessContext()}

Additional Context:
{$contextInfo}

Provide:
1. Direct answer to the question
2. Relevant Nigerian tax law references
3. Practical examples
4. Action steps
5. Potential risks or considerations
6. When to seek additional professional help

Respond in this JSON format:
{
  "question_summary": "Brief restatement of question",
  "answer": "Comprehensive answer with specific Nigerian tax guidance",
  "legal_references": [
    {
      "law": "VAT Act 2020",
      "section": "Section 2",
      "relevance": "Defines VAT taxable persons"
    }
  ],
  "practical_examples": [
    "If you export software services to UK clients, this is VAT zero-rated..."
  ],
  "action_steps": [
    {
      "step": 1,
      "action": "Register for VAT if turnover exceeds ₦25M",
      "priority": "high",
      "timeline": "Within 6 months of crossing threshold"
    }
  ],
  "risks_to_consider": [
    "Failure to register for VAT when required attracts penalties"
  ],
  "when_to_consult_professional": "If annual turnover exceeds ₦50M or complex international transactions",
  "related_topics": [
    "E-commerce VAT treatment",
    "Digital services taxation"
  ],
  "confidence": 0.95,
  "disclaimer": "This is general guidance. Specific situations may require professional review."
}
PROMPT;

        $result = $this->callAi($prompt, ['context' => $context]);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Question response failed',
            ];
        }

        $answer = $this->parseJsonResponse($result['response']['content']);

        if ($this->currentStep) {
            $this->currentStep->update([
                'parsed_response' => $answer,
                'confidence_score' => $answer['confidence'] ?? null,
                'tokens_used' => $result['response']['tokens'] ?? 0,
            ]);
        }

        return [
            'success' => true,
            'answer' => $answer,
            'tokens_used' => $result['response']['tokens'] ?? 0,
        ];
    }

    /**
     * Provide tax optimization recommendations
     */
    public function recommendOptimizations(): array
    {
        $this->logActivity('recommend_optimizations');

        $prompt = <<<PROMPT
Analyze this Nigerian business and provide tax optimization recommendations:

Business Context:
{$this->formatBusinessContext()}

Analyze:
1. Current tax position
2. Potential deductions not being claimed
3. Tax incentives available
4. Structuring opportunities
5. Timing strategies

Recommend legal tax-efficient strategies within Nigerian law.

Respond in this JSON format:
{
  "current_assessment": {
    "effective_tax_rate": "Estimated %",
    "potential_savings_identified": 0.00,
    "optimization_score": 65
  },
  "recommendations": [
    {
      "category": "Deductions",
      "recommendation": "Claim full capital allowances on equipment",
      "potential_saving": 0.00,
      "implementation_effort": "low|medium|high",
      "risk_level": "low|medium|high",
      "legal_basis": "CITA Section X",
      "action_required": "Detailed description"
    }
  ],
  "incentives_available": [
    {
      "incentive": "Pioneer Status",
      "eligibility": "Yes/No/Maybe",
      "potential_benefit": "Tax holiday for 3-5 years",
      "application_process": "Apply to NIPC"
    }
  ],
  "timing_strategies": [
    "Defer income to next year if close to higher tax bracket"
  ],
  "compliance_improvements": [
    "Maintain better documentation for expense claims"
  ],
  "warnings": [
    "Avoid aggressive tax positions that may trigger audit"
  ],
  "implementation_priority": [
    {
      "priority": 1,
      "action": "Most impactful recommendation",
      "timeline": "Immediate/Short-term/Long-term"
    }
  ],
  "estimated_annual_savings": 0.00,
  "confidence": 0.90,
  "disclaimer": "Recommendations should be reviewed by tax professional before implementation"
}
PROMPT;

        $result = $this->callAi($prompt);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Optimization recommendations failed',
            ];
        }

        $recommendations = $this->parseJsonResponse($result['response']['content']);

        if ($this->currentStep) {
            $this->currentStep->update([
                'parsed_response' => $recommendations,
                'confidence_score' => $recommendations['confidence'] ?? null,
                'tokens_used' => $result['response']['tokens'] ?? 0,
            ]);
        }

        return [
            'success' => true,
            'recommendations' => $recommendations,
            'tokens_used' => $result['response']['tokens'] ?? 0,
        ];
    }

    /**
     * Analyze tax scenario
     */
    public function analyzeScenario(string $scenario, array $assumptions = []): array
    {
        $this->logActivity('analyze_scenario');

        $prompt = <<<PROMPT
Analyze this Nigerian tax scenario:

Scenario: {$scenario}

Assumptions:
{$this->formatForJson($assumptions)}

Business Context:
{$this->formatBusinessContext()}

Provide:
1. Tax implications analysis
2. Best course of action
3. Alternative approaches
4. Risks and mitigations
5. Estimated financial impact

Respond in this JSON format:
{
  "scenario_summary": "Brief restatement",
  "tax_implications": {
    "vat": "Impact on VAT",
    "cit": "Impact on Company Income Tax",
    "paye": "Impact on PAYE",
    "wht": "Impact on WHT",
    "other": "Other tax considerations"
  },
  "recommended_approach": {
    "strategy": "Detailed recommendation",
    "legal_basis": "Supporting laws",
    "implementation_steps": [],
    "estimated_tax_impact": 0.00
  },
  "alternatives": [
    {
      "approach": "Alternative strategy",
      "pros": [],
      "cons": [],
      "tax_impact": 0.00
    }
  ],
  "risks": [
    {
      "risk": "Description",
      "severity": "high|medium|low",
      "mitigation": "How to address"
    }
  ],
  "financial_impact": {
    "year_1": 0.00,
    "year_2": 0.00,
    "year_3": 0.00
  },
  "documentation_required": [],
  "approval_needed": "FIRS/NIPC/Other",
  "confidence": 0.90
}
PROMPT;

        $result = $this->callAi($prompt, [
            'scenario' => $scenario,
            'assumptions' => $assumptions,
        ]);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Scenario analysis failed',
            ];
        }

        $analysis = $this->parseJsonResponse($result['response']['content']);

        if ($this->currentStep) {
            $this->currentStep->update([
                'parsed_response' => $analysis,
                'confidence_score' => $analysis['confidence'] ?? null,
                'tokens_used' => $result['response']['tokens'] ?? 0,
            ]);
        }

        return [
            'success' => true,
            'analysis' => $analysis,
            'tokens_used' => $result['response']['tokens'] ?? 0,
        ];
    }

    /**
     * Format business context for AI
     */
    private function formatBusinessContext(): string
    {
        $context = $this->getBusinessContext();
        return json_encode($context, JSON_PRETTY_PRINT);
    }

    /**
     * Format data for JSON
     */
    private function formatForJson($data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
