<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds return_type column to paye_returns to support annual Form H1 returns.
     */
    public function up(): void
    {
        Schema::table('paye_returns', function (Blueprint $table) {
            $table->string('return_type', 20)->default('monthly')->after('period');
            // return_type: 'monthly' for regular monthly remittance, 'annual' for Form H1
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paye_returns', function (Blueprint $table) {
            $table->dropColumn('return_type');
        });
    }
};
