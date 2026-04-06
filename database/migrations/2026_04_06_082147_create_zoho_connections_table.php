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
        Schema::create('zoho_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');

            // Zoho Organization Info
            $table->string('organization_id')->nullable()->unique(); // Zoho Organization ID
            $table->string('organization_name')->nullable();
            $table->string('data_center')->default('com'); // com, eu, in, com.au, etc

            // API Credentials (Encrypted)
            $table->text('client_id');
            $table->text('client_secret');
            $table->text('redirect_uri');

            // OAuth Tokens
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();

            // Connection Status
            $table->enum('status', ['credentials_set', 'active', 'expired', 'revoked', 'error'])->default('credentials_set');
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status')->nullable(); // success, failed, partial
            $table->text('last_error')->nullable();

            // Sync Configuration
            $table->boolean('auto_sync_enabled')->default(true);
            $table->string('sync_frequency')->default('daily'); // hourly, daily, weekly
            $table->json('sync_settings')->nullable(); // What to sync (invoices, bills, etc)

            // Metadata
            $table->json('metadata')->nullable(); // Store additional Zoho info
            $table->timestamps();

            // Indexes
            $table->index(['business_id', 'status']);
            $table->index('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_connections');
    }
};
