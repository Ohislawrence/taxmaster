<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Business;
use App\Models\SubscriptionPlan;
use App\Models\BusinessSubscription;
use App\Models\ShopifyConnection;
use App\Models\ShopifySyncLog;
use App\Services\ShopifyIntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ShopifyIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a subscription plan with required features
        $plan = SubscriptionPlan::create([
            'name' => 'Professional',
            'slug' => 'professional',
            'description' => 'Professional plan for testing',
            'price' => 50000,
            'billing_cycle' => 'monthly',
            'staff_limit' => 10,
            'returns_limit' => 100,
            'features' => json_encode([
                'link_bank_account',
                'file_vat',
                'file_cit',
                'use_ai_chat',
                'export_pdf',
            ]),
            'is_active' => true,
        ]);

        // Create a user and business
        $this->user = User::factory()->create();
        $this->business = Business::factory()->create([
            'owner_id' => $this->user->id,
        ]);

        // Create active subscription for the business
        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_id' => $plan->id,
            'plan_type' => 'professional',
            'monthly_price' => 50000.00,
            'annual_price' => 500000.00,
            'max_staff_members' => 10,
            'max_returns_per_year' => 100,
            'ai_analysis_included' => true,
            'payment_automation' => true,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'started_at' => Carbon::now()->subDays(5),
            'renews_at' => Carbon::now()->addDays(25),
        ]);
    }

    /** @test */
    public function it_displays_shopify_integration_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('business.integrations.shopify.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Business/Integrations/Shopify')
            ->has('connection')
            ->has('syncLogs')
        );
    }

    /** @test */
    public function it_redirects_to_business_setup_if_no_business()
    {
        $userWithoutBusiness = User::factory()->create();

        $response = $this->actingAs($userWithoutBusiness)
            ->get(route('business.integrations.shopify.index'));

        $response->assertRedirect(route('business.setup'));
        $response->assertSessionHas('error', 'Please complete your business setup first.');
    }

    /** @test */
    public function it_saves_shopify_credentials_and_verifies_connection()
    {
        Http::fake([
            '*/admin/api/*/shop.json' => Http::response([
                'shop' => [
                    'name' => 'Test Store',
                    'email' => 'test@example.com',
                    'currency' => 'NGN',
                    'shop_owner' => 'Test Owner',
                    'plan_name' => 'Basic',
                    'timezone' => 'Africa/Lagos',
                ]
            ], 200),
        ]);

        $credentials = [
            'shop_domain' => 'teststore.myshopify.com',
            'access_token' => 'shpat_test_token_' . $this->faker->uuid,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('business.integrations.shopify.save-credentials'), $credentials);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shopify_connections', [
            'business_id' => $this->business->id,
            'shop_domain' => 'teststore.myshopify.com',
            'shop_name' => 'Test Store',
            'shop_email' => 'test@example.com',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_validates_shop_domain_format()
    {
        $response = $this->actingAs($this->user)
            ->post(route('business.integrations.shopify.save-credentials'), [
                'shop_domain' => 'invalid-domain.com',
                'access_token' => 'test_token',
            ]);

        $response->assertSessionHasErrors(['shop_domain']);
    }

    /** @test */
    public function it_validates_required_credentials_fields()
    {
        $response = $this->actingAs($this->user)
            ->post(route('business.integrations.shopify.save-credentials'), [
                'shop_domain' => '',
                'access_token' => '',
            ]);

        $response->assertSessionHasErrors(['shop_domain', 'access_token']);
    }

    /** @test */
    public function it_disconnects_shopify_connection()
    {
        $connection = ShopifyConnection::create([
            'business_id' => $this->business->id,
            'shop_domain' => 'teststore.myshopify.com',
            'shop_name' => 'Test Store',
            'access_token' => encrypt('test_token'),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('business.integrations.shopify.disconnect'));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Successfully disconnected from Shopify.');

        $connection->refresh();
        $this->assertEquals('revoked', $connection->status);
        $this->assertNull($connection->access_token);
    }

    /** @test */
    public function it_requires_active_connection_to_sync()
    {
        $connection = ShopifyConnection::create([
            'business_id' => $this->business->id,
            'shop_domain' => 'teststore.myshopify.com',
            'access_token' => encrypt('test_token'),
            'status' => 'revoked',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('business.integrations.shopify.sync'), [
                'date_range' => 'last_30_days',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Shopify connection is not active. Please reconnect.');
    }

    /** @test */
    public function it_triggers_manual_sync()
    {
        $connection = ShopifyConnection::create([
            'business_id' => $this->business->id,
            'shop_domain' => 'teststore.myshopify.com',
            'shop_name' => 'Test Store',
            'access_token' => encrypt('test_token'),
            'status' => 'active',
        ]);

        Http::fake([
            '*/admin/api/*/orders.json*' => Http::response([
                'orders' => [
                    [
                        'id' => 123456,
                        'order_number' => 1001,
                        'name' => '#1001',
                        'total_price' => '15000.00',
                        'currency' => 'NGN',
                        'created_at' => now()->toIso8601String(),
                        'financial_status' => 'paid',
                        'fulfillment_status' => 'fulfilled',
                        'customer' => [
                            'first_name' => 'John',
                            'last_name' => 'Doe',
                            'email' => 'john@example.com',
                        ],
                        'tax_lines' => [
                            ['price' => '1125.00', 'title' => 'VAT'],
                        ],
                        'line_items' => [
                            ['id' => 1, 'title' => 'Product 1', 'quantity' => 1],
                        ],
                    ],
                ],
            ], 200, ['Link' => '']),
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('business.integrations.shopify.sync'), [
                'date_range' => 'last_30_days',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify sync log created
        $this->assertDatabaseHas('shopify_sync_logs', [
            'shopify_connection_id' => $connection->id,
            'entity_type' => 'order',
            'status' => 'completed',
        ]);

        // Verify transaction created
        $this->assertDatabaseHas('transactions', [
            'business_id' => $this->business->id,
            'external_id' => 'shopify_order_123456',
            'type' => 'INCOME',
            'category' => 'VAT_OUTPUT',
            'amount' => 15000.00,
        ]);
    }

    /** @test */
    public function it_updates_sync_settings()
    {
        $connection = ShopifyConnection::create([
            'business_id' => $this->business->id,
            'shop_domain' => 'teststore.myshopify.com',
            'access_token' => encrypt('test_token'),
            'status' => 'active',
            'auto_sync_enabled' => false,
            'sync_frequency' => 'daily',
        ]);

        $response = $this->actingAs($this->user)
            ->patch(route('business.integrations.shopify.update-settings'), [
                'auto_sync_enabled' => true,
                'sync_frequency' => 'hourly',
                'sync_settings' => [
                    'sync_orders' => true,
                    'sync_products' => false,
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Sync settings updated successfully.');

        $connection->refresh();
        $this->assertTrue($connection->auto_sync_enabled);
        $this->assertEquals('hourly', $connection->sync_frequency);
        $this->assertEquals(['sync_orders' => true, 'sync_products' => false], $connection->sync_settings);
    }

    /** @test */
    public function it_retrieves_sync_log_details()
    {
        $connection = ShopifyConnection::create([
            'business_id' => $this->business->id,
            'shop_domain' => 'teststore.myshopify.com',
            'access_token' => encrypt('test_token'),
            'status' => 'active',
        ]);

        $syncLog = ShopifySyncLog::create([
            'shopify_connection_id' => $connection->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'completed',
            'total_records' => 10,
            'processed_records' => 10,
            'success_count' => 10,
            'failure_count' => 0,
            'started_at' => now(),
            'completed_at' => now(),
            'duration_seconds' => 5,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('business.integrations.shopify.sync-log', $syncLog->id));

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $syncLog->id,
            'sync_type' => 'manual',
            'entity_type' => 'order',
            'status' => 'completed',
            'success_count' => 10,
        ]);
    }

    /** @test */
    public function it_shows_shopify_in_integrations_hub()
    {
        $response = $this->actingAs($this->user)
            ->get(route('business.integrations.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Business/Integrations/Index')
            ->has('integrations')
            ->where('integrations', fn ($integrations) =>
                collect($integrations)->contains('slug', 'shopify')
            )
        );
    }
}
