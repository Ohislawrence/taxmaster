<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\AiAgentLog;
use App\Models\TaxReturn;
use App\Services\AiAgentService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AiController extends Controller
{
    protected $aiAgentService;
    protected $subscriptionService;

    public function __construct(AiAgentService $aiAgentService, SubscriptionService $subscriptionService)
    {
        $this->aiAgentService = $aiAgentService;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Get AI configuration from environment variables
     */
    protected function getAiConfig()
    {
        return [
            'provider' => config('services.ai.provider', env('AI_PROVIDER', 'deepseek')),
            'enabled' => config('services.ai.enabled', env('AI_ENABLED', true)),
            'deepseek_key' => config('services.ai.deepseek_key', env('DEEPSEEK_API_KEY')),
            'gemini_key' => config('services.ai.gemini_key', env('GEMINI_API_KEY')),
            'model' => $this->getDefaultModel(),
            'max_tokens' => 2000,
            'temperature' => 0.7,
        ];
    }

    /**
     * Get default model based on provider
     */
    protected function getDefaultModel()
    {
        $provider = config('services.ai.provider', env('AI_PROVIDER', 'deepseek'));
        if ($provider === 'deepseek') {
            return 'deepseek-chat';
        }
        return 'gemini-pro'; // Default Gemini model
    }

    /**
     * Check if AI is properly configured
     */
    protected function checkAiConfiguration()
    {
        $config = $this->getAiConfig();

        if (!$config['enabled']) {
            return [
                'configured' => false,
                'error' => 'AI features are not enabled. Please contact your administrator.',
            ];
        }

        $apiKey = $config['provider'] === 'deepseek'
            ? $config['deepseek_key']
            : $config['gemini_key'];

        if (!$apiKey) {
            return [
                'configured' => false,
                'error' => 'AI is not configured. Please ask your administrator to set up AI features.',
            ];
        }

        return [
            'configured' => true,
            'provider' => $config['provider'],
        ];
    }

    /**
     * Show AI insights dashboard
     */
    public function insights()
    {
        $business = Auth::user()->defaultBusiness();

        if (! $business) {
            return redirect()->route('business.dashboard')
                ->with('error', 'No business selected. Please select a business to view AI insights.');
        }

        // Check subscription feature
        if (! $this->subscriptionService->canPerformAction($business, 'use_ai_analysis')) {
            return redirect()->route('business.dashboard')
                ->with('error', 'Your current plan does not include AI insights. Please upgrade to Professional or higher.');
        }

        $aiStatus = $this->checkAiConfiguration();

        return Inertia::render('Business/Ai/TaxInsights', [
            'business' => $business,
            'aiConfigured' => $aiStatus['configured'],
            'aiError' => $aiStatus['error'] ?? null,
        ]);
    }

    /**
     * Show AI chat interface
     */
    public function chat()
    {
        $business = Auth::user()->defaultBusiness() ?? null;

        // AI chat is now open to all users and does not require a business

        $aiStatus = $this->checkAiConfiguration();

        return Inertia::render('Business/Ai/Chat', [
            'business' => $business,
            'aiConfigured' => $aiStatus['configured'],
            'aiError' => $aiStatus['error'] ?? null,
            'provider' => $aiStatus['provider'] ?? null,
        ]);
    }

    /**
     * Send message to AI
     */
    public function sendMessage(Request $request)
    {
        $business = Auth::user()->defaultBusiness();

        // AI chat is now open to all users; no subscription check

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

        // Check AI configuration
        $aiStatus = $this->checkAiConfiguration();
        if (!$aiStatus['configured']) {
            return response()->json([
                'error' => $aiStatus['error'],
            ], 503);
        }

        try {
            $aiConfig = $this->getAiConfig();
            $aiService = new AiAgentService($business, $aiConfig['provider']);

            // Build context-aware prompt (handle missing business inside builder)
            $systemPrompt = $this->buildSystemPrompt($business, $validated['context'] ?? 'general');
            $fullPrompt = "{$systemPrompt}\n\nUser Question: {$validated['message']}";

            // Call AI API
            $response = $this->callAiDirectly($aiConfig, $fullPrompt);

            if (!$response['success']) {
                return response()->json([
                    'error' => $response['error'] ?? 'AI call failed',
                ], 400);
            }

            // Log interaction only if business exists
            if ($business) {
                AiAgentLog::create([
                    'business_id' => $business->id,
                    'action_type' => 'chat',
                    'ai_provider' => $aiConfig['provider'],
                    'prompt' => $validated['message'],
                    'response' => $response['message'],
                    'status' => 'completed',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $response['message'],
                'context' => $validated['context'] ?? 'general',
            ]);
        } catch (\Exception $e) {
            Log::error('AI chat error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'error' => 'Failed to process your question: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Analyze tax return with AI
     */
    public function analyzeTaxReturn(TaxReturn $taxReturn)
    {
        $business = Auth::user()->defaultBusiness();

        // Require a business context
        if (! $business) {
            return response()->json(['error' => 'No business selected'], 403);
        }

        // Verify ownership
        if ($taxReturn->business_id !== $business->id) {
            abort(403);
        }

        // Check AI configuration
        $aiStatus = $this->checkAiConfiguration();
        if (!$aiStatus['configured']) {
            return response()->json([
                'error' => $aiStatus['error'],
            ], 503);
        }

        try {
            $aiConfig = $this->getAiConfig();
            $aiService = new AiAgentService($business, $aiConfig['provider']);
            $result = $aiService->analyzeTaxReturn($taxReturn);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Tax analysis error', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Failed to analyze tax return',
            ], 500);
        }
    }

    /**
     * Get tax optimization recommendations
     */
    public function getTaxOptimizationRecommendations(TaxReturn $taxReturn)
    {
        $business = Auth::user()->defaultBusiness();

        if (! $business) {
            return response()->json(['error' => 'No business selected'], 403);
        }

        // Check subscription feature
        if (! $this->subscriptionService->canPerformAction($business, 'use_ai_optimization')) {
            return response()->json([
                'error' => 'Your current plan does not include AI optimization. Please upgrade to Professional or higher.',
            ], 403);
        }

        // Verify ownership
        if ($taxReturn->business_id !== $business->id) {
            abort(403);
        }

        // Check AI configuration
        $aiStatus = $this->checkAiConfiguration();
        if (!$aiStatus['configured']) {
            return response()->json([
                'error' => $aiStatus['error'],
            ], 503);
        }

        try {
            $aiConfig = $this->getAiConfig();
            $aiService = new AiAgentService($business, $aiConfig['provider']);
            $result = $aiService->getTaxOptimizationRecommendations($taxReturn);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Optimization error', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Failed to get recommendations',
            ], 500);
        }
    }

    /**
     * Get AI interaction history
     */
    public function getHistory(Request $request)
    {
        // Use default business so accountants/managers are supported
        $business = Auth::user()->defaultBusiness();

        if (! $business) {
            return response()->json(['error' => 'No business selected'], 403);
        }

        $logs = AiAgentLog::where('business_id', $business->id)
            ->when($request->action_type, function ($query) use ($request) {
                return $query->where('action_type', $request->action_type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($logs);
    }

    /**
     * Clear persisted AI chat history for the current business (chat logs only)
     */
    public function clearHistory(Request $request)
    {
        $business = Auth::user()->defaultBusiness();

        if (! $business) {
            return response()->json(['error' => 'No business selected'], 403);
        }

        try {
            AiAgentLog::where('business_id', $business->id)
                ->where('action_type', 'chat')
                ->delete();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Failed to clear AI history', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to clear history'], 500);
        }
    }

    /**
     * Build context-aware system prompt
     */
    protected function buildSystemPrompt($business, $context): string
    {
        // Allow $business to be null (chat may be used without a selected business)
        $bizName = $business?->name ?? 'your business';
        $bizType = $business?->business_type ?? 'business';
        $bizIndustry = $business?->industry ?? 'your industry';

        $basePrompt = <<<PROMPT
    You are TaxMaster — an expert Nigerian tax accountant, tax lawyer, and digital assistant for {$bizName}, a {$bizType} business in the {$bizIndustry} industry.

PERSONALITY & STYLE:
- Be concise and direct; prefer short, actionable answers (2–4 sentences when possible)
- Use simple language a business owner can understand
- When citing law, give the specific section (e.g. "Section 40, CITA")
- If a question is ambiguous, answer the most likely interpretation and briefly note alternatives

NIGERIAN TAX LAW KNOWLEDGE:
- Companies Income Tax (CIT): Finance Act 2019 rates — Small companies (turnover < ₦25M) = 0%, Medium (₦25M–₦100M) = 20%, Large (> ₦100M) = 30%. Governed by CITA (Companies Income Tax Act, Cap C21 LFN 2004, as amended).
- Personal Income Tax / PAYE: Progressive rates from 7% to 24% under PITA (Personal Income Tax Act). Consolidated Relief Allowance = ₦200,000 + 20% of gross income; higher of 1% of gross income or ₦200,000.
- Value Added Tax (VAT): Standard rate is 7.5% (Finance Act 2019). **VAT EXEMPTIONS (Finance Acts 2019/2020)**: Exempt goods: medical/pharmaceutical products, basic food items (honey, bread, cereals, oils, fish, flour, fruits, meat, milk, nuts, pulses, roots, salt, vegetables, water), books/educational materials, baby products, agricultural inputs, exported goods, sanitary products, commercial aircraft. Exempt services: medical services, microfinance services, educational performances, exported services, tuition (nursery-tertiary), airline tickets (Nigerian airlines), agricultural equipment rental. **Turnover exemption**: Businesses < ₦25M annual turnover exempt from VAT registration. **TaxMaster Feature**: Users can toggle VAT exempt status in business settings if they deal exclusively in exempt goods/services. Governed by VAT Act Cap V1 LFN 2004.
- Withholding Tax (WHT): Rates vary by transaction type — Companies (resident): 10% (dividends, interest, rent), 5% (professional/consultancy/management/technical fees, commissions, brokerage, other construction), 2% (major construction projects, goods supply, telecom services, other services). Individuals (resident): 10% (dividends, interest, rent), 5% (professional fees, consultancy, technical fees, management fees, commissions, royalties, other construction), 15% (directors' fees), 2% (major construction, goods supply, other services). **IMPORTANT - WHT Regulations 2024**: Where a supplier has no Tax Identification Number (TIN) or an invalid TIN, the applicable WHT rate shall be **DOUBLE** the standard rate. Example: Professional services normally 5% → 10% if no TIN. This is mandatory to encourage tax compliance. Final tax for non-residents; credit against CIT for residents.
- Capital Gains Tax: 10% on gains from disposal of chargeable assets (CGT Act, Cap C1 LFN 2004).
- Filing deadlines: CIT returns due 6 months after year-end. PAYE remittance by 10th of following month. VAT returns by 21st of following month. Annual returns for PAYE by Jan 31.
- Penalties: Late filing attracts ₦25,000 (first month) + ₦5,000 each subsequent month for CIT. PAYE late remittance = 10% penalty + interest.
- TIN (Tax Identification Number) is required for all businesses and must be obtained from FIRS (federal) or relevant state IRS.
- Relevant bodies: FIRS (Federal Inland Revenue Service) for federal taxes. State IRS for PAYE and personal taxes.

APP FEATURES (TaxMaster Platform):
- Dashboard: Overview of business tax position, upcoming deadlines, and compliance status
- PAYE Returns: Add staff, compute monthly PAYE using progressive tax bands, generate returns for remittance. Each return has a detail page where you can generate a payment RRR.
- CIT Returns: File annual company income tax with auto-calculated rates using Finance Act 2019 tiers. Each return has a detail page where you can generate a payment RRR.
- WHT Transactions: Record withholding tax deductions by transaction type with automatic rate application. **NEW**: System automatically applies double rate if vendor TIN is missing or invalid (per WHT Regulations 2024). WHT returns have a detail page where you can generate a payment RRR.
- VAT Returns: Track output VAT collected and input VAT paid, generate net VAT returns. Each return has a detail page where you can generate a payment RRR.
- Tax Returns: Central hub for all tax filings across PAYE, CIT, VAT, WHT
- Payments: View and track all government payment records and their statuses
- Compliance Calendar: View all upcoming tax deadlines and compliance obligations
- Financial Statements: Generate balance sheet, income statement, and cash flow reports
- CAC Annual Return: File Corporate Affairs Commission annual returns
- Staff Management: Add/manage employees for PAYE computation
- Bank Accounts: Link bank accounts to auto-import transactions
- AI Insights: Get AI-powered tax optimization suggestions and compliance analysis
- Accountant Features: Provide tools for accountants managing client businesses:
    - Accountant Dashboard: view and manage all client/managed businesses, quick-switch context, and see aggregated compliance status.
    - Multi-business Management: create businesses on behalf of clients, invite clients, assign/manage access, and export managed companies as CSV.
    - Affiliate & Payouts: generate affiliate links, view referrals, request payouts, and capture bank details for payouts (admin approves/marks paid).
    - Client Onboarding: auto-enroll clients into starter plans, record referral sources, and maintain audit logs for business switching and actions.
- Subscription Plans: Free, Starter, Professional, and Enterprise tiers unlock different features

PAYMENT PROCESS (HOW TO GENERATE RRR):
1. Go to the relevant tax section (PAYE Returns, CIT Returns, VAT Returns, or WHT Transactions)
2. File/create the tax return first with all required details
3. Open the specific return's detail page (click "View" on the return)
4. On the return detail page, click the "Generate Payment RRR" button
5. The app calculates the amount owed and generates a Remita Retrieval Reference (RRR) number
6. Use the RRR to pay on the Remita platform (bank transfer, card, or USSD)
7. Payment status updates automatically in the app
8. A receipt/acknowledgment is generated for your records
NOTE: RRR is generated from within each tax return's detail page, NOT from a separate Payments page. The Payments page is for viewing/tracking existing payment records.
PROMPT;

        $contextPrompts = [
            'general' => 'Answer questions about tax, payroll, compliance, business finance, or how to use the TaxMaster app.',
            'tax_planning' => 'Focus on strategies to optimize tax liability within Nigerian tax regulations. Reference specific sections of CITA, PITA, or VAT Act where relevant.',
            'payroll' => 'Provide advice on PAYE computation, employee deductions, consolidated relief allowance, and payroll compliance. Guide users through the PAYE Returns section of the app.',
            'deductions' => 'Help identify valid business deductions, allowable WHT credits, capital allowances, and optimize write-offs under Nigerian tax law.',
            'compliance' => 'Ensure all recommendations comply with FIRS regulations and Nigerian tax law. Highlight filing deadlines, penalty risks, and required documentation.',
        ];

        return $basePrompt . "\n\nCURRENT CONTEXT: " . ($contextPrompts[$context] ?? $contextPrompts['general']);
    }

    /**
     * Call AI API directly with proper format
     */
    protected function callAiDirectly($config, $prompt)
    {
        $client = new \GuzzleHttp\Client();

        if ($config['provider'] === 'deepseek') {
            return $this->callDeepseek($client, $config, $prompt);
        } elseif ($config['provider'] === 'gemini') {
            return $this->callGemini($client, $config, $prompt);
        }

        return [
            'success' => false,
            'error' => 'Unknown provider',
        ];
    }

    /**
     * Call Deepseek API
     */
    protected function callDeepseek($client, $config, $prompt)
    {
        try {
            $apiKey = $config['deepseek_key'] ?? env('DEEPSEEK_API_KEY');

            if (!$apiKey) {
                return [
                    'success' => false,
                    'error' => 'Deepseek API key not configured',
                ];
            }

            $response = $client->post('https://api.deepseek.com/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'verify' => env('APP_ENV') === 'production' ? true : false,
                'json' => [
                    'model' => $config['model'] ?? 'deepseek-chat',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_tokens' => $config['max_tokens'] ?? 2000,
                    'temperature' => $config['temperature'] ?? 0.7,
                ],
                'timeout' => 30,
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['choices'][0]['message']['content'])) {
                return [
                    'success' => true,
                    'message' => $result['choices'][0]['message']['content'],
                ];
            }

            return [
                'success' => false,
                'error' => 'Invalid response from Deepseek',
            ];
        } catch (\Exception $e) {
            Log::error('Deepseek error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Deepseek API error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Call Google Gemini API
     */
    protected function callGemini($client, $config, $prompt)
    {
        try {
            $apiKey = $config['gemini_key'] ?? env('GEMINI_API_KEY');

            if (!$apiKey) {
                return [
                    'success' => false,
                    'error' => 'Gemini API key not configured',
                ];
            }

            $response = $client->post("https://generativelanguage.googleapis.com/v1beta/models/{$config['model']}:generateContent", [
                'query' => [
                    'key' => $apiKey,
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'verify' => env('APP_ENV') === 'production' ? true : false,
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt,
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => $config['max_tokens'] ?? 2000,
                        'temperature' => $config['temperature'] ?? 0.7,
                    ],
                ],
                'timeout' => 30,
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                return [
                    'success' => true,
                    'message' => $result['candidates'][0]['content']['parts'][0]['text'],
                ];
            }

            return [
                'success' => false,
                'error' => 'Invalid response from Gemini',
            ];
        } catch (\Exception $e) {
            Log::error('Gemini error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Gemini API error: ' . $e->getMessage(),
            ];
        }
    }
}

