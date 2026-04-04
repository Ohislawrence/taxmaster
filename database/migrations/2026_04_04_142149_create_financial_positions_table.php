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
        Schema::create('financial_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->date('position_date'); // End of period date
            $table->string('period_type')->default('year-end'); // year-end, quarter-end, month-end

            // Assets - Current
            $table->decimal('cash_and_bank', 15, 2)->default(0);
            $table->decimal('trade_receivables', 15, 2)->default(0);
            $table->decimal('inventory', 15, 2)->default(0);
            $table->decimal('other_current_assets', 15, 2)->default(0);

            // Assets - Non-Current
            $table->decimal('property_plant_equipment', 15, 2)->default(0);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->decimal('intangible_assets', 15, 2)->default(0);
            $table->decimal('other_non_current_assets', 15, 2)->default(0);

            // Liabilities - Current
            $table->decimal('trade_payables', 15, 2)->default(0);
            $table->decimal('tax_payable', 15, 2)->default(0);
            $table->decimal('other_current_liabilities', 15, 2)->default(0);

            // Liabilities - Non-Current
            $table->decimal('long_term_borrowings', 15, 2)->default(0);
            $table->decimal('other_non_current_liabilities', 15, 2)->default(0);

            // Equity
            $table->decimal('share_capital', 15, 2)->default(0);
            $table->decimal('retained_earnings', 15, 2)->default(0);
            $table->decimal('other_reserves', 15, 2)->default(0);

            // Metadata
            $table->boolean('is_ai_generated')->default(false);
            $table->json('ai_confidence')->nullable(); // Confidence scores for AI estimates
            $table->text('notes')->nullable();

            $table->timestamps();

            // Ensure one position per business per date
            $table->unique(['business_id', 'position_date']);
            $table->index(['business_id', 'period_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_positions');
    }
};
