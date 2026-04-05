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
        Schema::create('quickbooks_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            
            // QuickBooks Company Info
            $table->string('realm_id')->unique(); // QuickBooks Company ID
            $table->string('company_name')->nullable();
            $table->string('company_country')->default('NG');
            
            // OAuth Tokens
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('token_expires_at');
            $table->timestamp('refresh_token_expires_at');
            
            // Connection Status
            $table->enum('status', ['active', 'expired', 'revoked', 'error'])->default('active');
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status')->nullable(); // success, failed, partial
            $table->text('last_error')->nullable();
            
            // Sync Configuration
            $table->boolean('auto_sync_enabled')->default(true);
            $table->string('sync_frequency')->default('daily'); // hourly, daily, weekly
            $table->json('sync_settings')->nullable(); // What to sync (invoices, bills, etc)
            
            // Metadata
            $table->json('metadata')->nullable(); // Store additional QB info
            $table->timestamps();
            
            // Indexes
            $table->index(['business_id', 'status']);
            $table->index('realm_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quickbooks_connections');
    }
};
