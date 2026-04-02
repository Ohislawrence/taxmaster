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
        Schema::create('ai_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Workflow identification
            $table->string('workflow_type'); // 'monthly_vat', 'monthly_paye', 'monthly_wht', 'annual_return', 'quarterly_vat'
            $table->string('tax_period'); // e.g., '2026-03', '2026-Q1', '2026'
            $table->string('reference')->unique(); // e.g., 'WF-VAT-202603-001'

            // Status tracking
            $table->enum('status', [
                'pending',
                'running',
                'awaiting_review',
                'completed',
                'failed',
                'cancelled'
            ])->default('pending');

            // Progress tracking
            $table->integer('total_steps')->default(0);
            $table->integer('completed_steps')->default(0);
            $table->string('current_step')->nullable();
            $table->integer('progress_percentage')->default(0);

            // AI decisions and reasoning
            $table->json('ai_decisions')->nullable(); // Store all AI agent decisions
            $table->json('confidence_scores')->nullable(); // Confidence for each step
            $table->json('warnings')->nullable(); // AI-detected issues
            $table->json('recommendations')->nullable(); // AI suggestions

            // Input and output data
            $table->json('input_data')->nullable(); // Initial data provided to workflow
            $table->json('output_data')->nullable(); // Final results
            $table->json('context')->nullable(); // Business context, preferences, etc.

            // Execution details
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('execution_time_seconds')->nullable();
            $table->string('ai_provider')->default('deepseek'); // deepseek, gemini, gpt4, etc.

            // Error handling
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();
            $table->integer('retry_count')->default(0);

            // Review and approval
            $table->boolean('requires_human_review')->default(false);
            $table->boolean('auto_submitted')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();

            // Related entities
            $table->nullableMorphs('related_entity'); // Can link to TaxReturn, Invoice, etc.

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['business_id', 'workflow_type', 'status']);
            $table->index(['business_id', 'tax_period']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_workflows');
    }
};
