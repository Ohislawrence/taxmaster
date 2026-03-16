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
        // Add created_by_accountant_id and billing flag.
        // NOTE: making `owner_id` nullable via `change()` requires the doctrine/dbal package
        // which may not be available in all environments. To keep this migration safe
        // during tests and CI, we only add the new columns here. Making `owner_id`
        // nullable can be performed separately if doctrine/dbal is installed.
        Schema::table('businesses', function (Blueprint $table) {
            if (!Schema::hasColumn('businesses', 'created_by_accountant_id')) {
                $table->foreignId('created_by_accountant_id')->nullable()->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('businesses', 'billing_managed_by_platform')) {
                $table->boolean('billing_managed_by_platform')->default(true)->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('billing_managed_by_platform');
            $table->dropConstrainedForeignId('created_by_accountant_id');
            $table->foreignId('owner_id')->nullable(false)->change();
        });
    }
};
