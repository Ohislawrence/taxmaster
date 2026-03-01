<?php

/**
 * Extended Mono API Test - Try alternative endpoints
 */

$secretKey = 'test_sk_mda48kk7ytfa9syjtt5n';
$publicKey = 'test_pk_k5li1ky66bw9sxuxgnin';
$baseUrl = 'https://api.withmono.com';

echo "=== Extended Mono API Test ===\n\n";

// Function to test endpoints
function testEndpoint($method, $url, $headers, $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'error' => $error,
        'body' => $response,
    ];
}

$testEndpoints = [
    'Health Check' => [
        'method' => 'GET',
        'url' => "$baseUrl/health",
        'headers' => ['Content-Type: application/json'],
    ],
    'Account Auth (v1)' => [
        'method' => 'POST',
        'url' => "$baseUrl/account/auth",
        'headers' => [
            'mono-sec-key: ' . $secretKey,
            'Content-Type: application/json',
        ],
        'data' => ['code' => 'test'],
    ],
    'Account Auth (v2)' => [
        'method' => 'POST',
        'url' => "$baseUrl/api/v1/account/auth",
        'headers' => [
            'mono-sec-key: ' . $secretKey,
            'Content-Type: application/json',
        ],
        'data' => ['code' => 'test'],
    ],
    'List Accounts' => [
        'method' => 'GET',
        'url' => "$baseUrl/accounts",
        'headers' => [
            'mono-sec-key: ' . $secretKey,
            'Content-Type: application/json',
        ],
    ],
    'API Status' => [
        'method' => 'GET',
        'url' => "$baseUrl/status",
        'headers' => ['Content-Type: application/json'],
    ],
];

foreach ($testEndpoints as $name => $test) {
    echo "Testing: $name\n";
    echo str_repeat("-", 60) . "\n";
    echo "Method: {$test['method']}\n";
    echo "URL: {$test['url']}\n";

    $result = testEndpoint(
        $test['method'],
        $test['url'],
        $test['headers'],
        $test['data'] ?? null
    );

    echo "Status: {$result['status']}\n";

    if ($result['error']) {
        echo "❌ Error: {$result['error']}\n";
    } else {
        if ($result['status'] === 200) {
            echo "✓ Success\n";
            if ($result['body']) {
                echo "Response: " . substr($result['body'], 0, 100) . "\n";
            }
        } else if ($result['status'] === 401) {
            echo "❌ Unauthorized - Invalid API key\n";
        } else if ($result['status'] === 404) {
            echo "⚠ Not Found - Endpoint may not exist\n";
        } else if ($result['status'] === 400 || $result['status'] === 422) {
            echo "⚠ Bad Request - Endpoint exists, invalid data\n";
            if ($result['body']) {
                $decoded = json_decode($result['body'], true);
                echo "Message: " . ($decoded['message'] ?? substr($result['body'], 0, 100)) . "\n";
            }
        } else {
            echo "⚠ Status: {$result['status']}\n";
        }
    }

    echo "\n";
}

echo str_repeat("=", 60) . "\n";
echo "Results Summary:\n";
echo "1. If any 200 status: API is reachable\n";
echo "2. If any 400/422 status: Endpoint exists but needs different input\n";
echo "3. If all 404: API endpoint paths may be incorrect\n";
echo "4. If all timeout: Network/firewall issue or API is down\n";
?>
