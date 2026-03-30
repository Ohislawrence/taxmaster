<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add fields to support WHT Regulations 2024 requirement:
     * "Where a supplier has no Tax Identification Number (TIN),
     * the applicable WHT rate shall be twice the rate specified in the schedule."
     */
    public function up(): void
    {
        Schema::table('wht_transactions', function (Blueprint $table) {
            // Track if vendor TIN was validated with FIRS
            $table->boolean('tin_validated')->default(false)->after('vendor_tin');

            // Track if double rate was applied (per WHT Regulations 2024)
            $table->boolean('is_double_rate')->default(false)->after('wht_rate');

            // Store original rate before doubling (for transparency)
            $table->decimal('original_rate', 5, 2)->nullable()->after('wht_rate');

            $table->index('is_double_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wht_transactions', function (Blueprint $table) {
            $table->dropIndex(['is_double_rate']);
            $table->dropColumn(['tin_validated', 'is_double_rate', 'original_rate']);
        });
    }
};
