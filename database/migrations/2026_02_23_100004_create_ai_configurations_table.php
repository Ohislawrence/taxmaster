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
        Schema::create('ai_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('business_id')->nullable();
            $table->string('api_provider')->default('deepseek'); // deepseek, gemini, gpt-4, etc
            $table->string('api_key');
            $table->string('model')->default('deepseek-chat');
            $table->integer('max_tokens')->default(2000);
            $table->decimal('temperature', 3, 2)->default(0.7);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'api_provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_configurations');
    }
};
