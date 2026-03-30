<?php

namespace App\Services\EInvoice;

use phpseclib3\Crypt\EC;
use phpseclib3\Crypt\PublicKeyLoader;

class ECDSASignatureService
{
    /**
     * Sign invoice data using ECDSA (P-256)
     * Returns base64-encoded signature
     */
    public static function sign(string $data, string $privateKeyPem): string
    {
        $private = PublicKeyLoader::load($privateKeyPem)->withHash('sha256');
        $signature = $private->sign($data);
        return base64_encode($signature);
    }

    /**
     * Verify ECDSA signature
     */
    public static function verify(string $data, string $signature, string $publicKeyPem): bool
    {
        $public = PublicKeyLoader::load($publicKeyPem)->withHash('sha256');
        return $public->verify($data, base64_decode($signature));
    }
}
