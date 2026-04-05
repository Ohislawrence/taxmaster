<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Business;
use App\Models\QuickBooksConnection;
use App\Models\QuickBooksSyncLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class QuickBooksConnectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_business()
    {
        $business = Business::factory()->create();
        $connection = QuickBooksConnection::factory()->create([
            'business_id' => $business->id,
        ]);

        $this->assertInstanceOf(Business::class, $connection->business);
        $this->assertEquals($business->id, $connection->business->id);
    }

    /** @test */
    public function it_has_many_sync_logs()
    {
        $connection = QuickBooksConnection::factory()->create();
        QuickBooksSyncLog::factory()->count(3)->create([
            'quickbooks_connection_id' => $connection->id,
        ]);

        $this->assertCount(3, $connection->syncLogs);
        $this->assertInstanceOf(QuickBooksSyncLog::class, $connection->syncLogs->first());
    }

    /** @test */
    public function it_detects_expired_access_token()
    {
        $connection = QuickBooksConnection::factory()->create([
            'token_expires_at' => Carbon::now()->subHour(),
        ]);

        $this->assertTrue($connection->isTokenExpired());
    }

    /** @test */
    public function it_detects_valid_access_token()
    {
        $connection = QuickBooksConnection::factory()->create([
            'token_expires_at' => Carbon::now()->addHour(),
        ]);

        $this->assertFalse($connection->isTokenExpired());
    }

    /** @test */
    public function it_detects_expired_refresh_token()
    {
        $connection = QuickBooksConnection::factory()->create([
            'refresh_token_expires_at' => Carbon::now()->subDay(),
        ]);

        $this->assertTrue($connection->isRefreshTokenExpired());
    }

    /** @test */
    public function it_detects_active_connection()
    {
        $connection = QuickBooksConnection::factory()->create([
            'status' => 'active',
            'token_expires_at' => Carbon::now()->addHour(),
            'refresh_token_expires_at' => Carbon::now()->addDays(30),
        ]);

        $this->assertTrue($connection->isActive());
    }

    /** @test */
    public function inactive_status_makes_connection_not_active()
    {
        $connection = QuickBooksConnection::factory()->create([
            'status' => 'revoked',
            'token_expires_at' => Carbon::now()->addHour(),
            'refresh_token_expires_at' => Carbon::now()->addDays(30),
        ]);

        $this->assertFalse($connection->isActive());
    }

    /** @test */
    public function expired_refresh_token_makes_connection_not_active()
    {
        $connection = QuickBooksConnection::factory()->create([
            'status' => 'active',
            'token_expires_at' => Carbon::now()->addHour(),
            'refresh_token_expires_at' => Carbon::now()->subDay(),
        ]);

        $this->assertFalse($connection->isActive());
    }

    /** @test */
    public function it_validates_credentials_presence()
    {
        $connectionWithCredentials = QuickBooksConnection::factory()->create([
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'https://example.com/callback',
        ]);

        $this->assertTrue($connectionWithCredentials->hasValidCredentials());
    }

    /** @test */
    public function it_detects_missing_credentials()
    {
        $connectionWithoutCredentials = QuickBooksConnection::factory()->create([
            'client_id' => null,
            'client_secret' => null,
            'redirect_uri' => null,
        ]);

        $this->assertFalse($connectionWithoutCredentials->hasValidCredentials());
    }

    /** @test */
    public function it_checks_if_sync_is_due_based_on_frequency()
    {
        // Hourly sync due
        $hourlyConnection = QuickBooksConnection::factory()->create([
            'auto_sync_enabled' => true,
            'sync_frequency' => 'hourly',
            'last_synced_at' => Carbon::now()->subHours(2),
        ]);
        $this->assertTrue($hourlyConnection->isSyncDue());

        // Daily sync due
        $dailyConnection = QuickBooksConnection::factory()->create([
            'auto_sync_enabled' => true,
            'sync_frequency' => 'daily',
            'last_synced_at' => Carbon::now()->subDays(2),
        ]);
        $this->assertTrue($dailyConnection->isSyncDue());

        // Weekly sync due
        $weeklyConnection = QuickBooksConnection::factory()->create([
            'auto_sync_enabled' => true,
            'sync_frequency' => 'weekly',
            'last_synced_at' => Carbon::now()->subWeeks(2),
        ]);
        $this->assertTrue($weeklyConnection->isSyncDue());
    }

    /** @test */
    public function it_detects_sync_not_due()
    {
        $connection = QuickBooksConnection::factory()->create([
            'auto_sync_enabled' => true,
            'sync_frequency' => 'daily',
            'last_synced_at' => Carbon::now()->subHours(12),
        ]);

        $this->assertFalse($connection->isSyncDue());
    }

    /** @test */
    public function disabled_auto_sync_means_sync_not_due()
    {
        $connection = QuickBooksConnection::factory()->create([
            'auto_sync_enabled' => false,
            'sync_frequency' => 'daily',
            'last_synced_at' => Carbon::now()->subDays(10),
        ]);

        $this->assertFalse($connection->isSyncDue());
    }

    /** @test */
    public function it_marks_connection_as_expired()
    {
        $connection = QuickBooksConnection::factory()->create([
            'status' => 'active',
        ]);

        $connection->markExpired('Token expired');

        $this->assertEquals('expired', $connection->status);
        $this->assertEquals('Token expired', $connection->last_error);
    }

    /** @test */
    public function it_encrypts_sensitive_fields()
    {
        $connection = QuickBooksConnection::factory()->create([
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
        ]);

        // Check that sensitive fields are in the hidden array
        $this->assertContains('access_token', $connection->getHidden());
        $this->assertContains('refresh_token', $connection->getHidden());
        $this->assertContains('client_secret', $connection->getHidden());

        // Check that values are encrypted in the database
        $raw = \DB::table('quickbooks_connections')->find($connection->id);
        $this->assertNotEquals('test_access_token', $raw->access_token);
        $this->assertNotEquals('test_client_secret', $raw->client_secret);
    }

    /** @test */
    public function it_casts_arrays_correctly()
    {
        $syncSettings = [
            'sync_invoices' => true,
            'sync_bills' => true,
            'sync_customers' => false,
        ];

        $metadata = [
            'last_invoice_id' => '123',
            'last_bill_id' => '456',
        ];

        $connection = QuickBooksConnection::factory()->create([
            'sync_settings' => $syncSettings,
            'metadata' => $metadata,
        ]);

        $this->assertEquals($syncSettings, $connection->sync_settings);
        $this->assertEquals($metadata, $connection->metadata);
        $this->assertIsArray($connection->sync_settings);
        $this->assertIsArray($connection->metadata);
    }
}
