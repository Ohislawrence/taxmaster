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
        Schema::table('ai_workflows', function (Blueprint $table) {
            $table->string('related_entity_type')->nullable()->change();
            $table->unsignedBigInteger('related_entity_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_workflows', function (Blueprint $table) {
            $table->string('related_entity_type')->nullable(false)->change();
            $table->unsignedBigInteger('related_entity_id')->nullable(false)->change();
        });
    }
};
