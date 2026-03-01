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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->onDelete('cascade');
            $table->foreignId('business_id')->constrained()->onDelete('cascade');

            // Mono transaction details
            $table->string('mono_transaction_id')->unique();

            // Transaction data
            $table->enum('type', ['debit', 'credit']);
            $table->decimal('amount', 20, 2);
            $table->string('currency', 3)->default('NGN');
            $table->text('description');
            $table->string('counterparty')->nullable();
            $table->timestamp('transaction_date');
            $table->decimal('balance', 20, 2)->nullable();

            // Categorization
            $table->string('category')->nullable(); // REVENUE, EXPENSES, TAX, PERSONAL
            $table->string('sub_category')->nullable(); // VAT_OUTPUT, VAT_INPUT, etc
            $table->decimal('confidence', 3, 2)->nullable(); // 0.00 to 1.00
            $table->boolean('vat_applicable')->default(false);
            $table->boolean('is_business_expense')->default(true);
            $table->boolean('user_verified')->default(false);

            // Notes and attachments
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable();

            // Metadata
            $table->json('meta')->nullable();
            $table->timestamps();

            // Indexes for fast queries
            $table->index(['business_id', 'transaction_date']);
            $table->index(['business_id', 'category', 'sub_category']);
            $table->index(['business_id', 'vat_applicable']);
            $table->index('transaction_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
