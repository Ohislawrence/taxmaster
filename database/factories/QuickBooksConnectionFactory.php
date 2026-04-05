<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\QuickBooksConnection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class QuickBooksConnectionFactory extends Factory
{
    protected $model = QuickBooksConnection::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'client_id' => 'QB' . Str::random(40),
            'client_secret' => 'secret_' . Str::random(40),
            'redirect_uri' => 'https://example.com/quickbooks/callback',
            'environment' => $this->faker->randomElement(['sandbox', 'production']),
            'realm_id' => (string) $this->faker->numberBetween(1000000000, 9999999999),
            'company_name' => $this->faker->company(),
            'company_country' => 'NG',
            'access_token' => 'eyJenc' . Str::random(100),
            'refresh_token' => 'AB11' . Str::random(100),
            'token_expires_at' => Carbon::now()->addHour(),
            'refresh_token_expires_at' => Carbon::now()->addDays(100),
            'status' => 'active',
            'last_synced_at' => Carbon::now()->subHours(6),
            'last_sync_status' => 'success',
            'last_error' => null,
            'auto_sync_enabled' => true,
            'sync_frequency' => 'daily',
            'sync_settings' => [
                'sync_invoices' => true,
                'sync_bills' => true,
                'sync_customers' => true,
                'sync_vendors' => true,
            ],
            'metadata' => [
                'last_invoice_id' => (string) $this->faker->numberBetween(1, 1000),
                'last_bill_id' => (string) $this->faker->numberBetween(1, 1000),
            ],
        ];
    }

    /**
     * Indicate that the connection has no credentials set
     */
    public function withoutCredentials(): static
    {
        return $this->state(fn (array $attributes) => [
            'client_id' => null,
            'client_secret' => null,
            'redirect_uri' => null,
            'status' => 'error',
            'last_error' => 'No credentials set',
        ]);
    }

    /**
     * Indicate that the connection is expired
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'token_expires_at' => Carbon::now()->subDay(),
            'refresh_token_expires_at' => Carbon::now()->subDay(),
            'last_error' => 'Token expired',
        ]);
    }

    /**
     * Indicate that the connection is disconnected
     */
    public function disconnected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'revoked',
            'last_error' => 'Connection revoked by user',
        ]);
    }

    /**
     * Indicate that only credentials are set
     */
    public function credentialsOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'error',
            'last_error' => 'OAuth not completed',
            'realm_id' => null,
            'company_name' => null,
            'access_token' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
            'refresh_token_expires_at' => null,
        ]);
    }

    /**
     * Indicate that the access token is expired but refresh token is valid
     */
    public function accessTokenExpired(): static
    {
        return $this->state(fn (array $attributes) => [
            'token_expires_at' => Carbon::now()->subMinutes(30),
            'refresh_token_expires_at' => Carbon::now()->addDays(50),
        ]);
    }
}
