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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');

            // Bank details
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('currency', 3)->default('NGN');

            // Mono integration
            $table->string('mono_account_id')->unique();
            $table->text('mono_access_token')->nullable(); // Encrypted

            // Account status
            $table->decimal('balance', 20, 2)->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_sync')->default(true);

            // Metadata
            $table->json('meta')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['business_id', 'is_active']);
            $table->index('last_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
