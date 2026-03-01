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
        Schema::create('tax_brackets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_type_id')->constrained('tax_types')->onDelete('cascade');
            $table->decimal('min_amount', 15, 2); // Minimum income for this bracket
            $table->decimal('max_amount', 15, 2)->nullable(); // Maximum (null = unlimited)
            $table->decimal('rate', 5, 2); // Tax rate percentage (e.g., 7.00 for 7%)
            $table->decimal('fixed_amount', 15, 2)->default(0); // Fixed amount for this bracket
            $table->integer('order')->default(0); // Order of application
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tax_type_id', 'is_active']);
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_brackets');
    }
};
