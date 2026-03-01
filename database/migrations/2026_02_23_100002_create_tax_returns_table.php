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
        Schema::create('tax_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->string('return_type'); // monthly, quarterly, annual
            $table->string('tax_period'); // e.g., 2026-01, 2026-Q1, 2026
            $table->date('submission_date')->nullable();
            $table->date('due_date');
            $table->decimal('gross_income', 15, 2);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('taxable_income', 15, 2);
            $table->decimal('total_tax_due', 15, 2);
            $table->decimal('total_tax_paid', 15, 2)->default(0);
            $table->decimal('balance', 15, 2); // tax_due - tax_paid
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'paid'])->default('draft');
            $table->text('ai_analysis')->nullable(); // AI agent's analysis and recommendations
            $table->text('rejection_reason')->nullable();
            $table->timestamp('ai_processed_at')->nullable();
            $table->json('staff_breakdown')->nullable(); // Staff-wise tax breakdown
            $table->json('deduction_breakdown')->nullable(); // Breakdown of deductions
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'tax_period']);
            $table->index('business_id');
            $table->index('status');
            $table->index('tax_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_returns');
    }
};
