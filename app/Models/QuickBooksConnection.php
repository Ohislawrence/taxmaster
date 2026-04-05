<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuickBooksConnection extends Model
{
    use HasFactory;
    protected $table = 'quickbooks_connections';

    protected $fillable = [
        'business_id',
        'client_id',
        'client_secret',
        'redirect_uri',
        'environment',
        'realm_id',
        'company_name',
        'company_country',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'refresh_token_expires_at',
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
        'refresh_token_expires_at' => 'datetime',
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
        return $this->hasMany(QuickBooksSyncLog::class, 'quickbooks_connection_id');
    }

    /**
     * Check if access token is expired
     */
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at <= now();
    }

    /**
     * Check if refresh token is expired
     */
    public function isRefreshTokenExpired(): bool
    {
        return $this->refresh_token_expires_at <= now();
    }

    /**
     * Check if connection is active and usable
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isRefreshTokenExpired();
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
    public function updateTokens(string $accessToken, string $refreshToken, int $expiresIn = 3600, int $refreshExpiresIn = 8726400): void
    {
        $this->update([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_expires_at' => now()->addSeconds($expiresIn),
            'refresh_token_expires_at' => now()->addSeconds($refreshExpiresIn),
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
        return $this->hasCredentials() && !empty($this->redirect_uri);
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
    public function lastSuccessfulSync(): ?QuickBooksSyncLog
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
}
