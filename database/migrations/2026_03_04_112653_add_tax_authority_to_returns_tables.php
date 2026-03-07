<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Nigerian tax authority distinction:
     * - FIRS (Federal Inland Revenue Service): VAT, CIT, WHT (companies), Stamp Duty
     * - SIRS (State Internal Revenue Service): PAYE, WHT (individuals)
     */
    public function up(): void
    {
        $tables = ['paye_returns', 'vat_returns', 'wht_returns', 'cit_returns'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'tax_authority')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->string('tax_authority', 10)->default('firs')
                        ->after('status')
                        ->comment('firs = Federal, sirs = State');
                    $blueprint->index('tax_authority');
                });
            }
        }

        // Set sensible defaults based on Nigerian tax law
        // PAYE is administered by SIRS (state tax)
        DB::table('paye_returns')->update(['tax_authority' => 'sirs']);
        // VAT and CIT are federal (FIRS)
        // WHT defaults to FIRS (companies), can be changed per return
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['paye_returns', 'vat_returns', 'wht_returns', 'cit_returns'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tax_authority')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropIndex(['tax_authority']);
                    $blueprint->dropColumn('tax_authority');
                });
            }
        }
    }
};
