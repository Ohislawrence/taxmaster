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
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->integer('max_bank_accounts')->default(0)->after('max_returns_per_year');
        });

        // Set default limits per plan
        \DB::table('subscription_plans')->where('slug', 'free')->update(['max_bank_accounts' => 0]);
        \DB::table('subscription_plans')->where('slug', 'basic')->update(['max_bank_accounts' => 1]);
        \DB::table('subscription_plans')->where('slug', 'professional')->update(['max_bank_accounts' => 3]);
        \DB::table('subscription_plans')->where('slug', 'enterprise')->update(['max_bank_accounts' => 999]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('max_bank_accounts');
        });
    }
};
