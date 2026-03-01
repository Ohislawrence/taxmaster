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
        Schema::create('tax_reliefs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_type_id')->constrained('tax_types')->onDelete('cascade');
            $table->string('code')->unique(); // cra, nhf, nhis, pension, life_assurance
            $table->string('name'); // Consolidated Relief Allowance, etc.
            $table->text('description')->nullable();
            $table->enum('calculation_type', ['percentage', 'fixed', 'formula']); // How relief is calculated
            $table->decimal('value', 15, 2)->nullable(); // Fixed amount or percentage
            $table->string('formula')->nullable(); // For complex calculations (e.g., CRA formula)
            $table->decimal('minimum_amount', 15, 2)->nullable(); // Minimum relief amount
            $table->decimal('maximum_amount', 15, 2)->nullable(); // Maximum relief amount (cap)
            $table->boolean('is_mandatory')->default(false); // Auto-apply or optional
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0); // Order of application
            $table->json('eligibility_rules')->nullable(); // Conditions for eligibility
            $table->timestamps();

            $table->index(['tax_type_id', 'is_active']);
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_reliefs');
    }
};
