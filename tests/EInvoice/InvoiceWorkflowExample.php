<?php

use App\Services\EInvoice\UBLInvoice;
use App\Services\EInvoice\IRNGenerator;
use App\Services\EInvoice\ECDSASignatureService;
use App\Services\EInvoice\JAdESSignatureService;

// Sample data (replace with real values in production)
$invoiceData = [
    'invoiceNumber' => 'INV-2026-001',
    'issueDate' => date('Y-m-d'),
    'sellerName' => 'Acme Ltd',
    'sellerTIN' => '1234567890',
    'buyerName' => 'Beta Corp',
    'buyerTIN' => '0987654321',
    'totalAmount' => 100000.00,
    'vatAmount' => 7500.00,
    'currency' => 'NGN',
    // ...add all other required UBL fields
];

$nrsCredential = 'NRS-SECRET-KEY'; // Replace with real NRS credential

// 1. Generate IRN
$invoiceData['irn'] = IRNGenerator::generate($invoiceData['invoiceNumber'], $invoiceData['sellerTIN'], $nrsCredential);

// 2. Create UBL invoice object
$ublInvoice = new UBLInvoice($invoiceData);

// 3. Sign invoice (ECDSA)
$privateKeyPem = file_get_contents(__DIR__.'/ecdsa_private.pem'); // Path to your ECDSA private key
$invoiceJson = json_encode($ublInvoice->toArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$signature = ECDSASignatureService::sign($invoiceJson, $privateKeyPem);

// 4. Wrap signature in JAdES structure
$jadesInvoice = JAdESSignatureService::wrap($signature, $ublInvoice->toArray());

// Output result
print_r($jadesInvoice);
