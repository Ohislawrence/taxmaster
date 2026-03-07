<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * NDPA 2023 compliance: encrypt PII columns at rest.
     *
     * Converts columns to TEXT type to accommodate encrypted values
     * (encrypted strings are significantly longer than plaintext).
     * The actual encryption/decryption is handled by Laravel's 'encrypted' cast
     * on the model, using the APP_KEY.
     *
     * WARNING: After running this migration, run the artisan command
     * `php artisan app:encrypt-existing-pii` to re-save existing records.
     */
    public function up(): void
    {
        // Drop unique indexes first — encrypted TEXT values cannot be indexed
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropUnique('businesses_tax_identification_number_unique');
            $table->dropUnique('businesses_registration_number_unique');
        });

        // Business PII columns — convert to TEXT for encrypted storage
        Schema::table('businesses', function (Blueprint $table) {
            $table->text('tax_identification_number')->nullable()->change();
            $table->text('registration_number')->nullable()->change();
        });

        // Staff PII columns
        Schema::table('business_staff', function (Blueprint $table) {
            $table->text('tax_identification_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('tax_identification_number', 50)->nullable()->change();
            $table->string('registration_number', 100)->nullable()->change();
        });

        // Re-add unique indexes
        Schema::table('businesses', function (Blueprint $table) {
            $table->unique('tax_identification_number');
            $table->unique('registration_number');
        });

        Schema::table('business_staff', function (Blueprint $table) {
            $table->string('tax_identification_number', 50)->nullable()->change();
        });
    }
};
