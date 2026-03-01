<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_subscriptions', function (Blueprint $table) {
            // Payment recovery fields
            $table->float('discount_percentage')->default(0)->after('payment_failures');
            $table->timestamp('discount_applied_at')->nullable()->after('discount_percentage');
            $table->json('payment_plan')->nullable()->after('discount_applied_at');
            $table->timestamp('payment_plan_started_at')->nullable()->after('payment_plan');
            $table->timestamp('suspended_at')->nullable()->after('payment_plan_started_at');

            // Transaction categorization tracking
            $table->integer('auto_categorized_count')->default(0)->after('suspended_at');
            $table->timestamp('last_auto_categorization_at')->nullable()->after('auto_categorized_count');
        });
    }

    public function rollback(): void
    {
        Schema::table('business_subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'discount_percentage',
                'discount_applied_at',
                'payment_plan',
                'payment_plan_started_at',
                'suspended_at',
                'auto_categorized_count',
                'last_auto_categorization_at',
            ]);
        });
    }
};
