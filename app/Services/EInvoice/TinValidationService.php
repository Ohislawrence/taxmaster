<?php

namespace App\Services\EInvoice;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Tax Identification Number (TIN) Validation Service
 * Validates Nigerian TINs according to FIRS standards
 */
class TinValidationService
{
    protected FirsApiService $firsApi;

    public function __construct()
    {
        $this->firsApi = new FirsApiService();
    }

    /**
     * Validate TIN format (basic structural validation)
     *
     * Nigerian TIN format: 10 digits (XXXXXXXXXX-0001)
     * or 14 characters with branch code
     *
     * @param string $tin Tax Identification Number
     * @return bool
     */
    public function validateFormat(string $tin): bool
    {
        // Remove any whitespace
        $tin = trim($tin);

        // Remove hyphens for validation
        $cleanTin = str_replace('-', '', $tin);

        // Basic format: 10-14 digits
        if (!preg_match('/^\d{10,14}$/', $cleanTin)) {
            return false;
        }

        return true;
    }

    /**
     * Validate TIN with FIRS (online verification)
     *
     * @param string $tin Tax Identification Number
     * @param bool $useCache Whether to use cached validation results
     * @return array Validation result
     */
    public function validateWithFirs(string $tin, bool $useCache = true): array
    {
        // Basic format check first
        if (!$this->validateFormat($tin)) {
            return [
                'valid' => false,
                'error' => 'Invalid TIN format',
                'source' => 'format_validation',
            ];
        }

        // Check if FIRS API credentials are configured
        if (!$this->firsApi->hasCredentials()) {
            return [
                'valid' => true,  // Accept format-valid TINs when API unavailable
                'format_only' => true,
                'error' => 'FIRS API credentials not configured. Format validation only.',
                'source' => 'format_validation',
            ];
        }

        // Check cache if enabled
        $cacheKey = "tin_validation_{$tin}";
        if ($useCache) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return array_merge($cached, ['source' => 'cache']);
            }
        }

        // Validate with FIRS API
        $result = $this->firsApi->validateTIN($tin);

        // Cache successful validations for 24 hours
        if ($result['valid']) {
            Cache::put($cacheKey, $result, now()->addHours(24));
        }

        return array_merge($result, ['source' => 'firs_api']);
    }

    /**
     * Validate TIN and return detailed information
     *
     * @param string $tin Tax Identification Number
     * @return array Detailed validation result
     */
    public function validate(string $tin): array
    {
        // Format validation
        $formatValid = $this->validateFormat($tin);

        if (!$formatValid) {
            return [
                'valid' => false,
                'format_valid' => false,
                'firs_verified' => false,
                'error' => 'TIN format is invalid',
                'details' => [
                    'expected_format' => '10-14 digits (e.g., 1234567890-0001)',
                    'provided' => $tin,
                ],
            ];
        }

        // FIRS verification (if enabled)
        if (config('services.firs.enabled', true)) {
            $firsResult = $this->validateWithFirs($tin);

            return [
                'valid' => $firsResult['valid'],
                'format_valid' => true,
                'firs_verified' => $firsResult['valid'],
                'taxpayer_name' => $firsResult['taxpayer_name'] ?? null,
                'taxpayer_type' => $firsResult['taxpayer_type'] ?? null,
                'status' => $firsResult['status'] ?? null,
                'error' => $firsResult['error'] ?? null,
                'source' => $firsResult['source'] ?? 'firs_api',
            ];
        }

        // If FIRS verification is disabled, return format validation only
        return [
            'valid' => true,
            'format_valid' => true,
            'firs_verified' => false,
            'note' => 'FIRS verification is disabled. Only format validation performed.',
        ];
    }

    /**
     * Normalize TIN format
     *
     * @param string $tin Tax Identification Number
     * @return string Normalized TIN
     */
    public function normalize(string $tin): string
    {
        // Remove whitespace
        $tin = trim($tin);

        // Remove existing hyphens
        $tin = str_replace('-', '', $tin);

        // Add hyphen after 10th digit if TIN is longer (branch code)
        if (strlen($tin) > 10) {
            $tin = substr($tin, 0, 10) . '-' . substr($tin, 10);
        }

        return $tin;
    }

    /**
     * Generate TIN checksum (if applicable)
     * Note: This is a placeholder - actual FIRS TIN checksum algorithm may differ
     *
     * @param string $tin Tax Identification Number
     * @return string|null Checksum digit
     */
    protected function generateChecksum(string $tin): ?string
    {
        // Remove any non-digits
        $digits = preg_replace('/\D/', '', $tin);

        if (strlen($digits) < 10) {
            return null;
        }

        // Simple modulo 11 checksum (placeholder - adjust for actual FIRS algorithm)
        $sum = 0;
        $weights = [2, 3, 4, 5, 6, 7, 8, 9, 2, 3];

        for ($i = 0; $i < 10; $i++) {
            $sum += (int)$digits[$i] * $weights[$i];
        }

        $checksum = (11 - ($sum % 11)) % 11;

        return (string)$checksum;
    }

    /**
     * Bulk validate multiple TINs
     *
     * @param array $tins Array of TINs to validate
     * @return array Validation results for each TIN
     */
    public function bulkValidate(array $tins): array
    {
        $results = [];

        foreach ($tins as $tin) {
            $results[$tin] = $this->validate($tin);
        }

        return $results;
    }

    /**
     * Clear cached TIN validation
     *
     * @param string|null $tin Specific TIN to clear, or null to clear all
     * @return bool
     */
    public function clearCache(?string $tin = null): bool
    {
        if ($tin) {
            Cache::forget("tin_validation_{$tin}");
            return true;
        }

        // Clear all TIN validation cache (using pattern matching if available)
        return Cache::flush(); // Note: This clears all cache, consider more targeted approach
    }
}
