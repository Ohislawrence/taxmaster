<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('affiliate_bank_name')->nullable()->after('affiliate_commission_percent');
            $table->string('affiliate_bank_account_name')->nullable()->after('affiliate_bank_name');
            $table->string('affiliate_bank_account_number')->nullable()->after('affiliate_bank_account_name');
            $table->string('affiliate_bank_code')->nullable()->after('affiliate_bank_account_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'affiliate_bank_name',
                'affiliate_bank_account_name',
                'affiliate_bank_account_number',
                'affiliate_bank_code',
            ]);
        });
    }
};
