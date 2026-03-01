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
        Schema::table('businesses', function (Blueprint $table) {
            // Add accounting and compliance fields
            $table->string('tin')->nullable()->after('registration_number');
            $table->date('accounting_year_end')->nullable()->after('tin');
            $table->date('incorporation_date')->nullable()->after('accounting_year_end');
            $table->boolean('has_staff')->default(false)->after('incorporation_date');
            $table->integer('staff_count')->default(0)->after('has_staff');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'accounting_year_end',
                'incorporation_date',
                'has_staff',
                'staff_count',
            ]);
        });
    }
};
