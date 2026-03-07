<?php

namespace App\Services\EInvoice;

class EInvoiceService
{
    /**
     * Generate a fully signed, JAdES-compliant UBL invoice
     * @param array $invoiceData - All required UBL fields
     * @param string $nrsCredential - NRS credential/secret
     * @param string $ecdsaPrivateKeyPem - ECDSA private key PEM
     * @return array - JAdES-wrapped invoice
     */
    public static function generateJAdESInvoice(array $invoiceData, string $nrsCredential, string $ecdsaPrivateKeyPem): array
    {
        // 1. Generate IRN
        $invoiceData['irn'] = IRNGenerator::generate($invoiceData['invoiceNumber'], $invoiceData['sellerTIN'], $nrsCredential);

        // 2. Create UBL invoice object
        $ublInvoice = new UBLInvoice($invoiceData);

        // 3. Sign invoice (ECDSA)
        $invoiceJson = json_encode($ublInvoice->toArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $signature = ECDSASignatureService::sign($invoiceJson, $ecdsaPrivateKeyPem);

        // 4. Wrap signature in JAdES structure
        return JAdESSignatureService::wrap($signature, $ublInvoice->toArray());
    }
}
