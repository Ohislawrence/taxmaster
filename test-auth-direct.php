<?php
/**
 * Direct test of /account/auth endpoint as documented
 */

require 'vendor/autoload.php';

use GuzzleHttp\Client;

$secretKey = 'test_sk_mda48kk7ytfa9syjtt5n';
$baseUrl = 'https://api.withmono.com';

echo "Testing POST /account/auth endpoint (as per documentation)\n";
echo "Endpoint: $baseUrl/account/auth\n";
echo "Header: mono-sec-key: $secretKey\n\n";

$client = new Client([
    'timeout' => 15,
    'verify' => false,
]);

try {
    $response = $client->post('https://api.withmono.com/account/auth', [
        'headers' => [
            'mono-sec-key' => $secretKey,
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'code' => 'test_code'
        ],
    ]);

    echo "✓ SUCCESS - Status: " . $response->getStatusCode() . "\n";
    echo "Response:\n";
    echo $response->getBody()->getContents() . "\n";

} catch (\GuzzleHttp\Exception\RequestException $e) {
    $response = $e->getResponse();
    $statusCode = $response ? $response->getStatusCode() : 'N/A';
    $body = $response ? $response->getBody()->getContents() : $e->getMessage();

    echo "✗ FAILED - Status: $statusCode\n";
    echo "Response:\n";
    echo $body . "\n";
    echo "\nFull Error:\n";
    echo $e->getMessage() . "\n";
}
?>
