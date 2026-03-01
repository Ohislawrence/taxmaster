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
        Schema::create('compliance_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');

            // Deadline details
            $table->string('deadline_type'); // VAT, PAYE, CIT, CAC_ANNUAL, etc
            $table->string('period'); // 2026-02 for monthly, 2026 for annual
            $table->string('description');
            $table->date('due_date');
            $table->enum('frequency', ['monthly', 'quarterly', 'annual', 'one-time'])->default('monthly');

            // Requirements
            $table->json('required_documents'); // Array of form names

            // Status tracking
            $table->enum('status', ['pending', 'completed', 'overdue', 'dismissed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reminded_at')->nullable();
            $table->integer('reminder_count')->default(0);

            // Notes and attachments
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable(); // Uploaded receipts, forms

            $table->timestamps();

            // Indexes
            $table->index(['business_id', 'due_date', 'status']);
            $table->index(['business_id', 'deadline_type', 'period']);
            $table->index('due_date');
            $table->unique(['business_id', 'deadline_type', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_deadlines');
    }
};
