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
        Schema::table('tax_returns', function (Blueprint $table) {
            // Add tax type support
            $table->foreignId('tax_type_id')->nullable()->after('business_id')->constrained('tax_types')->onDelete('cascade');
            
            // Add state for location-based taxes
            $table->string('state_code', 3)->nullable()->after('tax_type_id');
            
            // Add filing status for PAYE
            $table->enum('filing_status', ['single', 'married', 'head_of_household'])->nullable()->after('state_code');
            
            // Add reliefs claimed
            $table->json('reliefs_claimed')->nullable()->after('deduction_breakdown');
            
            // Add penalties and interest
            $table->decimal('penalties', 15, 2)->default(0)->after('balance');
            $table->decimal('interest', 15, 2)->default(0)->after('penalties');
            
            // Add total amount due (including penalties & interest)
            $table->decimal('total_amount_due', 15, 2)->default(0)->after('interest');
            
            // Add calculation details
            $table->json('calculation_details')->nullable()->after('staff_breakdown');
            
            $table->index('tax_type_id');
            $table->index('state_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_returns', function (Blueprint $table) {
            $table->dropForeign(['tax_type_id']);
            $table->dropColumn([
                'tax_type_id',
                'state_code',
                'filing_status',
                'reliefs_claimed',
                'penalties',
                'interest',
                'total_amount_due',
                'calculation_details'
            ]);
        });
    }
};
