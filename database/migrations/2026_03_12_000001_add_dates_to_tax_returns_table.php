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
            $table->date('date_filed')->nullable()->after('submission_date');
            $table->date('date_paid')->nullable()->after('date_filed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_returns', function (Blueprint $table) {
            $table->dropColumn(['date_filed', 'date_paid']);
        });
    }
};
