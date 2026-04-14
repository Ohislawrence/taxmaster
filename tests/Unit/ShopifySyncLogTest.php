<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ShopifyConnection;
use App\Models\ShopifySyncLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ShopifySyncLogTest extends TestCase
{
    use RefreshDatabase;

    protected ShopifyConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = ShopifyConnection::create([
            'business_id' => \App\Models\Business::factory()->create()->id,
            'shop_domain' => 'teststore.myshopify.com',
            'access_token' => encrypt('test_token'),
        ]);
    }

    /** @test */
    public function it_belongs_to_a_shopify_connection()
    {
        $syncLog = ShopifySyncLog::create([
            'shopify_connection_id' => $this->connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'completed',
            'started_at' => now(),
        ]);

        $this->assertInstanceOf(ShopifyConnection::class, $syncLog->shopifyConnection);
        $this->assertEquals($this->connection->id, $syncLog->shopifyConnection->id);
    }

    /** @test */
    public function it_detects_completed_sync()
    {
        $syncLog = ShopifySyncLog::create([
            'shopify_connection_id' => $this->connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'completed',
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $this->assertTrue($syncLog->isComplete());
    }

    /** @test */
    public function it_detects_incomplete_sync()
    {
        $syncLog = ShopifySyncLog::create([
            'shopify_connection_id' => $this->connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'processing',
            'started_at' => now(),
        ]);

        $this->assertFalse($syncLog->isComplete());
    }

    /** @test */
    public function it_detects_successful_sync()
    {
        $syncLog = ShopifySyncLog::create([
            'shopify_connection_id' => $this->connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'completed',
            'total_records' => 10,
            'success_count' => 10,
            'failure_count' => 0,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $this->assertTrue($syncLog->wasSuccessful());
    }

    /** @test */
    public function it_detects_failed_sync()
    {
        $syncLog = ShopifySyncLog::create([
            'shopify_connection_id' => $this->connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'failed',
            'total_records' => 10,
            'success_count' => 5,
            'failure_count' => 5,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $this->assertFalse($syncLog->wasSuccessful());
    }

    /** @test */
    public function it_calculates_progress_percentage()
    {
        $syncLog = ShopifySyncLog::create([
            'shopify_connection_id' => $this->connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'processing',
            'total_records' => 100,
            'processed_records' => 50,
            'started_at' => now(),
        ]);

        $this->assertEquals(50, $syncLog->getProgressPercentage());
    }

    /** @test */
    public function it_returns_zero_percentage_when_no_total_records()
    {
        $syncLog = ShopifySyncLog::create([
            'shopify_connection_id' => $this->connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'processing',
            'total_records' => 0,
            'processed_records' => 0,
            'started_at' => now(),
        ]);

        $this->assertEquals(0, $syncLog->getProgressPercentage());
    }

    /** @test */
    public function it_returns_hundred_percentage_when_complete()
    {
        $syncLog = ShopifySyncLog::create([
            'shopify_connection_id' => $this->connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'completed',
            'total_records' => 100,
            'processed_records' => 100,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $this->assertEquals(100, $syncLog->getProgressPercentage());
    }

    /** @test */
    public function it_stores_errors_as_array()
    {
        $errors = [
            ['order_id' => 123, 'error' => 'Invalid data'],
            ['order_id' => 456, 'error' => 'Missing field'],
        ];

        $syncLog = ShopifySyncLog::create([
            'shopify_connection_id' => $this->connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'failed',
            'started_at' => now(),
            'errors' => $errors,
        ]);

        $this->assertEquals($errors, $syncLog->errors);
        $this->assertIsArray($syncLog->errors);
    }

    /** @test */
    public function it_stores_summary_as_array()
    {
        $summary = [
            'total_value' => 50000,
            'currency' => 'NGN',
            'date_range' => '2026-03-01 to 2026-04-01',
        ];

        $syncLog = ShopifySyncLog::create([
            'shopify_connection_id' => $this->connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'completed',
            'started_at' => now(),
            'summary' => $summary,
        ]);

        $this->assertEquals($summary, $syncLog->summary);
        $this->assertIsArray($syncLog->summary);
    }

    /** @test */
    public function it_tracks_different_entity_types()
    {
        $orderLog = ShopifySyncLog::create([
            'shopify_connection_id' => $this->connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'completed',
            'started_at' => now(),
        ]);

        $productLog = ShopifySyncLog::create([
            'shopify_connection_id' => $this->connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'product',
            'status' => 'completed',
            'started_at' => now(),
        ]);

        $this->assertEquals('order', $orderLog->entity_type);
        $this->assertEquals('product', $productLog->entity_type);
    }

    /** @test */
    public function it_tracks_different_sync_types()
    {
        $manualLog = ShopifySyncLog::create([
            'shopify_connection_id' => $this->connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'completed',
            'started_at' => now(),
        ]);

        $autoLog = ShopifySyncLog::create([
            'shopify_connection_id' => $this->connection->id,
            'sync_type' => 'auto',
            'entity_type' => 'order',
            'status' => 'completed',
            'started_at' => now(),
        ]);

        $this->assertEquals('manual', $manualLog->sync_type);
        $this->assertEquals('auto', $autoLog->sync_type);
    }
}
