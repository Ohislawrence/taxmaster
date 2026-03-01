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
        Schema::create('state_tax_configs', function (Blueprint $table) {
            $table->id();
            $table->string('state_code', 3); // LAG, ABJ, KAN, etc.
            $table->string('state_name'); // Lagos, Abuja, Kano, etc.
            $table->foreignId('tax_type_id')->constrained('tax_types')->onDelete('cascade');
            $table->decimal('rate_override', 5, 2)->nullable(); // Override federal rate if needed
            $table->decimal('minimum_tax', 15, 2)->nullable(); // Minimum tax for the state
            $table->json('exemptions')->nullable(); // State-specific exemptions
            $table->json('additional_levies')->nullable(); // Local government levies, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['state_code', 'tax_type_id']);
            $table->index('state_code');
            $table->index(['tax_type_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('state_tax_configs');
    }
};
