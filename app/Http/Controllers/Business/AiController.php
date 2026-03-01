<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\AiAgentLog;
use App\Models\TaxReturn;
use App\Services\AiAgentService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
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
        $business = auth()->user()->ownedBusiness;

        // Check subscription feature
        if (!$this->subscriptionService->canPerformAction($business, 'use_ai_analysis')) {
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
        $business = auth()->user()->ownedBusiness;

        // Check subscription feature
        if (!$this->subscriptionService->canPerformAction($business, 'use_ai_chat')) {
            return redirect()->route('business.dashboard')
                ->with('error', 'Your current plan does not include AI chat. Please upgrade to Professional or higher.');
        }

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
        $business = auth()->user()->ownedBusiness;

        // Check subscription feature
        if (!$this->subscriptionService->canPerformAction($business, 'use_ai_chat')) {
            return response()->json([
                'error' => 'Your current plan does not include AI chat. Please upgrade to Professional or higher.',
            ], 403);
        }

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

            // Build context-aware prompt
            $systemPrompt = $this->buildSystemPrompt($business, $validated['context'] ?? 'general');
            $fullPrompt = "{$systemPrompt}\n\nUser Question: {$validated['message']}";

            // Call AI API
            $response = $this->callAiDirectly($aiConfig, $fullPrompt);

            if (!$response['success']) {
                return response()->json([
                    'error' => $response['error'] ?? 'AI call failed',
                ], 400);
            }

            // Log interaction
            AiAgentLog::create([
                'business_id' => $business->id,
                'action_type' => 'chat',
                'ai_provider' => $aiConfig['provider'],
                'prompt' => $validated['message'],
                'response' => $response['message'],
                'status' => 'completed',
            ]);

            return response()->json([
                'success' => true,
                'message' => $response['message'],
                'context' => $validated['context'] ?? 'general',
            ]);
        } catch (\Exception $e) {
            \Log::error('AI chat error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

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
        $business = auth()->user()->ownedBusiness;

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
            \Log::error('Tax analysis error', ['error' => $e->getMessage()]);

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
        $business = auth()->user()->ownedBusiness;

        // Check subscription feature
        if (!$this->subscriptionService->canPerformAction($business, 'use_ai_optimization')) {
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
            \Log::error('Optimization error', ['error' => $e->getMessage()]);

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
        $business = auth()->user()->ownedBusiness;

        $logs = AiAgentLog::where('business_id', $business->id)
            ->when($request->action_type, function ($query) use ($request) {
                return $query->where('action_type', $request->action_type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($logs);
    }

    /**
     * Build context-aware system prompt
     */
    protected function buildSystemPrompt($business, $context): string
    {
        $basePrompt = "You are an expert tax advisor for Nigerian businesses. You provide clear, actionable, and tax-compliant advice for {$business->name}, a {$business->business_type} business in the {$business->industry} industry.";

        $contextPrompts = [
            'general' => 'Answer questions about tax, payroll, compliance, and business finance.',
            'tax_planning' => 'Focus on strategies to optimize tax liability within Nigerian tax regulations.',
            'payroll' => 'Provide advice on payroll tax, employee deductions, and compensation planning.',
            'deductions' => 'Help identify valid business deductions and optimize write-offs.',
            'compliance' => 'Ensure all recommendations comply with FIRS regulations and Nigerian tax law.',
        ];

        return $basePrompt . ' ' . ($contextPrompts[$context] ?? $contextPrompts['general']);
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
            \Log::error('Deepseek error', ['error' => $e->getMessage()]);
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
            \Log::error('Gemini error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Gemini API error: ' . $e->getMessage(),
            ];
        }
    }
}

