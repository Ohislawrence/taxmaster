<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // categorization, compliance_reminder, payment_recovery
            $table->string('suggestible_type')->nullable(); // Polymorphic type
            $table->unsignedBigInteger('suggestible_id')->nullable(); // Polymorphic ID
            $table->json('data'); // AI response stored as JSON
            $table->float('confidence')->default(0);
            $table->string('status')->default('pending'); // pending, applied, dismissed, reviewed
            $table->timestamp('applied_at')->nullable();
            $table->text('user_feedback')->nullable();
            $table->timestamps();

            $table->index(['type', 'status', 'created_at']);
            $table->index(['suggestible_type', 'suggestible_id']);
        });
    }

    public function rollback(): void
    {
        Schema::dropIfExists('ai_suggestions');
    }
};
