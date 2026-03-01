<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Artisan;

class AiSettingsController extends Controller
{
    public function __construct()
    {
       //this->middleware('auth');
       //this->middleware('admin');
    }

    /**
     * Display AI settings page
     */
    public function index()
    {
        $settings = [
            'ai_provider' => env('AI_PROVIDER', 'deepseek'),
            'deepseek_api_key' => env('DEEPSEEK_API_KEY') ? '***' . substr(env('DEEPSEEK_API_KEY'), -4) : null,
            'gemini_api_key' => env('GEMINI_API_KEY') ? '***' . substr(env('GEMINI_API_KEY'), -4) : null,
            'ai_enabled' => env('AI_ENABLED', true),
        ];

        return Inertia::render('Admin/AiSettings/Index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update AI settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'ai_provider' => 'required|in:deepseek,gemini',
            'deepseek_api_key' => 'nullable|string|min:10',
            'gemini_api_key' => 'nullable|string|min:10',
            'ai_enabled' => 'required|boolean',
        ]);

        // Prepare .env content updates
        $envUpdates = [];

        // Update provider
        $envUpdates['AI_PROVIDER'] = $validated['ai_provider'];
        $envUpdates['AI_ENABLED'] = $validated['ai_enabled'] ? 'true' : 'false';

        // Update keys only if provided (not just masked values)
        if ($validated['deepseek_api_key'] && strpos($validated['deepseek_api_key'], '***') === false) {
            $envUpdates['DEEPSEEK_API_KEY'] = $validated['deepseek_api_key'];
        }

        if ($validated['gemini_api_key'] && strpos($validated['gemini_api_key'], '***') === false) {
            $envUpdates['GEMINI_API_KEY'] = $validated['gemini_api_key'];
        }

        // Update .env file
        $this->updateEnvFile($envUpdates);

        // Clear config cache so new values are loaded
        Artisan::call('config:cache');

        return back()->with('success', 'AI settings updated successfully. Configuration refreshed.');
    }

    /**
     * Test AI connection
     */
    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|in:deepseek,gemini',
        ]);

        try {
            $provider = $validated['provider'];

            if ($provider === 'deepseek') {
                $apiKey = env('DEEPSEEK_API_KEY');
                if (!$apiKey) {
                    return response()->json(['success' => false, 'message' => 'Deepseek API key not configured']);
                }

                // Test Deepseek connection
                $response = $this->testDeepseekConnection($apiKey);
            } else {
                $apiKey = env('GEMINI_API_KEY');
                if (!$apiKey) {
                    return response()->json(['success' => false, 'message' => 'Gemini API key not configured']);
                }

                // Test Gemini connection
                $response = $this->testGeminiConnection($apiKey);
            }

            if ($response) {
                return response()->json(['success' => true, 'message' => "$provider connection successful"]);
            } else {
                return response()->json(['success' => false, 'message' => "Failed to connect to $provider"]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Update .env file with key-value pairs
     */
    private function updateEnvFile(array $updates)
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return false;
        }

        $content = file_get_contents($envPath);

        foreach ($updates as $key => $value) {
            $pattern = '/^' . preg_quote($key) . '=.*/m';

            if (preg_match($pattern, $content)) {
                // Update existing key
                $content = preg_replace($pattern, "$key=$value", $content);
            } else {
                // Add new key
                $content .= "\n$key=$value";
            }
        }

        file_put_contents($envPath, $content);

        return true;
    }

    /**
     * Test Deepseek API connection
     */
    private function testDeepseekConnection(string $apiKey)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.deepseek.com/chat/completions',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                ),
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'deepseek-chat',
                    'messages' => [
                        ['role' => 'user', 'content' => 'test']
                    ],
                    'max_tokens' => 10,
                ]),
            ));

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            return $httpCode === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Test Gemini API connection
     */
    private function testGeminiConnection(string $apiKey)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $apiKey,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                ),
                CURLOPT_POSTFIELDS => json_encode([
                    'contents' => [
                        ['parts' => [['text' => 'test']]]
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => 10,
                    ]
                ]),
            ));

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            return $httpCode === 200;
        } catch (\Exception $e) {
            return false;
        }
    }
}
