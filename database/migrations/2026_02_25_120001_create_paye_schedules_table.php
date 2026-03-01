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
        Schema::create('paye_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paye_return_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('business_staff_id');
            $table->foreign('business_staff_id')->references('id')->on('business_staff')->onDelete('cascade');
            $table->decimal('gross_pay', 15, 2);
            $table->json('allowances')->nullable(); // Housing, Transport, etc.
            $table->json('tax_reliefs')->nullable(); // CRA, Pension, NHF, NHIS
            $table->decimal('taxable_income', 15, 2);
            $table->decimal('paye_due', 15, 2);
            $table->decimal('cumulative_gross', 15, 2)->default(0);
            $table->decimal('cumulative_tax', 15, 2)->default(0);
            $table->timestamps();

            $table->index('paye_return_id');
            $table->index('business_staff_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paye_schedules');
    }
};
