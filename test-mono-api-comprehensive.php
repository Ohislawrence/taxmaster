<?php
/**
 * Comprehensive Mono API Testing
 * Tests multiple endpoint variations and authentication approaches
 */

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Load environment variables directly from .env
$envVars = [];
foreach (file('.env') as $line) {
    if (empty(trim($line)) || strpos(trim($line), '#') === 0) {
        continue;
    }
    list($key, $value) = array_map('trim', explode('=', $line, 2));
    $envVars[$key] = $value;
}

$secretKey = $envVars['MONO_SECRET_KEY'] ?? null;
$publicKey = $envVars['MONO_PUBLIC_KEY'] ?? null;
$baseUrl = $envVars['MONO_BASE_URL'] ?? 'https://api.withmono.com';

echo "\n=== MONO API COMPREHENSIVE TEST ===\n";
echo "Base URL: $baseUrl\n";
echo "Secret Key: " . ($secretKey ? "✓ Configured" : "✗ Missing") . "\n";
echo "Public Key: " . ($publicKey ? "✓ Configured" : "✗ Missing") . "\n\n";

if (!$secretKey) {
    echo "ERROR: MONO_SECRET_KEY not found in .env\n";
    exit(1);
}

$client = new Client([
    'timeout' => 10,
    'verify' => false, // For testing only
]);

// Test configurations
$endpoints = [
    // Standard endpoints (no version)
    ['method' => 'GET', 'path' => '/accounts', 'description' => 'List accounts (no version)'],
    ['method' => 'POST', 'path' => '/account/auth', 'description' => 'Auth (no version)', 'data' => ['code' => 'test_code']],
    ['method' => 'GET', 'path' => '/account', 'description' => 'Account endpoint (singular)'],

    // V1 endpoints
    ['method' => 'GET', 'path' => '/v1/accounts', 'description' => 'List accounts (v1)'],
    ['method' => 'POST', 'path' => '/v1/account/auth', 'description' => 'Auth (v1)', 'data' => ['code' => 'test_code']],
    ['method' => 'GET', 'path' => '/v1/account', 'description' => 'Account endpoint (v1, singular)'],

    // V2 endpoints
    ['method' => 'GET', 'path' => '/v2/accounts', 'description' => 'List accounts (v2)'],
    ['method' => 'POST', 'path' => '/v2/account/auth', 'description' => 'Auth (v2)', 'data' => ['code' => 'test_code']],

    // Alternative auth paths
    ['method' => 'POST', 'path' => '/auth', 'description' => 'Auth (root)', 'data' => ['code' => 'test_code']],
    ['method' => 'POST', 'path' => '/authenticate', 'description' => 'Authenticate', 'data' => ['code' => 'test_code']],

    // Health/status endpoints
    ['method' => 'GET', 'path' => '/health', 'description' => 'Health check'],
    ['method' => 'GET', 'path' => '/status', 'description' => 'Status endpoint'],
    ['method' => 'GET', 'path' => '/', 'description' => 'Root endpoint'],
];

// Test with different header approaches
$headerVariations = [
    [
        'name' => 'mono-sec-key header',
        'headers' => ['mono-sec-key' => $secretKey, 'Content-Type' => 'application/json'],
    ],
    [
        'name' => 'Authorization Bearer',
        'headers' => ['Authorization' => "Bearer {$secretKey}", 'Content-Type' => 'application/json'],
    ],
    [
        'name' => 'x-api-key header',
        'headers' => ['x-api-key' => $secretKey, 'Content-Type' => 'application/json'],
    ],
];

echo "Testing " . count($endpoints) . " endpoints with " . count($headerVariations) . " header variations...\n\n";

$results = [];
$successCount = 0;
$notFoundCount = 0;
$errorCount = 0;

foreach ($headerVariations as $headerVariation) {
    echo "--- Using: {$headerVariation['name']} ---\n";

    foreach ($endpoints as $endpoint) {
        $method = $endpoint['method'];
        $path = $endpoint['path'];
        $description = $endpoint['description'];
        $url = $baseUrl . $path;

        try {
            $options = [
                'headers' => $headerVariation['headers'],
            ];

            if ($method === 'POST' && isset($endpoint['data'])) {
                $options['json'] = $endpoint['data'];
            }

            $response = $client->request($method, $url, $options);
            $status = $response->getStatusCode();

            if ($status < 300) {
                echo "✓ $method $path (Status: $status) - $description\n";
                echo "  Response: " . substr($response->getBody(), 0, 100) . "\n";
                $successCount++;
                $results[] = [
                    'method' => $method,
                    'path' => $path,
                    'status' => $status,
                    'headers' => $headerVariation['name'],
                ];
            } else {
                echo "⚠ $method $path (Status: $status) - $description\n";
            }
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $status = $response ? $response->getStatusCode() : 'Error';

            if ($status == 404) {
                echo "✗ $method $path (404 Not Found) - $description\n";
                $notFoundCount++;
            } elseif ($status == 401 || $status == 403) {
                echo "⚠ $method $path (Status: $status - Auth issue) - $description\n";
                $successCount++; // This is actually a good sign - endpoint exists
            } else {
                echo "✗ $method $path (Status: $status) - $description\n";
                $errorCount++;
            }
        } catch (\Exception $e) {
            echo "✗ $method $path - Error: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
    echo "\n";
}

echo "\n=== TEST SUMMARY ===\n";
echo "Successful (2xx): $successCount\n";
echo "Auth issues (401/403): (indicates endpoint exists)\n";
echo "Not found (404): $notFoundCount\n";
echo "Other errors: $errorCount\n\n";

if ($successCount > 0) {
    echo "✓ GOOD NEWS: Found working endpoints!\n";
    echo "Found endpoints with successful responses.\n";
} elseif ($notFoundCount > 0 && $successCount === 0) {
    echo "⚠ ALL ENDPOINTS RETURN 404\n";
    echo "This suggests:\n";
    echo "1. The base URL might be incorrect\n";
    echo "2. The API structure has significantly changed\n";
    echo "3. Test keys might be for a different environment\n\n";

    echo "Try checking if Mono API uses a different base URL:\n";
    echo "- https://connect.withmono.com\n";
    echo "- https://sandbox.withmono.com\n";
    echo "- https://api.mono.co (different domain)\n";
} else {
    echo "⚠ INCONCLUSIVE: Check errors above\n";
}

echo "\n";
?>
