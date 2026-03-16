<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('affiliate_rules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->default('global');
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->enum('mode', ['one_off', 'recurring_1_year'])->default('one_off');
            $table->decimal('value', 10, 2)->default(0);
            $table->string('applies_to')->default('global');
            $table->string('plan_slug')->nullable();
            $table->boolean('active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('affiliate_rules');
    }
};
