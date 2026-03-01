<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update bank_accounts table - mono_access_token will be encrypted at model level
        // Add a flag to indicate encryption status
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->boolean('token_encrypted')->default(false)->after('mono_access_token');
        });

        // Update transactions table - add encryption flag for sensitive data
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('data_encrypted')->default(false)->after('meta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn('token_encrypted');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('data_encrypted');
        });
    }
};
