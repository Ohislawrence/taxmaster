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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('quickbooks_id')->nullable()->after('id');
            $table->timestamp('quickbooks_synced_at')->nullable()->after('quickbooks_id');
            $table->boolean('quickbooks_sync_enabled')->default(true)->after('quickbooks_synced_at');

            $table->index('quickbooks_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['quickbooks_id']);
            $table->dropColumn(['quickbooks_id', 'quickbooks_synced_at', 'quickbooks_sync_enabled']);
        });
    }
};
