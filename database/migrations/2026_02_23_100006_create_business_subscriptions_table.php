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
        Schema::create('business_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->string('plan_type'); // basic, professional, enterprise
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('annual_price', 10, 2)->nullable();
            $table->integer('max_staff_members');
            $table->integer('max_returns_per_year');
            $table->boolean('ai_analysis_included')->default(true);
            $table->boolean('payment_automation')->default(false);
            $table->enum('billing_cycle', ['monthly', 'annual'])->default('monthly');
            $table->enum('status', ['active', 'inactive', 'cancelled', 'past_due'])->default('active');
            $table->timestamp('started_at');
            $table->timestamp('renews_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('business_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_subscriptions');
    }
};
