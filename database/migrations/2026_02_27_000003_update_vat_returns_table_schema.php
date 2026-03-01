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
        // Drop the old vat_returns table to recreate with proper schema
        Schema::dropIfExists('vat_returns');

        // Create the new vat_returns table with comprehensive schema
        Schema::create('vat_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');

            // Period and Type
            $table->string('period'); // e.g., "2026-02" for monthly
            $table->enum('form_type', ['Form 002', 'Form 001'])->default('Form 002'); // Form 002: VAT Return, Form 001: Sales Register
            $table->enum('reporting_period', ['monthly', 'quarterly'])->default('monthly');

            // Sales Information
            $table->decimal('sales_turnover', 20, 2)->nullable(); // Total sales with VAT
            $table->decimal('exempt_sales', 20, 2)->default(0); // Exempt supplies (0% VAT)
            $table->decimal('zero_rated_sales', 20, 2)->default(0); // Zero-rated supplies (0% VAT)
            $table->decimal('export_sales', 20, 2)->default(0); // Exports (0% VAT)

            // VAT Calculation - Sales (Output VAT)
            $table->decimal('vat_on_sales', 20, 2)->nullable(); // Output VAT (5% of sales_turnover)

            // Purchases Information
            $table->decimal('purchases_turnover', 20, 2)->nullable(); // Total purchases
            $table->decimal('capital_goods_purchases', 20, 2)->default(0); // Capital goods (5% VAT)
            $table->decimal('services_purchases', 20, 2)->default(0); // Services (5% VAT)

            // VAT Calculation - Purchases (Input VAT)
            $table->decimal('input_vat', 20, 2)->nullable(); // Input VAT (5% of eligible purchases)
            $table->decimal('input_vat_adjustment', 20, 2)->default(0); // Adjustment to input VAT
            $table->decimal('input_credit', 20, 2)->nullable(); // Allowed input credit

            // VAT Settlement
            $table->decimal('vat_due', 20, 2)->nullable(); // Output VAT - Input Credit
            $table->decimal('settlement_amount', 20, 2)->nullable(); // Amount to pay or refund
            $table->enum('settlement_type', ['payment', 'refund', 'zero'])->default('payment');

            // Advance Payments/Credits
            $table->decimal('prior_month_credit', 20, 2)->default(0); // Previous month's refund carried forward
            $table->decimal('advance_payment', 20, 2)->default(0); // Advance payment made
            $table->decimal('withholding_vat', 20, 2)->default(0); // VAT withheld received

            // Credit Note & Adjustments
            $table->decimal('credit_notes_issued', 20, 2)->default(0); // Credit notes issued (reduces output VAT)
            $table->decimal('credit_notes_received', 20, 2)->default(0); // Credit notes received (reduces input VAT)
            $table->decimal('bad_debt_relief', 20, 2)->default(0); // Bad debt relief (claim back input VAT)

            // Status and References
            $table->enum('status', ['draft', 'submitted', 'accepted', 'paid', 'rejected', 'refund_pending', 'overdue'])->default('draft');
            $table->date('due_date')->nullable(); // Payment due date
            $table->string('firs_reference')->nullable(); // FIRS filing reference
            $table->text('notes')->nullable();

            // Form Data and Attachments
            $table->json('form_data')->nullable(); // Complete Form 002 data
            $table->json('sales_schedule')->nullable(); // Form 001: Sales details by category
            $table->json('purchases_schedule')->nullable(); // Supporting purchases
            $table->json('attachments')->nullable(); // Document paths

            // Audit Trail
            $table->timestamp('submitted_at')->nullable();
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
        Schema::dropIfExists('vat_returns');
    }
};
