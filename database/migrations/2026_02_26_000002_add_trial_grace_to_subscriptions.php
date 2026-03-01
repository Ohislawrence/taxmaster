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
            // Trial period
            $table->integer('trial_days')->default(14)->after('status');
            $table->timestamp('trial_ends_at')->nullable()->after('trial_days');

            // Grace period for failed payments
            $table->integer('grace_days')->default(3)->after('trial_ends_at');
            $table->timestamp('grace_ends_at')->nullable()->after('grace_days');

            // Payment failure tracking
            $table->integer('payment_failures')->default(0)->after('grace_ends_at');
            $table->timestamp('last_payment_failure_at')->nullable()->after('payment_failures');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'trial_days',
                'trial_ends_at',
                'grace_days',
                'grace_ends_at',
                'payment_failures',
                'last_payment_failure_at',
            ]);
        });
    }
};
