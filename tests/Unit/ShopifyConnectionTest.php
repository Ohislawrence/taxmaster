<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Business;
use App\Models\ShopifyConnection;
use App\Models\ShopifySyncLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ShopifyConnectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_business()
    {
        $business = Business::factory()->create();
        $connection = ShopifyConnection::create([
            'business_id' => $business->id,
            'shop_domain' => 'teststore.myshopify.com',
            'access_token' => encrypt('test_token'),
        ]);

        $this->assertInstanceOf(Business::class, $connection->business);
        $this->assertEquals($business->id, $connection->business->id);
    }

    /** @test */
    public function it_has_many_sync_logs()
    {
        $connection = ShopifyConnection::create([
            'business_id' => Business::factory()->create()->id,
            'shop_domain' => 'teststore.myshopify.com',
            'access_token' => encrypt('test_token'),
        ]);

        ShopifySyncLog::create([
            'shopify_connection_id' => $connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'completed',
            'started_at' => now(),
        ]);

        ShopifySyncLog::create([
            'shopify_connection_id' => $connection->id,
            'sync_type' => 'auto',
            'entity_type' => 'product',
            'status' => 'completed',
            'started_at' => now(),
        ]);

        $this->assertCount(2, $connection->syncLogs);
        $this->assertInstanceOf(ShopifySyncLog::class, $connection->syncLogs->first());
    }

    /** @test */
    public function it_detects_active_connection()
    {
        $connection = ShopifyConnection::create([
            'business_id' => Business::factory()->create()->id,
            'shop_domain' => 'teststore.myshopify.com',
            'access_token' => encrypt('test_token'),
            'status' => 'active',
        ]);

        $this->assertTrue($connection->isActive());
    }

    /** @test */
    public function inactive_status_makes_connection_not_active()
    {
        $connection = ShopifyConnection::create([
            'business_id' => Business::factory()->create()->id,
            'shop_domain' => 'teststore.myshopify.com',
            'access_token' => encrypt('test_token'),
            'status' => 'revoked',
        ]);

        $this->assertFalse($connection->isActive());
    }

    /** @test */
    public function it_checks_if_credentials_are_set()
    {
        $connection = ShopifyConnection::create([
            'business_id' => Business::factory()->create()->id,
            'shop_domain' => 'teststore.myshopify.com',
            'access_token' => encrypt('test_token'),
        ]);

        $this->assertTrue($connection->has_credentials);
    }

    /** @test */
    public function it_detects_missing_credentials()
    {
        $connection = ShopifyConnection::create([
            'business_id' => Business::factory()->create()->id,
            'shop_domain' => 'teststore.myshopify.com',
        ]);

        $this->assertFalse($connection->has_credentials);
    }

    /** @test */
    public function it_generates_admin_url()
    {
        $connection = ShopifyConnection::create([
            'business_id' => Business::factory()->create()->id,
            'shop_domain' => 'teststore.myshopify.com',
            'access_token' => encrypt('test_token'),
        ]);

        $this->assertEquals('https://teststore.myshopify.com/admin', $connection->admin_url);
    }

    /** @test */
    public function it_updates_sync_stats()
    {
        $connection = ShopifyConnection::create([
            'business_id' => Business::factory()->create()->id,
            'shop_domain' => 'teststore.myshopify.com',
            'access_token' => encrypt('test_token'),
            'total_orders_synced' => 0,
            'total_products_synced' => 0,
            'total_customers_synced' => 0,
        ]);

        $connection->updateSyncStats(10, 5, 3);
        $connection->refresh();

        $this->assertEquals(10, $connection->total_orders_synced);
        $this->assertEquals(5, $connection->total_products_synced);
        $this->assertEquals(3, $connection->total_customers_synced);
        $this->assertNotNull($connection->first_sync_at);
    }

    /** @test */
    public function it_increments_existing_sync_stats()
    {
        $connection = ShopifyConnection::create([
            'business_id' => Business::factory()->create()->id,
            'shop_domain' => 'teststore.myshopify.com',
            'access_token' => encrypt('test_token'),
            'total_orders_synced' => 5,
            'total_products_synced' => 3,
            'total_customers_synced' => 2,
            'first_sync_at' => Carbon::yesterday(),
        ]);

        $connection->updateSyncStats(10, 5, 3);
        $connection->refresh();

        $this->assertEquals(15, $connection->total_orders_synced);
        $this->assertEquals(8, $connection->total_products_synced);
        $this->assertEquals(5, $connection->total_customers_synced);
    }

    /** @test */
    public function it_marks_connection_as_expired()
    {
        $connection = ShopifyConnection::create([
            'business_id' => Business::factory()->create()->id,
            'shop_domain' => 'teststore.myshopify.com',
            'access_token' => encrypt('test_token'),
            'status' => 'active',
        ]);

        $connection->markExpired();
        $connection->refresh();

        $this->assertEquals('expired', $connection->status);
    }

    /** @test */
    public function it_encrypts_access_token()
    {
        $connection = ShopifyConnection::create([
            'business_id' => Business::factory()->create()->id,
            'shop_domain' => 'teststore.myshopify.com',
            'access_token' => 'plain_text_token',
        ]);

        $raw = \DB::table('shopify_connections')->where('id', $connection->id)->first();

        // Access token should be encrypted in database
        $this->assertNotEquals('plain_text_token', $raw->access_token);

        // But should decrypt properly when accessed via model
        $this->assertEquals('plain_text_token', $connection->access_token);
    }
}
