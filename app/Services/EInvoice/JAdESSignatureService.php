<?php

namespace App\Services\EInvoice;

/**
 * JAdES-compliant signature wrapper for JSON invoices
 * (Stub for extension: add full JAdES structure as needed)
 */
class JAdESSignatureService
{
    /**
     * Wrap ECDSA signature in JAdES JSON structure
     */
    public static function wrap(string $signature, array $invoiceData): array
    {
        // Build signed fields list: top-level keys + seller subkeys (if present)
        $signedFields = array_keys($invoiceData);
        if (isset($invoiceData['seller']) && is_array($invoiceData['seller'])) {
            foreach ($invoiceData['seller'] as $k => $v) {
                $signedFields[] = 'seller.' . $k;
            }
        }

        return [
            'jades' => [
                'signatureValue' => $signature,
                'algorithm' => 'ES256',
                'signedFields' => $signedFields,
                // ...add more JAdES fields as required
            ],
            'invoice' => $invoiceData,
        ];
    }
}
