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
        Schema::create('tax_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // paye, cit, vat, wht, cgt, stamp_duty
            $table->string('name'); // Personal Income Tax, Company Income Tax, etc.
            $table->text('description')->nullable();
            $table->enum('calculation_method', ['progressive', 'flat', 'percentage']); // How tax is calculated
            $table->decimal('flat_rate', 5, 2)->nullable(); // For flat rate taxes (e.g., CIT = 30%)
            $table->enum('frequency', ['monthly', 'quarterly', 'annual']); // Filing frequency
            $table->integer('due_day')->nullable(); // Day of month when due (e.g., 21st for VAT)
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Additional settings per tax type
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_types');
    }
};
