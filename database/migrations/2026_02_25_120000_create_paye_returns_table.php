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
        Schema::create('paye_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('period'); // YYYY-MM format
            $table->decimal('total_gross_pay', 15, 2)->default(0);
            $table->decimal('total_tax_deducted', 15, 2)->default(0);
            $table->integer('staff_count')->default(0);
            $table->json('schedule_data')->nullable(); // Staff breakdown
            $table->date('filed_date')->nullable();
            $table->enum('status', ['draft', 'filed', 'paid', 'overdue'])->default('draft');
            $table->string('firs_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'period']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paye_returns');
    }
};
