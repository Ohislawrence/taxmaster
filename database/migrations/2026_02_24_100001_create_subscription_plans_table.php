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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Basic, Professional, Enterprise
            $table->string('slug')->unique(); // basic, professional, enterprise
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('annual_price', 10, 2)->default(0);
            $table->integer('max_staff_members')->default(1);
            $table->integer('max_returns_per_year')->default(12);
            $table->integer('storage_gb')->default(5);
            $table->boolean('ai_analysis_included')->default(false);
            $table->boolean('payment_automation')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->boolean('custom_branding')->default(false);
            $table->json('features')->nullable(); // Array of features
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
