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
        Schema::create('cit_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');

            // Period and Dates
            $table->string('period'); // e.g., "2026" or "2026-Q1" for quarterly
            $table->enum('return_type', ['annual', 'quarterly', 'provisional'])->default('annual');
            $table->date('submitted_at')->nullable();
            $table->date('due_date')->nullable();

            // Financial Data
            $table->decimal('gross_profit', 20, 2)->nullable(); // From P&L
            $table->decimal('revenue', 20, 2)->nullable();
            $table->decimal('cost_of_goods_sold', 20, 2)->nullable();

            // Add-backs
            $table->decimal('depreciation', 20, 2)->default(0);
            $table->decimal('amortization', 20, 2)->default(0);
            $table->decimal('other_add_backs', 20, 2)->default(0);

            // Deductions/Less
            $table->decimal('capital_allowances', 20, 2)->default(0);
            $table->decimal('allowable_expenses', 20, 2)->default(0);
            $table->decimal('other_deductions', 20, 2)->default(0);

            // Tax Calculation
            $table->decimal('taxable_income', 20, 2)->nullable();
            $table->decimal('cit_rate', 5, 4)->default(0.30); // 30% standard rate
            $table->decimal('cit_payable', 20, 2)->nullable();

            // Minimum Tax
            $table->decimal('turnover', 20, 2)->nullable(); // For min tax calculation
            $table->decimal('gross_assets', 20, 2)->nullable(); // For min tax calculation
            $table->decimal('paid_up_capital', 20, 2)->nullable(); // For min tax calculation
            $table->decimal('minimum_tax_amount', 20, 2)->nullable();
            $table->decimal('tax_due', 20, 2)->nullable(); // Higher of CIT or min tax

            // Advance Tax / Quarterly Payments
            $table->decimal('advance_tax', 20, 2)->default(0);
            $table->decimal('withholding_tax', 20, 2)->default(0);
            $table->decimal('total_credits', 20, 2)->default(0);

            // Balance
            $table->decimal('balance_due', 20, 2)->nullable();
            $table->decimal('balance_refund', 20, 2)->nullable();

            // Penalties and Interest
            $table->decimal('late_filing_penalty', 20, 2)->default(0);
            $table->decimal('payment_interest', 20, 2)->default(0);

            // Status and References
            $table->enum('status', ['draft', 'submitted', 'accepted', 'paid', 'rejected', 'overdue'])->default('draft');
            $table->string('firs_reference')->nullable(); // FIRS acknowledgment number
            $table->string('form_a_reference')->nullable(); // Form A filing ref

            // Notes and Attachments
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable(); // Document paths
            $table->json('calculation_details')->nullable(); // Breakdown of calculations
            $table->json('form_data')->nullable(); // Full form data

            // Audit
            $table->timestamp('filed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['business_id', 'period']);
            $table->index(['status', 'due_date']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cit_returns');
    }
};
