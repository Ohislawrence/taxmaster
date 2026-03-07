<?php

namespace App\Services\EInvoice;

class IRNGenerator
{
    /**
     * Generate a unique Invoice Reference Number (IRN)
     * Combines internal invoice number, seller TIN, and NRS credentials
     */
    public static function generate(string $invoiceNumber, string $sellerTIN, string $nrsCredential): string
    {
        // Example: hash of invoiceNumber + sellerTIN + NRS credential
        return strtoupper(hash('sha256', $invoiceNumber . $sellerTIN . $nrsCredential));
    }
}
