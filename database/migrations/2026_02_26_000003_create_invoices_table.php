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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_subscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('business_id')->constrained()->onDelete('cascade');

            // Invoice details
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->date('due_date');

            // Amount
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            // Period
            $table->date('period_start');
            $table->date('period_end');

            // Status
            $table->enum('status', ['draft', 'sent', 'viewed', 'paid', 'cancelled'])->default('draft');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();

            // Storage
            $table->string('pdf_path')->nullable();
            $table->json('data')->nullable(); // Full invoice data for PDF generation

            $table->timestamps();

            // Indexes
            $table->index(['business_id', 'status']);
            $table->index(['business_subscription_id', 'invoice_date']);
            $table->index('invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
