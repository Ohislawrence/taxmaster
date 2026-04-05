<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Business;
use App\Models\SubscriptionPlan;
use App\Models\BusinessSubscription;
use App\Models\QuickBooksConnection;
use App\Models\QuickBooksSyncLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;

class QuickBooksIntegrationTest extends TestCase
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
    public function it_displays_quickbooks_integration_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('business.integrations.quickbooks.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Business/Integrations/QuickBooks')
            ->has('connection')
        );
    }

    /** @test */
    public function it_redirects_to_business_setup_if_no_business()
    {
        $userWithoutBusiness = User::factory()->create();

        $response = $this->actingAs($userWithoutBusiness)
            ->get(route('business.integrations.quickbooks.index'));

        $response->assertRedirect(route('business.setup'));
        $response->assertSessionHas('error', 'Please complete your business setup first.');
    }

    /** @test */
    public function it_saves_quickbooks_credentials()
    {
        $credentials = [
            'client_id' => 'test_client_id_' . $this->faker->uuid,
            'client_secret' => 'test_client_secret_' . $this->faker->uuid,
            'redirect_uri' => 'https://example.com/callback',
            'environment' => 'sandbox',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('business.integrations.quickbooks.save-credentials'), $credentials);

        $response->assertRedirect();
        $response->assertSessionHas('message', 'QuickBooks credentials saved successfully.');

        $this->assertDatabaseHas('quickbooks_connections', [
            'business_id' => $this->business->id,
            'environment' => 'sandbox',
            'redirect_uri' => 'https://example.com/callback',
            'status' => 'credentials_set',
        ]);
    }

    /** @test */
    public function it_validates_required_credentials_fields()
    {
        $response = $this->actingAs($this->user)
            ->post(route('business.integrations.quickbooks.save-credentials'), [
                'client_id' => '',
                'client_secret' => '',
                'redirect_uri' => '',
            ]);

        $response->assertSessionHasErrors(['client_id', 'client_secret', 'redirect_uri']);
    }

    /** @test */
    public function it_disconnects_quickbooks_connection()
    {
        $connection = QuickBooksConnection::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('business.integrations.quickbooks.disconnect'));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Successfully disconnected from QuickBooks.');

        $connection->refresh();
        $this->assertEquals('revoked', $connection->status);
    }

    /** @test */
    public function it_updates_sync_settings()
    {
        $connection = QuickBooksConnection::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'active',
        ]);

        $settings = [
            'auto_sync_enabled' => true,
            'sync_frequency' => 'daily',
            'sync_invoices' => true,
            'sync_bills' => true,
            'sync_customers' => true,
            'sync_vendors' => false,
        ];

        $response = $this->actingAs($this->user)
            ->patch(route('business.integrations.quickbooks.update-settings'), $settings);

        $response->assertRedirect();
        $response->assertSessionHas('message', 'Sync settings updated successfully.');

        $connection->refresh();
        $this->assertTrue($connection->auto_sync_enabled);
        $this->assertEquals('daily', $connection->sync_frequency);
        $this->assertEquals([
            'sync_invoices' => true,
            'sync_bills' => true,
            'sync_customers' => true,
            'sync_vendors' => false,
        ], $connection->sync_settings);
    }

    /** @test */
    public function it_shows_connection_with_sync_logs()
    {
        $connection = QuickBooksConnection::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'active',
            'company_name' => 'Test Company',
        ]);

        // Create some sync logs
        QuickBooksSyncLog::factory()->count(5)->create([
            'quickbooks_connection_id' => $connection->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('business.integrations.quickbooks.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Business/Integrations/QuickBooks')
            ->has('connection')
            ->where('connection.company_name', 'Test Company')
            ->where('connection.status', 'active')
            ->has('syncLogs', 5)
        );
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->get(route('business.integrations.quickbooks.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function expired_connection_shows_correct_status()
    {
        $connection = QuickBooksConnection::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'active',
            'refresh_token_expires_at' => Carbon::now()->subDay(),
        ]);

        $this->assertFalse($connection->isActive());

        $response = $this->actingAs($this->user)
            ->get(route('business.integrations.quickbooks.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Business/Integrations/QuickBooks')
            ->where('connection.is_active', false)
        );
    }

    /** @test */
    public function it_checks_if_sync_is_due()
    {
        $connection = QuickBooksConnection::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'active',
            'auto_sync_enabled' => true,
            'sync_frequency' => 'daily',
            'last_synced_at' => Carbon::now()->subDays(2),
        ]);

        $this->assertTrue($connection->isSyncDue());

        $response = $this->actingAs($this->user)
            ->get(route('business.integrations.quickbooks.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('connection.is_sync_due', true)
        );
    }
}
