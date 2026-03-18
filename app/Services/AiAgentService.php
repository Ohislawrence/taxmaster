<?php

namespace App\Services;

use App\Models\AiAgentLog;
use App\Models\Business;
use App\Models\TaxReturn;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Exception;

class AiAgentService
{
    protected $client;
    protected $business;
    protected $provider;

    public function __construct(Business $business, ?string $provider = null)
    {
        $this->business = $business;
        $this->provider = $provider ?? env('AI_PROVIDER', 'deepseek');
        $this->client = new Client();
    }

    /**
     * Get API key for the current provider
     */
    protected function getApiKey(): ?string
    {
        if ($this->provider === 'deepseek') {
            return env('DEEPSEEK_API_KEY');
        } elseif ($this->provider === 'gemini') {
            return env('GEMINI_API_KEY');
        }
        return null;
    }

    /**
     * Get model name for the current provider
     */
    protected function getModel(): string
    {
        if ($this->provider === 'deepseek') {
            return 'deepseek-chat';
        }
        return 'gemini-pro';
    }

    /**
     * Explain an insight and suggest actions using the AI provider.
     * Returns array with 'success' and 'explanation' keys on success.
     */
    public function explainInsight(string $title, string $message): array
    {
        try {
            $apiKey = $this->getApiKey();

            if (! $apiKey) {
                return [
                    'success' => false,
                    'error' => 'AI configuration not found',
                ];
            }

            $prompt = "You are an expert Nigerian tax advisor.\n\nInsight: {$title}\n\nDetails: {$message}\n\nProvide a concise (1-2 sentence) explanation of what this likely means for the business, and list 3 short, actionable next steps the business should take. Return the explanation followed by a numbered list of actions.";

            $response = $this->callAiApi($prompt, 'insight_explain');

            if ($response['success']) {
                $this->logAiInteraction('insight_explain', $prompt, $response['analysis'] ?? null, 'completed');

                return [
                    'success' => true,
                    'explanation' => $response['analysis'] ?? null,
                ];
            }

            $this->logAiInteraction('insight_explain', $prompt, null, 'failed', $response['error'] ?? 'Unknown');

            return $response;
        } catch (Exception $e) {
            Log::error('AI explainInsight error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'AI explainInsight failed',
            ];
        }
    }
    /**
     * Analyze tax return using AI
     */
    public function analyzeTaxReturn(TaxReturn $taxReturn): array
    {
        try {
            $apiKey = $this->getApiKey();

            if (!$apiKey) {
                return [
                    'success' => false,
                    'message' => 'AI configuration not found',
                ];
            }

            $prompt = $this->buildTaxAnalysisPrompt($taxReturn);

            $response = $this->callAiApi($prompt, 'tax_analysis');

            if ($response['success']) {
                // Update tax return with AI analysis
                $taxReturn->update([
                    'ai_analysis' => $response['analysis'],
                    'ai_processed_at' => now(),
                ]);

                // Log the AI interaction
                $this->logAiInteraction('tax_analysis', $prompt, $response['analysis'], 'completed');

                return [
                    'success' => true,
                    'analysis' => $response['analysis'],
                    'recommendations' => $response['recommendations'] ?? [],
                ];
            }

            $this->logAiInteraction('tax_analysis', $prompt, null, 'failed', $response['error'] ?? 'Unknown error');

            return $response;
        } catch (Exception $e) {
            Log::error('AI analysis error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'AI analysis failed',
            ];
        }
    }

    /**
     * Get AI recommendations for tax optimization
     */
    public function getTaxOptimizationRecommendations(TaxReturn $taxReturn): array
    {
        try {
            $apiKey = $this->getApiKey();

            if (!$apiKey) {
                return [
                    'success' => false,
                    'message' => 'AI configuration not found',
                ];
            }

            $prompt = $this->buildOptimizationPrompt($taxReturn);

            $response = $this->callAiApi($prompt, 'tax_optimization');

            if ($response['success']) {
                $this->logAiInteraction('tax_optimization', $prompt, $response['recommendations'], 'completed');

                return [
                    'success' => true,
                    'recommendations' => $response['recommendations'],
                ];
            }

            return $response;
        } catch (Exception $e) {
            Log::error('AI optimization error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Failed to get recommendations',
            ];
        }
    }

