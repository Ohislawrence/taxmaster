<?php

/**
 * Test Mono API Connection
 * Run with: php test-mono-api.php
 */

// Test Mono API credentials
$secretKey = 'test_sk_mda48kk7ytfa9syjtt5n';
$publicKey = 'test_pk_k5li1ky66bw9sxuxgnin';
$baseUrl = 'https://api.withmono.com';

echo "=== Mono API Connection Test ===\n\n";
echo "Testing with API Keys:\n";
echo "- Secret Key: " . substr($secretKey, 0, 10) . "...\n";
echo "- Public Key: " . substr($publicKey, 0, 10) . "...\n";
echo "- Base URL: $baseUrl\n\n";

// Test 1: Verify credentials
echo "Test 1: Verify credentials are set\n";
echo str_repeat("-", 50) . "\n";
if (!$secretKey) {
    echo "❌ ERROR: Mono API secret key is not configured.\n";
} else {
    echo "✓ Secret key is configured\n";
}

if (!$publicKey) {
    echo "❌ ERROR: Mono API public key is not configured.\n";
} else {
    echo "✓ Public key is configured\n";
}

// Test 2: Test API Health endpoint
echo "\n\nTest 2: Test Mono API Health\n";
echo str_repeat("-", 50) . "\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$baseUrl/health");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "❌ Connection Error: $error\n";
    } else {
        echo "HTTP Status: $httpCode\n";
        if ($httpCode === 200) {
            echo "✓ API is reachable and healthy\n";
            if ($response) {
                echo "Response: " . substr($response, 0, 100) . "\n";
            }
        } else {
            echo "⚠ API returned status $httpCode\n";
            if ($response) {
                echo "Response: " . substr($response, 0, 150) . "\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

// Test 3: Test authentication with Mono API (without account code)
echo "\n\nTest 3: Test Mono API Authentication Headers\n";
echo str_repeat("-", 50) . "\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$baseUrl/account/auth");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'code' => 'test_invalid_code_12345',
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'mono-sec-key: ' . $secretKey,
        'Content-Type: application/json',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "❌ Connection Error: $error\n";
    } else {
        echo "HTTP Status: $httpCode\n";
        if ($httpCode === 401) {
            echo "❌ Authentication Failed: Invalid secret key or unauthorized\n";
            if ($response) {
                $decoded = json_decode($response, true);
                if ($decoded) {
                    echo "Error: " . ($decoded['message'] ?? $decoded['error'] ?? $response) . "\n";
                } else {
                    echo "Response: " . substr($response, 0, 150) . "\n";
                }
            }
        } else if ($httpCode === 400 || $httpCode === 422) {
            echo "✓ API authentication header accepted (invalid code as expected)\n";
            if ($response) {
                $decoded = json_decode($response, true);
                if ($decoded) {
                    echo "Error Message: " . ($decoded['message'] ?? json_encode($decoded)) . "\n";
                } else {
                    echo "Response: " . substr($response, 0, 150) . "\n";
                }
            }
        } else if ($httpCode === 200) {
            echo "✓ API authenticated successfully\n";
            if ($response) {
                echo "Response: " . substr($response, 0, 200) . "\n";
            }
        } else {
            echo "⚠ Unexpected status: $httpCode\n";
            if ($response) {
                echo "Response: " . substr($response, 0, 150) . "\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\nSummary:\n";
echo "- If Test 1 passes: credentials are in .env\n";
echo "- If Test 2 passes: Mono API is reachable\n";
echo "- If Test 3 shows 400/422: API accepts your secret key format\n";
echo "- If Test 3 shows 401: Secret key is invalid or expired\n";
?>
