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
        Schema::create('tax_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->foreignId('tax_return_id')->nullable()->constrained('tax_returns')->onDelete('set null');
            $table->string('payment_reference')->unique();
            $table->string('paystack_reference')->nullable()->unique();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method'); // paystack, bank_transfer, etc
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('currency')->default('NGN');
            $table->date('payment_date')->nullable();
            $table->text('description')->nullable();
            $table->json('paystack_response')->nullable(); // Store Paystack API response
            $table->json('metadata')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('business_id');
            $table->index('status');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_payments');
    }
};
