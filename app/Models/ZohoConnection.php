<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZohoConnection extends Model
{
    use HasFactory;

    protected $table = 'zoho_connections';

    protected $fillable = [
        'business_id',
        'client_id',
        'client_secret',
        'redirect_uri',
        'data_center',
        'organization_id',
        'organization_name',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'status',
        'last_synced_at',
        'last_sync_status',
        'last_error',
        'auto_sync_enabled',
        'sync_frequency',
        'sync_settings',
        'metadata',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'auto_sync_enabled' => 'boolean',
        'sync_settings' => 'array',
        'metadata' => 'array',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'client_id' => 'encrypted',
        'client_secret' => 'encrypted',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
        'client_secret',
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
        return $this->hasMany(ZohoSyncLog::class, 'zoho_connection_id');
    }

    /**
     * Check if access token is expired
     */
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at <= now();
    }

    /**
     * Check if connection is active and usable
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && !empty($this->access_token);
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
     * Mark connection as error state
     */
    public function markError(string $error): void
    {
        $this->update([
            'status' => 'error',
            'last_error' => $error,
        ]);
    }

    /**
     * Update tokens
     */
    public function updateTokens(string $accessToken, string $refreshToken, int $expiresIn = 3600): void
    {
        $this->update([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_expires_at' => now()->addSeconds($expiresIn),
            'status' => 'active',
        ]);
    }

    /**
     * Check if credentials are configured
     */
    public function hasCredentials(): bool
    {
        return !empty($this->client_id) && !empty($this->client_secret);
    }

    /**
     * Check if credentials are valid (non-empty after decryption)
     */
    public function hasValidCredentials(): bool
    {
        return $this->hasCredentials() && !empty($this->redirect_uri) && !empty($this->data_center);
    }

    /**
     * Update sync status
     */
    public function updateSyncStatus(string $status, ?string $error = null): void
    {
        $this->update([
            'last_synced_at' => now(),
            'last_sync_status' => $status,
            'last_error' => $error,
        ]);
    }

    /**
     * Get the latest successful sync log
     */
    public function lastSuccessfulSync(): ?ZohoSyncLog
    {
        return $this->syncLogs()
            ->where('status', 'completed')
            ->latest()
            ->first();
    }

    /**
     * Check if sync is due based on frequency
     */
    public function isSyncDue(): bool
    {
        if (!$this->auto_sync_enabled || !$this->isActive()) {
            return false;
        }

        if (!$this->last_synced_at) {
            return true;
        }

        $threshold = match($this->sync_frequency) {
            'hourly' => now()->subHour(),
            'daily' => now()->subDay(),
            'weekly' => now()->subWeek(),
            default => now()->subDay(),
        };

        return $this->last_synced_at < $threshold;
    }

    /**
     * Get has_credentials attribute
     */
    public function getHasCredentialsAttribute(): bool
    {
        return $this->hasCredentials();
    }

    /**
     * Get the Zoho API base URL based on data center
     */
    public function getApiBaseUrl(): string
    {
        $dataCenterMap = [
            'com' => 'https://books.zoho.com',
            'eu' => 'https://books.zoho.eu',
            'in' => 'https://books.zoho.in',
            'com.au' => 'https://books.zoho.com.au',
            'com.cn' => 'https://books.zoho.com.cn',
            'jp' => 'https://books.zoho.jp',
        ];

        return $dataCenterMap[$this->data_center] ?? 'https://books.zoho.com';
    }

    /**
     * Get the Zoho accounts base URL based on data center
     */
    public function getAccountsBaseUrl(): string
    {
        $dataCenterMap = [
            'com' => 'https://accounts.zoho.com',
            'eu' => 'https://accounts.zoho.eu',
            'in' => 'https://accounts.zoho.in',
            'com.au' => 'https://accounts.zoho.com.au',
            'com.cn' => 'https://accounts.zoho.com.cn',
            'jp' => 'https://accounts.zoho.jp',
        ];

        return $dataCenterMap[$this->data_center] ?? 'https://accounts.zoho.com';
    }
}
