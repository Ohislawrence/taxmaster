<?php
/**
 * Test additional Mono API endpoint variations
 * Looking for auth and webhook endpoints
 */

require 'vendor/autoload.php';

use GuzzleHttp\Client;

$envVars = [];
foreach (file('.env') as $line) {
    if (empty(trim($line)) || strpos(trim($line), '#') === 0) {
        continue;
    }
    list($key, $value) = array_map('trim', explode('=', $line, 2));
    $envVars[$key] = $value;
}

$secretKey = $envVars['MONO_SECRET_KEY'] ?? null;
$baseUrl = 'https://api.withmono.com';

echo "\n=== MONO API ADDITIONAL ENDPOINT DISCOVERY ===\n\n";

$client = new Client(['timeout' => 10, 'verify' => false]);

// Looking at Mono's typical API patterns - auth is often separate from v2 resources
$tests = [
    // Auth endpoints - sometimes not versioned or different path
    [
        'name' => 'Exchange auth code',
        'method' => 'POST',
        'endpoint' => '/account/auth',
        'body' => ['code' => 'test_code'],
    ],
    [
        'name' => 'Exchange auth code v2',
        'method' => 'POST',
        'endpoint' => '/v2/account/auth',
        'body' => ['code' => 'test_code'],
    ],
    [
        'name' => 'Link account',
        'method' => 'POST',
        'endpoint' => '/account/link',
        'body' => ['code' => 'test_code'],
    ],
    [
        'name' => 'Token exchange',
        'method' => 'POST',
        'endpoint' => '/exchange',
        'body' => ['code' => 'test_code'],
    ],
    // Webhook/event endpoints
    [
        'name' => 'Verify webhook signature',
        'method' => 'POST',
        'endpoint' => '/webhook/verify',
        'body' => ['signature' => 'test', 'payload' => 'test'],
    ],
    // Monoctl endpoints (sometimes exposed)
    [
        'name' => 'Monoctl transactions',
        'method' => 'GET',
        'endpoint' => '/v2/monoctl/accounts/test_id/transactions',
    ],
    // Check if data is at different paths
    [
        'name' => 'Data export',
        'method' => 'GET',
        'endpoint' => '/v2/data/transactions',
    ],
];

foreach ($tests as $test) {
    $method = $test['method'];
    $endpoint = $test['endpoint'];

    try {
        $options = [
            'headers' => [
                'mono-sec-key' => $secretKey,
                'Content-Type' => 'application/json',
            ],
        ];

        if ($method === 'POST') {
            $options['json'] = $test['body'] ?? [];
        }

        $response = $client->request($method, $baseUrl . $endpoint, $options);
        $status = $response->getStatusCode();

        echo "✓ {$test['name']}: Status $status ($method $endpoint)\n";

    } catch (\GuzzleHttp\Exception\RequestException $e) {
        $response = $e->getResponse();
        $status = $response ? $response->getStatusCode() : 'Timeout';

        if ($status == 404) {
            echo "✗ {$test['name']}: 404 Not Found ($method $endpoint)\n";
        } elseif ($status == 400) {
            echo "⚠ {$test['name']}: 400 Bad Request - Endpoint exists! ($method $endpoint)\n";
        } else {
            echo "⚠ {$test['name']}: Status $status ($method $endpoint)\n";
        }
    }
}

echo "\n=== NEXT STEPS ===\n";
echo "Based on findings:\n";
echo "1. GET /v2/accounts works with mono-sec-key header\n";
echo "2. Auth endpoint needs to be located (likely POST /account/auth with code)\n";
echo "3. The base URL is correct: https://api.withmono.com\n";
echo "\nCheck Mono API documentation for exact auth endpoint structure.\n";
echo "Reference: https://docs.getmono.co/docs/accounts/linking-accounts\n";
?>
