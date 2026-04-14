<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopifyConnection extends Model
{
    use HasFactory;

    protected $table = 'shopify_connections';

    protected $fillable = [
        'business_id',
        'shop_domain',
        'shop_name',
        'shop_email',
        'shop_currency',
        'api_key',
        'api_secret',
        'access_token',
        'scope',
        'token_expires_at',
        'status',
        'last_synced_at',
        'last_sync_status',
        'last_error',
        'auto_sync_enabled',
        'sync_frequency',
        'sync_settings',
        'total_orders_synced',
        'total_products_synced',
        'total_customers_synced',
        'first_sync_at',
        'metadata',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'first_sync_at' => 'datetime',
        'auto_sync_enabled' => 'boolean',
        'sync_settings' => 'array',
        'metadata' => 'array',
        'access_token' => 'encrypted',
        'api_key' => 'encrypted',
        'api_secret' => 'encrypted',
        'total_orders_synced' => 'integer',
        'total_products_synced' => 'integer',
        'total_customers_synced' => 'integer',
    ];

    protected $hidden = [
        'access_token',
        'api_key',
        'api_secret',
    ];

    protected $appends = [
        'has_credentials',
    ];

    /**
     * Get the business that owns this connection
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get sync logs for this connection
     */
    public function syncLogs(): HasMany
    {
        return $this->hasMany(ShopifySyncLog::class, 'shopify_connection_id');
    }

    /**
     * Check if access token is expired
     */
    public function isTokenExpired(): bool
    {
        // Shopify tokens don't expire by default, but check if expiry is set
        return $this->token_expires_at && $this->token_expires_at <= now();
    }

    /**
     * Check if connection is active and usable
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && !empty($this->access_token) && !empty($this->shop_domain);
    }

    /**
     * Mark connection as expired
     */
    public function markExpired(?string $error = null): void
    {
        $this->update([
            'status' => 'expired',
            'last_error' => $error,
        ]);
    }

    /**
     * Check if credentials are configured
     */
    public function getHasCredentialsAttribute(): bool
    {
        return !empty($this->api_key) || !empty($this->access_token);
    }

    /**
     * Get the Shopify admin URL
     */
    public function getAdminUrlAttribute(): ?string
    {
        if (!$this->shop_domain) {
            return null;
        }
        return "https://{$this->shop_domain}/admin";
    }

    /**
     * Update sync statistics
     */
    public function updateSyncStats(int $orders = 0, int $products = 0, int $customers = 0): void
    {
        $this->increment('total_orders_synced', $orders);
        $this->increment('total_products_synced', $products);
        $this->increment('total_customers_synced', $customers);

        if (!$this->first_sync_at) {
            $this->update(['first_sync_at' => now()]);
        }
    }
}
