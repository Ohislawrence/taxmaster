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
        Schema::create('vat_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');

            // Period
            $table->string('period'); // 2026-02 (YYYY-MM)

            // VAT Calculation
            $table->decimal('vat_sales', 20, 2)->default(0); // VATable sales
            $table->decimal('output_vat', 20, 2)->default(0); // VAT on sales (7.5%)
            $table->decimal('vat_expenses', 20, 2)->default(0); // VATable expenses
            $table->decimal('input_vat', 20, 2)->default(0); // VAT on expenses (7.5%)
            $table->decimal('net_vat', 20, 2)->default(0); // Output - Input

            // Filing details
            $table->date('due_date');
            $table->enum('status', ['draft', 'submitted', 'paid', 'overdue'])->default('draft');
            $table->string('form_002_reference')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Payment tracking
            $table->string('payment_reference')->nullable();
            $table->string('receipt_path')->nullable();

            // Form data (JSON storage of complete form)
            $table->json('form_data')->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['business_id', 'period']);
            $table->index(['business_id', 'status']);
            $table->unique(['business_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vat_returns');
    }
};
