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
        Schema::create('wht_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->date('transaction_date');
            $table->enum('transaction_type', [
                'dividends',
                'interest',
                'rent',
                'royalties',
                'commissions',
                'consultancy',
                'contracts',
                'management_fees',
                'directors_fees',
                'professional_fees'
            ]);
            $table->string('vendor_name');
            $table->string('vendor_tin')->nullable();
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('wht_rate', 5, 2); // Percentage
            $table->decimal('wht_amount', 15, 2);
            $table->decimal('net_amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'transaction_date']);
            $table->index('transaction_type');
            $table->index('vendor_tin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wht_transactions');
    }
};
