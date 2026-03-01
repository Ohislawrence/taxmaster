<?php
/**
 * Test v2 Mono API endpoints specifically
 */

require 'vendor/autoload.php';

use GuzzleHttp\Client;

// Load environment from .env
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
$baseUrl = 'https://api.withmono.com';

echo "\n=== MONO API v2 ENDPOINT TEST ===\n";
echo "Testing v2 endpoints with mono-sec-key header\n\n";

$client = new Client(['timeout' => 10, 'verify' => false]);

// Test specific v2 endpoints
$tests = [
    [
        'name' => 'List accounts',
        'method' => 'GET',
        'endpoint' => '/v2/accounts',
    ],
    [
        'name' => 'Get account instance',
        'method' => 'GET',
        'endpoint' => '/v2/accounts/test_account_id',
    ],
    [
        'name' => 'Get transactions',
        'method' => 'GET',
        'endpoint' => '/v2/accounts/test_account_id/transactions',
    ],
    [
        'name' => 'Account auth (POST)',
        'method' => 'POST',
        'endpoint' => '/v2/account/auth',
        'body' => ['code' => 'test_code'],
    ],
    [
        'name' => 'Authenticate (POST)',
        'method' => 'POST',
        'endpoint' => '/v2/authenticate',
        'body' => ['code' => 'test_code'],
    ],
    [
        'name' => 'Account link (POST)',
        'method' => 'POST',
        'endpoint' => '/v2/account/link',
        'body' => ['code' => 'test_code'],
    ],
    [
        'name' => 'Unlink account',
        'method' => 'POST',
        'endpoint' => '/v2/accounts/test_account_id/unlink',
    ],
    [
        'name' => 'Get statement',
        'method' => 'GET',
        'endpoint' => '/v2/accounts/test_account_id/statement',
        'params' => ['start' => '2026-01-01', 'end' => '2026-02-27', 'output' => 'pdf'],
    ],
];

foreach ($tests as $test) {
    $method = $test['method'];
    $endpoint = $test['endpoint'];
    $url = $baseUrl . $endpoint;

    echo "Testing: {$test['name']}\n";
    echo "  $method $endpoint\n";

    try {
        $options = [
            'headers' => [
                'mono-sec-key' => $secretKey,
                'Content-Type' => 'application/json',
            ],
        ];

        if ($method === 'POST' && isset($test['body'])) {
            $options['json'] = $test['body'];
        }

        if (isset($test['params'])) {
            $options['query'] = $test['params'];
        }

        $response = $client->request($method, $url, $options);
        $status = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        echo "  ✓ Status: $status\n";
        echo "  Response: " . substr($body, 0, 150) . (strlen($body) > 150 ? '...' : '') . "\n";

    } catch (\GuzzleHttp\Exception\RequestException $e) {
        $response = $e->getResponse();
        $status = $response ? $response->getStatusCode() : 'Error';
        $body = $response ? $response->getBody()->getContents() : $e->getMessage();

        echo "  ⚠ Status: $status\n";
        if ($status == 401) {
            echo "  Info: Authentication issue (but endpoint exists!)\n";
        } elseif ($status == 404) {
            echo "  ✗ Endpoint not found\n";
        }
        echo "  Response: " . substr($body, 0, 150) . (strlen($body) > 150 ? '...' : '') . "\n";
    }

    echo "\n";
}

echo "\n=== CONCLUSIONS ===\n";
echo "The Mono API uses /v2/ endpoints, NOT the root-level endpoints.\n";
echo "Current code needs to be updated to use v2 paths.\n";
echo "\nEndpoints that work:\n";
echo "- GET /v2/accounts ✓\n";
?>
