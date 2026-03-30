<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add VAT exempt tracking per Nigerian VAT Act and Finance Acts 2019/2020
     * Reference: FIRS VAT exempt goods and services list
     */
    public function up(): void
    {
        // Add VAT exempt fields to businesses
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('is_vat_exempt')->default(false)->after('tax_identification_number');
            $table->string('vat_exempt_category')->nullable()->after('is_vat_exempt');
            $table->text('vat_exempt_reason')->nullable()->after('vat_exempt_category');

            $table->index('is_vat_exempt');
        });

        // Add VAT exempt fields to transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('vat_exempt')->default(false)->after('vat_applicable');
            $table->string('vat_exempt_category')->nullable()->after('vat_exempt');

            $table->index('vat_exempt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropIndex(['is_vat_exempt']);
            $table->dropColumn(['is_vat_exempt', 'vat_exempt_category', 'vat_exempt_reason']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['vat_exempt']);
            $table->dropColumn(['vat_exempt', 'vat_exempt_category']);
        });
    }
};
