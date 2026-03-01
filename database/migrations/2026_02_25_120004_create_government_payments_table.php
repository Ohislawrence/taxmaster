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
        Schema::create('government_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->enum('tax_type', ['VAT', 'PAYE', 'WHT', 'CIT', 'CGT', 'STAMP_DUTY']);
            $table->morphs('return'); // Polymorphic relation to tax returns
            $table->string('period'); // YYYY-MM format
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['remita', 'bank_transfer', 'cash', 'cheque'])->default('remita');
            $table->string('remita_rrr')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('receipt_path')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'tax_type']);
            $table->index('status');
            $table->index('remita_rrr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('government_payments');
    }
};
