<?php

namespace App\Services\TaxAgents;

use App\Models\Business;
use App\Models\AiWorkflowStep;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Base abstract class for all Nigerian tax AI agents
 */
abstract class BaseTaxAgent
{
    protected Client $client;
    protected Business $business;
    protected string $provider;
    protected ?AiWorkflowStep $currentStep = null;

    public function __construct(Business $business, ?string $provider = null)
    {
        $this->business = $business;
        $this->provider = $provider ?? env('AI_PROVIDER', 'deepseek');
        $this->client = new Client(['timeout' => 60]);
    }

    /**
     * Get the agent's name
     */
    abstract public function getName(): string;

    /**
     * Get the agent's description
     */
    abstract public function getDescription(): string;

    /**
     * Set current workflow step for tracking
     */
    public function setCurrentStep(?AiWorkflowStep $step): void
    {
        $this->currentStep = $step;
    }

    /**
     * Get API key for the current provider
     */
    protected function getApiKey(): ?string
    {
        return match ($this->provider) {
            'deepseek' => env('DEEPSEEK_API_KEY'),
            'gemini' => env('GEMINI_API_KEY'),
            default => null,
        };
    }

    /**
     * Get API endpoint for the current provider
     */
    protected function getApiEndpoint(): string
    {
        return match ($this->provider) {
            'deepseek' => 'https://api.deepseek.com/v1/chat/completions',
            'gemini' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent',
            default => '',
        };
    }

    /**
     * Get model name for the current provider
     */
    protected function getModel(): string
    {
        return match ($this->provider) {
            'deepseek' => 'deepseek-chat',
            'gemini' => 'gemini-pro',
            default => 'deepseek-chat',
        };
    }

    /**
     * Call AI API with prompt
     */
    protected function callAi(string $prompt, ?array $context = null): array
    {
        try {
            $apiKey = $this->getApiKey();

            if (!$apiKey) {
                throw new Exception("No API key configured for provider: {$this->provider}");
            }

            // Track AI call start
            if ($this->currentStep) {
                $this->currentStep->update(['prompt' => $prompt]);
            }

            $response = $this->makeApiRequest($prompt, $context);

            // Track AI response
            if ($this->currentStep) {
                $this->currentStep->update([
                    'ai_response' => json_encode($response),
                    'ai_model' => $this->getModel(),
                ]);
            }

            return [
                'success' => true,
                'response' => $response,
            ];

        } catch (Exception $e) {
            Log::error("{$this->getName()} AI call failed", [
                'error' => $e->getMessage(),
                'business_id' => $this->business->id,
            ]);

            if ($this->currentStep) {
                $this->currentStep->update([
                    'error_message' => $e->getMessage(),
                ]);
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Make API request based on provider
     */
    protected function makeApiRequest(string $prompt, ?array $context = null): array
    {
        if ($this->provider === 'deepseek') {
            return $this->callDeepSeek($prompt, $context);
        } elseif ($this->provider === 'gemini') {
            return $this->callGemini($prompt, $context);
        }

        throw new Exception("Unsupported AI provider: {$this->provider}");
    }

    /**
     * Call DeepSeek API
     */
    protected function callDeepSeek(string $prompt, ?array $context = null): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->getSystemPrompt(),
            ],
        ];

        if ($context) {
            $messages[] = [
                'role' => 'user',
                'content' => json_encode($context, JSON_PRETTY_PRINT),
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        $response = $this->client->post($this->getApiEndpoint(), [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getApiKey(),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $this->getModel(),
                'messages' => $messages,
                'temperature' => 0.2, // Lower temperature for more consistent tax calculations
                'max_tokens' => 4000,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return [
            'content' => $data['choices'][0]['message']['content'] ?? '',
            'tokens' => $data['usage']['total_tokens'] ?? 0,
        ];
    }

    /**
     * Call Gemini API
     */
    protected function callGemini(string $prompt, ?array $context = null): array
    {
        $fullPrompt = $this->getSystemPrompt() . "\n\n";
        
        if ($context) {
            $fullPrompt .= "Context:\n" . json_encode($context, JSON_PRETTY_PRINT) . "\n\n";
        }
        
        $fullPrompt .= $prompt;

        $response = $this->client->post($this->getApiEndpoint() . '?key=' . $this->getApiKey(), [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $fullPrompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 4000,
                ],
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return [
            'content' => $data['candidates'][0]['content']['parts'][0]['text'] ?? '',
            'tokens' => $data['usageMetadata']['totalTokenCount'] ?? 0,
        ];
    }

    /**
     * Get system prompt for this agent - to be overridden by child classes
     */
    protected function getSystemPrompt(): string
    {
        return "You are an expert Nigerian tax compliance AI agent. Your role is to help businesses comply with Nigerian tax laws accurately and efficiently. Always respond in valid JSON format when requested.";
    }

    /**
     * Parse JSON response from AI
     */
    protected function parseJsonResponse(string $content): array
    {
        // Try to extract JSON from response
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        // If no valid JSON found, wrap the response
        return [
            'raw_response' => $content,
            'error' => 'Could not parse JSON from AI response',
        ];
    }

    /**
     * Get business context for AI
     */
    protected function getBusinessContext(): array
    {
        return [
            'business_name' => $this->business->name,
            'business_type' => $this->business->business_type ?? 'N/A',
            'industry' => $this->business->industry ?? 'N/A',
            'registration_number' => $this->business->rc_number ?? 'N/A',
            'tin' => $this->business->tin ?? 'N/A',
            'annual_revenue' => $this->business->estimated_annual_revenue ?? 'N/A',
            'employee_count' => $this->business->employee_count ?? 0,
            'location' => $this->business->address ?? 'Nigeria',
        ];
    }

    /**
     * Calculate confidence score based on various factors
     */
    protected function calculateConfidence(array $data, array $validations): float
    {
        $totalChecks = 0;
        $passedChecks = 0;

        foreach ($validations as $validation) {
            $totalChecks++;
            if ($validation['passed'] ?? false) {
                $passedChecks++;
            }
        }

        if ($totalChecks === 0) {
            return 0.5; // Default medium confidence if no validations
        }

        return round($passedChecks / $totalChecks, 2);
    }

    /**
     * Log agent activity
     */
    protected function logActivity(string $action, ?array $data = null, ?string $result = null): void
    {
        Log::info("{$this->getName()} - {$action}", [
            'business_id' => $this->business->id,
            'business_name' => $this->business->name,
            'data' => $data,
            'result' => $result,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
