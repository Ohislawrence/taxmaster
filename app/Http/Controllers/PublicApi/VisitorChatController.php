<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Services\PublicAiAgentService;
use Illuminate\Support\Facades\Log;

class VisitorChatController extends Controller
{
    /**
     * Show the public AI chat page (if needed)
     */
    public function chat()
    {
        return inertia('Public/VisitorChat');
    }

    /**
     * Handle visitor AI chat message
     */
    public function sendVisitorMessage(Request $request)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:2000',
                'context' => 'nullable|string|in:general,tax_planning,payroll,deductions,compliance',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation error: ' . implode(', ', array_values($e->errors())[0] ?? []),
            ], 422);
        }

        // AI config check (public, no business context)
        $aiConfig = [
            'provider' => config('services.ai.provider', env('AI_PROVIDER', 'deepseek')),
            'enabled' => config('services.ai.enabled', env('AI_ENABLED', true)),
            'deepseek_key' => config('services.ai.deepseek_key', env('DEEPSEEK_API_KEY')),
            'gemini_key' => config('services.ai.gemini_key', env('GEMINI_API_KEY')),
            'model' => config('services.ai.model', 'deepseek-chat'),
            'max_tokens' => 2000,
            'temperature' => 0.7,
        ];

        if (!$aiConfig['enabled'] || !($aiConfig['deepseek_key'] || $aiConfig['gemini_key'])) {
            return response()->json([
                'error' => 'AI is not configured. Please try again later.'
            ], 503);
        }

        try {
            $provider = $aiConfig['provider'];
            $aiService = new PublicAiAgentService($provider);

            // Build context-aware system prompt (public version)
            $contextPrompts = [
                'general' => 'Answer questions about Nigerian tax, payroll, compliance, or how to use the TaxMaster app.',
                'tax_planning' => 'Focus on strategies to optimize tax liability within Nigerian tax regulations. Reference specific sections of CITA, PITA, or VAT Act where relevant.',
                'payroll' => 'Provide advice on PAYE computation, employee deductions, consolidated relief allowance, and payroll compliance.',
                'deductions' => 'Help identify valid business deductions, allowable WHT credits, capital allowances, and optimize write-offs under Nigerian tax law.',
                'compliance' => 'Ensure all recommendations comply with FIRS regulations and Nigerian tax law. Highlight filing deadlines, penalty risks, and required documentation.',
            ];
            $systemPrompt = "You are TaxMaster — an expert Nigerian tax accountant, tax lawyer, and digital assistant.\n\nPERSONALITY & STYLE:\n- Be concise and direct; prefer short, actionable answers (2–4 sentences when possible)\n- Use simple language a business owner can understand\n- When citing law, give the specific section (e.g. 'Section 40, CITA')\n- If a question is ambiguous, answer the most likely interpretation and briefly note alternatives\n\nNIGERIAN TAX LAW KNOWLEDGE:\n- Companies Income Tax (CIT): Finance Act 2019 rates — Small companies (turnover < ₦25M) = 0%, Medium (₦25M–₦100M) = 20%, Large (> ₦100M) = 30%. Governed by CITA (Companies Income Tax Act, Cap C21 LFN 2004, as amended).\n- Personal Income Tax / PAYE: Progressive rates from 7% to 24% under PITA (Personal Income Tax Act). Consolidated Relief Allowance = ₦200,000 + 20% of gross income; higher of 1% of gross income or ₦200,000.\n- Value Added Tax (VAT): Standard rate is 7.5% (Finance Act 2019). Exemptions apply to basic food items, medical, educational services. Governed by VAT Act Cap V1 LFN 2004.\n- Withholding Tax (WHT): Rates vary by transaction type — 5% (dividends, interest, rent for companies), 10% (management/professional fees, construction, etc). Final tax for non-residents; credit against CIT for residents.\n- Capital Gains Tax: 10% on gains from disposal of chargeable assets (CGT Act, Cap C1 LFN 2004).\n- Filing deadlines: CIT returns due 6 months after year-end. PAYE remittance by 10th of following month. VAT returns by 21st of following month. Annual returns for PAYE by Jan 31.\n- Penalties: Late filing attracts ₦25,000 (first month) + ₦5,000 each subsequent month for CIT. PAYE late remittance = 10% penalty + interest.\n- TIN (Tax Identification Number) is required for all businesses and must be obtained from FIRS (federal) or relevant state IRS.\n- Relevant bodies: FIRS (Federal Inland Revenue Service) for federal taxes. State IRS for PAYE and personal taxes.\n\nCURRENT CONTEXT: " . ($contextPrompts[$validated['context'] ?? 'general']);

            $fullPrompt = "{$systemPrompt}\n\nUser Question: {$validated['message']}";
            $response = $aiService->callAiApi($fullPrompt);

            if (!$response['success']) {
                Log::error('VisitorChat AI call failed', [
                    'error' => $response['error'] ?? 'AI call failed',
                    'input' => $validated,
                    'fullPrompt' => $fullPrompt ?? null,
                ]);
                return response()->json([
                    'error' => $response['error'] ?? 'AI call failed',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => $response['message'],
                'context' => $validated['context'] ?? 'general',
            ]);
        } catch (\Exception $e) {
            Log::error('VisitorChat Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all(),
            ]);
            return response()->json([
                'error' => 'AI error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
