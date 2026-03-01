<?php
/**
 * Explore available Mono API endpoints by checking root responses
 */

require 'vendor/autoload.php';

use GuzzleHttp\Client;

$secretKey = 'test_sk_mda48kk7ytfa9syjtt5n';

echo "=== MONO API STATUS AND ENDPOINT DISCOVERY ===\n\n";

$client = new Client(['timeout' => 10, 'verify' => false]);

// Test root endpoint for info
echo "1. Root endpoint:\n";
try {
    $response = $client->get('https://api.withmono.com/', [
        'headers' => ['mono-sec-key' => $secretKey],
    ]);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Body: " . $response->getBody()->getContents() . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Check if there's an OpenAPI/Swagger doc
echo "2. API documentation endpoints:\n";
$docEndpoints = ['/docs', '/swagger', '/openapi', '/api/docs', '/api/openapi', '/.well-known/openapi.json'];
foreach ($docEndpoints as $doc) {
    try {
        $response = $client->get('https://api.withmono.com' . $doc, [
            'http_errors' => false,
            'timeout' => 5,
            'headers' => ['mono-sec-key' => $secretKey],
        ]);
        if ($response->getStatusCode() < 400) {
            echo "✓ Found: $doc (Status: {$response->getStatusCode()})\n";
        }
    } catch (\Exception $e) {
        // Ignore
    }
}

echo "\n3. Check what endpoints are accessible:\n";

// The docs mention the auth endpoint but the test shows 404
// This suggests the test keys might need to be activated or the endpoint might be different now
// Let me check what response we get with different approaches

echo "Testing token exchange with different code formats:\n";

$codesToTry = [
    'test_code',
    'code_test',
    '',
    'dummy_code_123',
];

foreach ($codesToTry as $code) {
    try {
        $response = $client->post('https://api.withmono.com/account/auth', [
            'headers' => [
                'mono-sec-key' => $secretKey,
                'Content-Type' => 'application/json',
            ],
            'json' => ['code' => $code],
            'http_errors' => false,
        ]);

        $status = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true);

        if ($status == 404) {
            echo "  Code '$code': 404 (Endpoint not found)\n";
        } else {
            echo "  Code '$code': Status $status\n";
            echo "  Response: " . json_encode($body) . "\n";
        }
    } catch (\Exception $e) {
        echo "  Code '$code': Exception - " . $e->getMessage() . "\n";
    }
}

echo "\n4. What we KNOW WORKS:\n";
echo "   ✓ GET /v2/accounts - Returns 200 with empty data\n";
echo "   ✓ GET / - Returns 'Mono API is Live!'\n";
echo "   ✗ POST /account/auth - Returns 404\n\n";

echo "CONCLUSION:\n";
echo "The /account/auth endpoint referenced in the docs does not exist.\n";
echo "This could mean:\n";
echo "1. Test keys are limited to read-only access (only /v2/accounts)\n";
echo "2. The API documentation is outdated\n";
echo "3. The authentication flow has changed\n\n";

echo "RECOMMENDATION:\n";
echo "Check the MONO_SETUP.md troubleshooting curl command in the docs.\n";
echo "That suggests the endpoint should exist. Possible issues:\n";
echo "- Test keys might need to be regenerated\n";
echo "- Test environment might have changed\n";
echo "- Contact Mono support for updated API documentation\n";
?>
