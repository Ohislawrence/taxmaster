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
        Schema::create('business_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('tax_identification_number')->nullable();
            $table->decimal('monthly_salary', 12, 2);
            $table->string('employment_type'); // full_time, part_time, contract
            $table->string('designation');
            $table->date('date_employed');
            $table->date('date_relieved')->nullable();
            $table->enum('status', ['active', 'on_leave', 'terminated'])->default('active');
            $table->decimal('taxable_income', 12, 2)->nullable(); // Calculated field
            $table->decimal('monthly_tax_due', 12, 2)->nullable(); // Calculated field
            $table->decimal('annual_tax_due', 12, 2)->nullable(); // Calculated field
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('business_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_staff');
    }
};
