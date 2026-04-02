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
        Schema::create('ai_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_workflow_id')->constrained()->onDelete('cascade');
            
            // Step identification
            $table->integer('step_number'); // 1, 2, 3...
            $table->string('step_name'); // 'collect_transactions', 'calculate_vat', 'validate_return'
            $table->string('agent_name'); // 'vat_agent', 'paye_agent', 'compliance_agent'
            $table->text('description')->nullable();
            
            // Status
            $table->enum('status', [
                'pending',
                'running',
                'completed',
                'failed',
                'skipped'
            ])->default('pending');
            
            // Execution details
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('execution_time_seconds')->nullable();
            
            // AI interaction
            $table->longText('prompt')->nullable(); // The prompt sent to AI
            $table->longText('ai_response')->nullable(); // Raw AI response
            $table->json('parsed_response')->nullable(); // Structured AI response
            $table->decimal('confidence_score', 5, 2)->nullable(); // 0.00 to 100.00
            
            // Step results
            $table->json('input_data')->nullable(); // Data received from previous step
            $table->json('output_data')->nullable(); // Data to pass to next step
            $table->json('validations')->nullable(); // Validation results
            $table->json('warnings')->nullable(); // Step-specific warnings
            
            // Error handling
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();
            $table->integer('retry_count')->default(0);
            
            // Metadata
            $table->string('ai_model')->nullable(); // deepseek-chat, gemini-pro, gpt-4
            $table->integer('tokens_used')->nullable();
            $table->decimal('cost', 10, 4)->nullable(); // Cost in NGN
            
            $table->timestamps();
            
            // Indexes
            $table->index(['ai_workflow_id', 'step_number']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_workflow_steps');
    }
};
