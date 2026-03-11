<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Exception;

class PublicAiAgentService
{
    protected $client;
    protected $provider;

    public function __construct(?string $provider = null)
    {
        $this->provider = $provider ?? env('AI_PROVIDER', 'deepseek');
        $this->client = new Client();
    }

    protected function getApiKey(): ?string
    {
        if ($this->provider === 'deepseek') {
            return env('DEEPSEEK_API_KEY');
        } elseif ($this->provider === 'gemini') {
            return env('GEMINI_API_KEY');
        }
        return null;
    }

    protected function getModel(): string
    {
        if ($this->provider === 'deepseek') {
            return 'deepseek-chat';
        }
        return 'gemini-pro';
    }

    public function callAiApi(string $prompt): array
    {
        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return [
                'success' => false,
                'error' => 'AI provider API key not configured',
            ];
        }
        if ($this->provider === 'deepseek') {
            return $this->callDeepseekApi($prompt, $apiKey);
        } elseif ($this->provider === 'gemini') {
            return $this->callGeminiApi($prompt, $apiKey);
        }
        return [
            'success' => false,
            'error' => 'Unknown AI provider',
        ];
    }

    protected function callDeepseekApi(string $prompt, string $apiKey): array
    {
        try {
            $response = $this->client->post('https://api.deepseek.com/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                // Fix: verify should be false for local/dev, true for production
                'verify' => env('APP_ENV') === 'production',
                'json' => [
                    'model' => $this->getModel(),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are TaxMaster AI, a helpful assistant for Nigerian tax questions.',
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
                    'message' => $result['choices'][0]['message']['content'],
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

    protected function callGeminiApi(string $prompt, string $apiKey): array
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
                    'message' => $result['candidates'][0]['content']['parts'][0]['text'],
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
}
