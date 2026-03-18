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
        Schema::create('reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('set null');

            $table->enum('match_method', ['reference', 'amount_date_fuzzy'])->nullable();
            $table->decimal('confidence', 4, 2)->default(0.0);
            $table->enum('status', ['pending', 'matched', 'confirmed', 'rejected'])->default('pending');
            $table->timestamp('matched_at')->nullable();
            $table->json('data')->nullable();

            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index('transaction_id');
            $table->index('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliations');
    }
};
