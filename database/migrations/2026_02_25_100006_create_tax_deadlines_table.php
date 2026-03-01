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
        Schema::create('tax_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_type_id')->constrained('tax_types')->onDelete('cascade');
            $table->string('period_type'); // monthly, quarterly, annual
            $table->integer('due_day'); // Day of month (e.g., 21 for VAT)
            $table->integer('due_month')->nullable(); // For annual taxes
            $table->integer('grace_days')->default(0); // Grace period after due date
            $table->decimal('late_filing_penalty_rate', 5, 2)->default(10.00); // 10% penalty
            $table->decimal('interest_rate_per_annum', 5, 2)->default(21.00); // 21% interest
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['tax_type_id', 'period_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_deadlines');
    }
};
