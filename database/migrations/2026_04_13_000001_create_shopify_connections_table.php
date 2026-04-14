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
        Schema::create('shopify_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');

            // Shopify Store Info
            $table->string('shop_domain')->nullable()->unique(); // e.g., mystore.myshopify.com
            $table->string('shop_name')->nullable();
            $table->string('shop_email')->nullable();
            $table->string('shop_currency')->default('NGN');

            // API Credentials (Encrypted)
            $table->text('api_key')->nullable(); // Custom app API key
            $table->text('api_secret')->nullable(); // Custom app API secret
            $table->text('access_token')->nullable(); // OAuth access token or Admin API access token

            // OAuth Tokens (if using OAuth)
            $table->string('scope')->nullable(); // API scopes granted
            $table->timestamp('token_expires_at')->nullable();

            // Connection Status
            $table->enum('status', ['credentials_set', 'active', 'expired', 'revoked', 'error'])->default('credentials_set');
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status')->nullable(); // success, failed, partial
            $table->text('last_error')->nullable();

            // Sync Configuration
            $table->boolean('auto_sync_enabled')->default(true);
            $table->string('sync_frequency')->default('daily'); // hourly, daily, weekly
            $table->json('sync_settings')->nullable(); // What to sync (orders, products, customers, etc)

            // Sync Statistics
            $table->integer('total_orders_synced')->default(0);
            $table->integer('total_products_synced')->default(0);
            $table->integer('total_customers_synced')->default(0);
            $table->timestamp('first_sync_at')->nullable();

            // Metadata
            $table->json('metadata')->nullable(); // Store additional Shopify info (plan, location, etc)
            $table->timestamps();

            // Indexes
            $table->index(['business_id', 'status']);
            $table->index('shop_domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify_connections');
    }
};
