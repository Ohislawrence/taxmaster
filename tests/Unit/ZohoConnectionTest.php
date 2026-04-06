<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Business;
use App\Models\ZohoConnection;
use App\Models\ZohoSyncLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ZohoConnectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_business()
    {
        $business = Business::factory()->create();
        $connection = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'status' => 'credentials_set',
        ]);

        $this->assertInstanceOf(Business::class, $connection->business);
        $this->assertEquals($business->id, $connection->business->id);
    }

    /** @test */
    public function it_has_many_sync_logs()
    {
        $business = Business::factory()->create();
        $connection = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'status' => 'active',
        ]);

        $log = ZohoSyncLog::create([
            'zoho_connection_id' => $connection->id,
            'sync_type' => 'all',
            'status' => 'completed',
            'started_at' => now(),
            'completed_at' => now(),
            'success_count' => 10,
            'failure_count' => 0,
        ]);

        $this->assertCount(1, $connection->syncLogs);
        $this->assertEquals($log->id, $connection->syncLogs->first()->id);
    }

    /** @test */
    public function it_encrypts_sensitive_fields()
    {
        $business = Business::factory()->create();
        $connection = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id_plain',
            'client_secret' => 'test_client_secret_plain',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'access_token' => 'test_access_token_plain',
            'refresh_token' => 'test_refresh_token_plain',
            'status' => 'active',
        ]);

        // Check that values are encrypted in database
        $raw = \DB::table('zoho_connections')->where('id', $connection->id)->first();

        $this->assertNotEquals('test_client_id_plain', $raw->client_id);
        $this->assertNotEquals('test_client_secret_plain', $raw->client_secret);
        $this->assertNotEquals('test_access_token_plain', $raw->access_token);
        $this->assertNotEquals('test_refresh_token_plain', $raw->refresh_token);

        // Check that decrypted values match
        $this->assertEquals('test_client_id_plain', decrypt($raw->client_id));
        $this->assertEquals('test_client_secret_plain', decrypt($raw->client_secret));
        $this->assertEquals('test_access_token_plain', decrypt($raw->access_token));
        $this->assertEquals('test_refresh_token_plain', decrypt($raw->refresh_token));
    }

    /** @test */
    public function it_hides_sensitive_fields_in_json()
    {
        $business = Business::factory()->create();
        $connection = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'status' => 'active',
        ]);

        $json = $connection->toArray();

        $this->assertArrayNotHasKey('access_token', $json);
        $this->assertArrayNotHasKey('refresh_token', $json);
        $this->assertArrayNotHasKey('client_secret', $json);
    }

    /** @test */
    public function it_checks_if_token_is_expired()
    {
        $business = Business::factory()->create();

        // Token not expired
        $activeConnection = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'token_expires_at' => now()->addHour(),
            'status' => 'active',
        ]);

        $this->assertFalse($activeConnection->isTokenExpired());

        // Token expired
        $expiredConnection = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id_2',
            'client_secret' => 'test_client_secret_2',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'token_expires_at' => now()->subHour(),
            'status' => 'active',
        ]);

        $this->assertTrue($expiredConnection->isTokenExpired());
    }

    /** @test */
    public function it_checks_if_connection_is_active()
    {
        $business = Business::factory()->create();

        $activeConnection = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'access_token' => 'test_token',
            'status' => 'active',
        ]);

        $this->assertTrue($activeConnection->isActive());

        // Credentials set but not active
        $pendingConnection = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id_2',
            'client_secret' => 'test_client_secret_2',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'status' => 'credentials_set',
        ]);

        $this->assertFalse($pendingConnection->isActive());
    }

    /** @test */
    public function it_marks_connection_as_expired()
    {
        $business = Business::factory()->create();
        $connection = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'status' => 'active',
        ]);

        $connection->markExpired('Token expired');

        $this->assertEquals('expired', $connection->status);
        $this->assertEquals('Token expired', $connection->last_error);
    }

    /** @test */
    public function it_marks_connection_with_error()
    {
        $business = Business::factory()->create();
        $connection = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'status' => 'active',
        ]);

        $connection->markError('API error occurred');

        $this->assertEquals('error', $connection->status);
        $this->assertEquals('API error occurred', $connection->last_error);
    }

    /** @test */
    public function it_updates_tokens()
    {
        $business = Business::factory()->create();
        $connection = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'status' => 'credentials_set',
        ]);

        $connection->updateTokens('new_access_token', 'new_refresh_token', 7200);

        $connection->refresh();

        $this->assertEquals('active', $connection->status);
        $this->assertNotNull($connection->access_token);
        $this->assertNotNull($connection->refresh_token);
        $this->assertNotNull($connection->token_expires_at);
    }

    /** @test */
    public function it_checks_if_has_credentials()
    {
        $business = Business::factory()->create();

        $withCredentials = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'status' => 'credentials_set',
        ]);

        $this->assertTrue($withCredentials->hasCredentials());
        $this->assertTrue($withCredentials->hasValidCredentials());
    }

    /** @test */
    public function it_updates_sync_status()
    {
        $business = Business::factory()->create();
        $connection = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'status' => 'active',
        ]);

        $connection->updateSyncStatus('success');

        $connection->refresh();

        $this->assertEquals('success', $connection->last_sync_status);
        $this->assertNotNull($connection->last_synced_at);
    }

    /** @test */
    public function it_checks_if_sync_is_due()
    {
        $business = Business::factory()->create();

        // Never synced - should be due
        $neverSynced = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'access_token' => 'test_token',
            'status' => 'active',
            'auto_sync_enabled' => true,
            'sync_frequency' => 'daily',
        ]);

        $this->assertTrue($neverSynced->isSyncDue());

        // Synced recently - should not be due
        $recentlySynced = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id_2',
            'client_secret' => 'test_client_secret_2',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'access_token' => 'test_token_2',
            'status' => 'active',
            'auto_sync_enabled' => true,
            'sync_frequency' => 'daily',
            'last_synced_at' => now()->subHours(2),
        ]);

        $this->assertFalse($recentlySynced->isSyncDue());

        // Auto sync disabled - should not be due
        $autoSyncDisabled = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id_3',
            'client_secret' => 'test_client_secret_3',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'access_token' => 'test_token_3',
            'status' => 'active',
            'auto_sync_enabled' => false,
            'sync_frequency' => 'daily',
        ]);

        $this->assertFalse($autoSyncDisabled->isSyncDue());
    }

    /** @test */
    public function it_returns_correct_api_base_url_for_data_center()
    {
        $business = Business::factory()->create();

        $dataCenters = [
            'com' => 'https://books.zoho.com',
            'eu' => 'https://books.zoho.eu',
            'in' => 'https://books.zoho.in',
            'com.au' => 'https://books.zoho.com.au',
            'com.cn' => 'https://books.zoho.com.cn',
            'jp' => 'https://books.zoho.jp',
        ];

        foreach ($dataCenters as $dc => $expectedUrl) {
            $connection = ZohoConnection::create([
                'business_id' => $business->id,
                'client_id' => "test_client_id_$dc",
                'client_secret' => "test_client_secret_$dc",
                'redirect_uri' => 'https://example.com/callback',
                'data_center' => $dc,
                'status' => 'credentials_set',
            ]);

            $this->assertEquals($expectedUrl, $connection->getApiBaseUrl());
        }
    }

    /** @test */
    public function it_returns_correct_accounts_base_url_for_data_center()
    {
        $business = Business::factory()->create();

        $dataCenters = [
            'com' => 'https://accounts.zoho.com',
            'eu' => 'https://accounts.zoho.eu',
            'in' => 'https://accounts.zoho.in',
            'com.au' => 'https://accounts.zoho.com.au',
            'com.cn' => 'https://accounts.zoho.com.cn',
            'jp' => 'https://accounts.zoho.jp',
        ];

        foreach ($dataCenters as $dc => $expectedUrl) {
            $connection = ZohoConnection::create([
                'business_id' => $business->id,
                'client_id' => "test_client_id_$dc",
                'client_secret' => "test_client_secret_$dc",
                'redirect_uri' => 'https://example.com/callback',
                'data_center' => $dc,
                'status' => 'credentials_set',
            ]);

            $this->assertEquals($expectedUrl, $connection->getAccountsBaseUrl());
        }
    }

    /** @test */
    public function it_has_credentials_attribute()
    {
        $business = Business::factory()->create();
        $connection = ZohoConnection::create([
            'business_id' => $business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'https://example.com/callback',
            'data_center' => 'com',
            'status' => 'credentials_set',
        ]);

        $this->assertTrue($connection->has_credentials);
        $this->assertArrayHasKey('has_credentials', $connection->toArray());
    }
}
