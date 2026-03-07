<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Multi-state tax support.
     *
     * Nigerian tax law requires:
     * - PAYE → remit to SIRS of employee's work state (PITA)
     * - WHT on individuals → remit to SIRS of the individual's state
     * - WHT on companies → remit to FIRS (federal)
     * - VAT, CIT → FIRS (no state dependency)
     */
    public function up(): void
    {
        // 1. Staff: add tax_state (where the employee works/resides for PAYE purposes)
        if (!Schema::hasColumn('business_staff', 'tax_state')) {
            Schema::table('business_staff', function (Blueprint $table) {
                $table->string('tax_state', 5)->nullable()
                    ->after('status')
                    ->comment('State code for PAYE remittance (e.g., LA=Lagos, FC=FCT)');
                $table->index('tax_state');
            });
        }

        // 2. PAYE returns: add tax_state (which state IRS receives this return)
        if (!Schema::hasColumn('paye_returns', 'tax_state')) {
            Schema::table('paye_returns', function (Blueprint $table) {
                $table->string('tax_state', 5)->nullable()
                    ->after('tax_authority')
                    ->comment('State code — determines which SIRS receives this PAYE return');
                $table->index('tax_state');
            });
        }

        // 3. WHT returns: add beneficiary_type and tax_state
        if (!Schema::hasColumn('wht_returns', 'beneficiary_type')) {
            Schema::table('wht_returns', function (Blueprint $table) {
                $table->string('beneficiary_type', 15)->default('company')
                    ->after('tax_authority')
                    ->comment('company = remit to FIRS, individual = remit to SIRS');
                $table->string('tax_state', 5)->nullable()
                    ->after('beneficiary_type')
                    ->comment('State code for individual WHT — which SIRS receives payment');
                $table->index('beneficiary_type');
                $table->index('tax_state');
            });
        }

        // 4. WHT transactions: add beneficiary_type so it aggregates correctly into returns
        if (!Schema::hasColumn('wht_transactions', 'beneficiary_type')) {
            Schema::table('wht_transactions', function (Blueprint $table) {
                $table->string('beneficiary_type', 15)->default('company')
                    ->after('vendor_tin')
                    ->comment('company or individual — affects WHT rate and remittance authority');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_staff', function (Blueprint $table) {
            $table->dropIndex(['tax_state']);
            $table->dropColumn('tax_state');
        });

        Schema::table('paye_returns', function (Blueprint $table) {
            $table->dropIndex(['tax_state']);
            $table->dropColumn('tax_state');
        });

        Schema::table('wht_returns', function (Blueprint $table) {
            $table->dropIndex(['beneficiary_type']);
            $table->dropIndex(['tax_state']);
            $table->dropColumn(['beneficiary_type', 'tax_state']);
        });

        Schema::table('wht_transactions', function (Blueprint $table) {
            $table->dropColumn('beneficiary_type');
        });
    }
};
