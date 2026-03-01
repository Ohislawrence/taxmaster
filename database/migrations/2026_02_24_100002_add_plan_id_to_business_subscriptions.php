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
        Schema::table('business_subscriptions', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('business_id')->constrained('subscription_plans')->onDelete('set null');
            $table->string('payment_status')->default('pending')->after('status'); // pending, completed, failed
            $table->string('payment_method')->nullable()->after('payment_status'); // paystack, bank_transfer
            $table->string('transaction_reference')->nullable()->unique()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['plan_id', 'payment_status', 'payment_method', 'transaction_reference']);
        });
    }
};