    /**
     * Call the configured AI API
     */
    /**
     * Call AI API for transaction categorization (public wrapper)
     */
    public function callAiForCategorization(string $prompt): ?string
    {
        $result = $this->callAiApi($prompt, 'categorization');

        if ($result['success'] && !empty($result['analysis'])) {
            return $result['analysis'];
        }

        return null;
    }

    /**
     * Call AI API based on provider
     */
    protected function callAiApi(string $prompt, string $actionType): array
    {
        $apiKey = $this->getApiKey();

        if (!$apiKey) {
            return [
                'success' => false,
                'error' => 'AI provider API key not configured',
            ];
        }

        if ($this->provider === 'deepseek') {
            return $this->callDeepseekApi($prompt, $apiKey, $actionType);
        } elseif ($this->provider === 'gemini') {
            return $this->callGeminiApi($prompt, $apiKey, $actionType);
        }

        return [
            'success' => false,
            'error' => 'Unknown AI provider',
        ];
    }

    /**
     * Call Deepseek API
     */
    protected function callDeepseekApi(string $prompt, string $apiKey, string $actionType): array
    {
        try {
            $response = $this->client->post('https://api.deepseek.com/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'verify' => env('APP_ENV') === 'production' ? true : false,
                'json' => [
                    'model' => $this->getModel(),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert tax advisor for Nigerian businesses. Provide clear, actionable, and compliant tax advice.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_tokens' => 2000,
                    'temperature' => 0.7,
                ],
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['choices'][0]['message']['content'])) {
                return [
                    'success' => true,
                    'analysis' => $result['choices'][0]['message']['content'],
                    'tokens_used' => $result['usage']['total_tokens'] ?? 0,
                ];
            }

            return [
                'success' => false,
                'error' => 'Invalid response from AI',
            ];
        } catch (Exception $e) {
            Log::error('Deepseek API error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Call Google Gemini API
     */
    protected function callGeminiApi(string $prompt, string $apiKey, string $actionType): array
    {
        try {
            $response = $this->client->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->getModel()}:generateContent", [
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
                        'maxOutputTokens' => 2000,
                        'temperature' => 0.7,
                    ],
                ],
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                return [
                    'success' => true,
                    'analysis' => $result['candidates'][0]['content']['parts'][0]['text'],
                ];
            }

            return [
                'success' => false,
                'error' => 'Invalid response from Gemini',
            ];
        } catch (Exception $e) {
            Log::error('Gemini API error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build prompt for tax analysis
     */
    protected function buildTaxAnalysisPrompt(TaxReturn $taxReturn): string
    {
        return <<<PROMPT
Please analyze the following Nigerian business tax return and provide insights:

Tax Period: {$taxReturn->tax_period}
Return Type: {$taxReturn->return_type}
Gross Income: ₦{$taxReturn->gross_income}
Deductions: ₦{$taxReturn->deductions}
Taxable Income: ₦{$taxReturn->taxable_income}
Total Tax Due: ₦{$taxReturn->total_tax_due}

Business Details:
- Type: {$this->business->business_type}
- Industry: {$this->business->industry}
- Annual Revenue: ₦{$this->business->annual_revenue}
- Number of Staff: {$this->business->staff()->count()}

Please provide:
1. Analysis of tax liabilities and compliance status
2. Potential areas for deductions
3. Recommendations for tax optimization (within legal bounds)
4. Due date reminders and compliance recommendations
PROMPT;
    }

    /**
     * Build prompt for tax optimization
     */
    protected function buildOptimizationPrompt(TaxReturn $taxReturn): string
    {
        return <<<PROMPT
As a tax advisor for Nigerian businesses, provide specific tax optimization recommendations for:

Business: {$this->business->name}
Tax Period: {$taxReturn->tax_period}
Current Tax Due: ₦{$taxReturn->total_tax_due}
Taxable Income: ₦{$taxReturn->taxable_income}

Please provide practical, legal recommendations to:
1. Minimize tax liability
2. Optimize deductions
3. Improve cash flow management
4. Plan for future tax periods
PROMPT;
    }

    /**
     * Log AI interaction
     */
    protected function logAiInteraction(string $actionType, string $prompt, ?string $response, string $status, ?string $errorMessage = null): void
    {
        AiAgentLog::create([
            'business_id' => $this->business->id,
            'action_type' => $actionType,
            'ai_provider' => $this->provider,
            'prompt' => $prompt,
            'response' => $response,
            'status' => $status,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Switch AI provider
     */
    public function switchProvider(string $provider): void
    {
        $this->provider = $provider;
    }
}
