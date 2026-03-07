<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Encrypts existing plaintext PII values in the database.
 * Uses raw DB queries to avoid triggering Eloquent's 'encrypted' cast
 * on the still-plaintext values.
 *
 * Run after the encrypt_pii_columns migration:
 *   php artisan app:encrypt-existing-pii
 */
class EncryptExistingPii extends Command
{
    protected $signature = 'app:encrypt-existing-pii {--force : Skip confirmation}';

    protected $description = 'Encrypt existing plaintext PII data for NDPA compliance';

    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('This will encrypt all plaintext PII in Business and Staff records. Continue?')) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        $this->info('Encrypting Business PII...');
        $businessCount = 0;

        DB::table('businesses')->orderBy('id')->chunk(100, function ($rows) use (&$businessCount) {
            foreach ($rows as $row) {
                $updates = [];

                if ($row->tax_identification_number && !$this->looksEncrypted($row->tax_identification_number)) {
                    $updates['tax_identification_number'] = Crypt::encryptString($row->tax_identification_number);
                }
                if ($row->registration_number && !$this->looksEncrypted($row->registration_number)) {
                    $updates['registration_number'] = Crypt::encryptString($row->registration_number);
                }

                if (!empty($updates)) {
                    DB::table('businesses')->where('id', $row->id)->update($updates);
                    $businessCount++;
                }
            }
        });

        $this->info("Encrypted {$businessCount} business records.");

        $this->info('Encrypting Staff PII...');
        $staffCount = 0;

        DB::table('business_staff')->orderBy('id')->chunk(100, function ($rows) use (&$staffCount) {
            foreach ($rows as $row) {
                if ($row->tax_identification_number && !$this->looksEncrypted($row->tax_identification_number)) {
                    DB::table('business_staff')->where('id', $row->id)->update([
                        'tax_identification_number' => Crypt::encryptString($row->tax_identification_number),
                    ]);
                    $staffCount++;
                }
            }
        });

        $this->info("Encrypted {$staffCount} staff records.");
        $this->info('PII encryption complete.');

        return self::SUCCESS;
    }

    /**
     * Check if a value looks like it's already encrypted (base64-encoded JSON).
     */
    private function looksEncrypted(string $value): bool
    {
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return false;
        }
        $json = json_decode($decoded, true);
        return is_array($json) && isset($json['iv'], $json['value'], $json['mac']);
    }
}
