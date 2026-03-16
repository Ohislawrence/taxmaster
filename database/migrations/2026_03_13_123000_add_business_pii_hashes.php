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
            if (! Schema::hasColumn('businesses', 'registration_number_hash')) {
                $table->string('registration_number_hash', 64)->nullable()->unique()->after('registration_number');
            }

            if (! Schema::hasColumn('businesses', 'tax_identification_number_hash')) {
                $table->string('tax_identification_number_hash', 64)->nullable()->unique()->after('tax_identification_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (Schema::hasColumn('businesses', 'registration_number_hash')) {
                $table->dropUnique('businesses_registration_number_hash_unique');
                $table->dropColumn('registration_number_hash');
            }

            if (Schema::hasColumn('businesses', 'tax_identification_number_hash')) {
                $table->dropUnique('businesses_tax_identification_number_hash_unique');
                $table->dropColumn('tax_identification_number_hash');
            }
        });
    }
};
