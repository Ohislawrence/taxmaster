<?php
/**
 * Test the CORRECT Mono API endpoint
 * The documentation shows it's /v2/accounts/auth (plural)
 */

require 'vendor/autoload.php';

use GuzzleHttp\Client;

$secretKey = 'test_sk_mda48kk7ytfa9syjtt5n';
$baseUrl = 'https://api.withmono.com';

echo "=== TESTING CORRECT MONO ENDPOINT ===\n\n";

echo "Documentation says: POST /v2/accounts/auth (plural 'accounts')\n";
echo "We were testing: POST /v2/account/auth (singular 'account')\n\n";

$client = new Client(['timeout' => 10, 'verify' => false]);

// Test the CORRECT endpoint from documentation
$correctEndpoint = '/v2/accounts/auth';  // ← PLURAL!

echo "Testing: {$correctEndpoint}\n";

try {
    $response = $client->post($baseUrl . $correctEndpoint, [
        'headers' => [
            'mono-sec-key' => $secretKey,
            'Content-Type' => 'application/json',
            'accept' => 'application/json',
        ],
        'json' => [
            'code' => 'test_code_123'
        ],
    ]);

    $status = $response->getStatusCode();
    $body = $response->getBody()->getContents();

    echo "✓ SUCCESS - Status: {$status}\n";
    echo "Response:\n{$body}\n";

} catch (\GuzzleHttp\Exception\RequestException $e) {
    $response = $e->getResponse();
    $status = $response ? $response->getStatusCode() : 'N/A';
    $body = $response ? $response->getBody()->getContents() : $e->getMessage();

    if ($status == 404) {
        echo "✗ FAILED - Still 404!\n";
        echo "Response: {$body}\n";
    } elseif ($status == 400 || $status == 422) {
        echo "✓ ENDPOINT EXISTS! (Got validation error, which is expected)\n";
        echo "Status: {$status}\n";
        echo "Response: {$body}\n";
    } elseif ($status == 401 || $status == 403) {
        echo "✓ ENDPOINT EXISTS! (Got auth error)\n";
        echo "Status: {$status}\n";
        echo "Response: {$body}\n";
    } else {
        echo "⚠ Status: {$status}\n";
        echo "Response: {$body}\n";
    }
}

echo "\n=== CONCLUSION ===\n";
echo "If we get 400/422 instead of 404, the endpoint is correct!\n";
echo "Invalid test code is expected - we just need to confirm endpoint exists.\n";
?>
