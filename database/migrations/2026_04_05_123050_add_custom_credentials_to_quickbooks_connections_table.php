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
        Schema::table('quickbooks_connections', function (Blueprint $table) {
            $table->text('client_id')->nullable()->after('business_id');
            $table->text('client_secret')->nullable()->after('client_id');
            $table->string('redirect_uri')->nullable()->after('client_secret');
            $table->string('environment', 20)->default('sandbox')->after('redirect_uri'); // sandbox or production
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quickbooks_connections', function (Blueprint $table) {
            $table->dropColumn(['client_id', 'client_secret', 'redirect_uri', 'environment']);
        });
    }
};
