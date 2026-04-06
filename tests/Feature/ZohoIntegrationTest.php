<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Business;
use App\Models\SubscriptionPlan;
use App\Models\BusinessSubscription;
use App\Models\ZohoConnection;
use App\Models\ZohoSyncLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;

class ZohoIntegrationTest extends TestCase
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

        // Reload user to refresh relationships
        $this->user->refresh();

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
    public function it_displays_zoho_integration_page()
    {
        $response = $this->actingAs($this->user)
            ->withSession(['business_id' => $this->business->id])
            ->get(route('business.integrations.zoho.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Business/Integrations/Zoho')
            ->has('connection')
            ->has('syncLogs')
        );
    }

    /** @test */
    public function it_redirects_to_business_setup_if_no_business()
    {
        $userWithoutBusiness = User::factory()->create();

        $response = $this->actingAs($userWithoutBusiness)
            ->get(route('business.integrations.zoho.index'));

        $response->assertRedirect(route('business.setup'));
        $response->assertSessionHas('error', 'Please complete your business setup first.');
    }

    /** @test */
    public function it_saves_zoho_credentials()
    {
        $credentials = [
            'client_id' => '1000.' . $this->faker->uuid,
            'client_secret' => 'test_client_secret_' . $this->faker->uuid,
            'redirect_uri' => url('/business/integrations/zoho/callback'),
            'data_center' => 'com',
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['business_id' => $this->business->id])
            ->post(route('business.integrations.zoho.save-credentials'), $credentials);

        $response->assertRedirect(route('business.integrations.zoho.index'));
        $response->assertSessionHas('message', 'Zoho credentials saved successfully. You can now connect your Zoho Books account.');

        $this->assertDatabaseHas('zoho_connections', [
            'business_id' => $this->business->id,
            'data_center' => 'com',
            'status' => 'credentials_set',
        ]);
    }

    /** @test */
    public function it_validates_required_credentials_fields()
    {
        $response = $this->actingAs($this->user)
            ->withSession(['business_id' => $this->business->id])
            ->post(route('business.integrations.zoho.save-credentials'), [
                'client_id' => '',
                'client_secret' => '',
                'redirect_uri' => '',
                'data_center' => '',
            ]);

        $response->assertSessionHasErrors(['client_id', 'client_secret', 'redirect_uri', 'data_center']);
    }

    /** @test */
    public function it_validates_data_center_is_valid()
    {
        $response = $this->actingAs($this->user)
            ->withSession(['business_id' => $this->business->id])
            ->post(route('business.integrations.zoho.save-credentials'), [
                'client_id' => '1000.TEST123',
                'client_secret' => 'secret123',
                'redirect_uri' => url('/business/integrations/zoho/callback'),
                'data_center' => 'invalid',
            ]);

        $response->assertSessionHasErrors(['data_center']);
    }

    /** @test */
    public function it_accepts_valid_data_centers()
    {
        $validDataCenters = ['com', 'eu', 'in', 'com.au', 'com.cn', 'jp'];

        foreach ($validDataCenters as $dataCenter) {
            $credentials = [
                'client_id' => '1000.' . $this->faker->uuid,
                'client_secret' => 'test_client_secret_' . $this->faker->uuid,
                'redirect_uri' => url('/business/integrations/zoho/callback'),
                'data_center' => $dataCenter,
            ];

            $response = $this->actingAs($this->user)
                ->withSession(['business_id' => $this->business->id])
                ->post(route('business.integrations.zoho.save-credentials'), $credentials);

            $response->assertRedirect();
            $response->assertSessionHasNoErrors();
        }
    }

    /** @test */
    public function it_updates_existing_credentials()
    {
        // Create initial connection
        $connection = ZohoConnection::create([
            'business_id' => $this->business->id,
            'client_id' => 'old_client_id',
            'client_secret' => 'old_client_secret',
            'redirect_uri' => url('/business/integrations/zoho/callback'),
            'data_center' => 'com',
            'status' => 'credentials_set',
        ]);

        $newCredentials = [
            'client_id' => '1000.NEW_CLIENT_ID',
            'client_secret' => 'new_client_secret',
            'redirect_uri' => url('/business/integrations/zoho/callback'),
            'data_center' => 'eu',
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['business_id' => $this->business->id])
            ->post(route('business.integrations.zoho.save-credentials'), $newCredentials);

        $response->assertRedirect();

        $connection->refresh();
        $this->assertEquals('eu', $connection->data_center);
        $this->assertEquals('credentials_set', $connection->status);
    }

    /** @test */
    public function it_disconnects_zoho_connection()
    {
        $connection = ZohoConnection::create([
            'business_id' => $this->business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => url('/business/integrations/zoho/callback'),
            'data_center' => 'com',
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['business_id' => $this->business->id])
            ->post(route('business.integrations.zoho.disconnect'));

        $response->assertRedirect(route('business.integrations.zoho.index'));
        $response->assertSessionHas('message', 'Zoho Books disconnected successfully.');

        $connection->refresh();
        $this->assertEquals('revoked', $connection->status);
        $this->assertNull($connection->access_token);
        $this->assertNull($connection->refresh_token);
    }

    /** @test */
    public function it_requires_credentials_before_connecting()
    {
        $response = $this->actingAs($this->user)
            ->get(route('business.integrations.zoho.connect'));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Please configure your Zoho credentials first.');
    }

    /** @test */
    public function it_generates_oauth_url_with_correct_parameters()
    {
        $connection = ZohoConnection::create([
            'business_id' => $this->business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => url('/business/integrations/zoho/callback'),
            'data_center' => 'com',
            'status' => 'credentials_set',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('business.integrations.zoho.connect'));

        $response->assertRedirect();

        // Check that redirect URL contains Zoho OAuth URL
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('https://accounts.zoho.com/oauth/v2/auth', $redirectUrl);
        $this->assertStringContainsString('scope=ZohoBooks.fullaccess.all', $redirectUrl);
    }

    /** @test */
    public function it_updates_sync_settings()
    {
        $connection = ZohoConnection::create([
            'business_id' => $this->business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => url('/business/integrations/zoho/callback'),
            'data_center' => 'com',
            'status' => 'active',
            'auto_sync_enabled' => false,
            'sync_frequency' => 'daily',
        ]);

        $settings = [
            'auto_sync_enabled' => true,
            'sync_frequency' => 'hourly',
            'sync_settings' => [
                'sync_invoices' => true,
                'sync_bills' => false,
            ],
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['business_id' => $this->business->id])
            ->patch(route('business.integrations.zoho.update-settings'), $settings);

        $response->assertRedirect(route('business.integrations.zoho.index'));
        $response->assertSessionHas('message', 'Sync settings updated successfully.');

        $connection->refresh();
        $this->assertTrue($connection->auto_sync_enabled);
        $this->assertEquals('hourly', $connection->sync_frequency);
        $this->assertTrue($connection->sync_settings['sync_invoices']);
        $this->assertFalse($connection->sync_settings['sync_bills']);
    }

    /** @test */
    public function it_validates_sync_frequency()
    {
        $connection = ZohoConnection::create([
            'business_id' => $this->business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => url('/business/integrations/zoho/callback'),
            'data_center' => 'com',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['business_id' => $this->business->id])
            ->patch(route('business.integrations.zoho.update-settings'), [
                'auto_sync_enabled' => true,
                'sync_frequency' => 'invalid',
                'sync_settings' => [
                    'sync_invoices' => true,
                    'sync_bills' => true,
                ],
            ]);

        $response->assertSessionHasErrors(['sync_frequency']);
    }

    /** @test */
    public function it_requires_active_connection_for_sync()
    {
        $connection = ZohoConnection::create([
            'business_id' => $this->business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => url('/business/integrations/zoho/callback'),
            'data_center' => 'com',
            'status' => 'credentials_set', // Not active
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['business_id' => $this->business->id])
            ->post(route('business.integrations.zoho.sync'), [
                'date_range' => 'last_30_days',
                'sync_type' => 'all',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Zoho connection is not active');
    }

    /** @test */
    public function it_displays_connection_in_integrations_list()
    {
        $connection = ZohoConnection::create([
            'business_id' => $this->business->id,
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => url('/business/integrations/zoho/callback'),
            'data_center' => 'com',
            'organization_name' => 'Test Organization',
            'status' => 'active',
            'last_synced_at' => now(),
        ]);

        $response = $this->actingAs($this->user)            ->withSession(['business_id' => $this->business->id])            ->get(route('business.integrations.index'));

        $response->assertStatus(200);

        // Check that Zoho is shown as connected
        $response->assertInertia(fn ($page) => $page
            ->component('Business/Integrations/Index')
            ->where('integrations.1.slug', 'zoho')
            ->where('integrations.1.status', 'connected')
        );
    }

    /** @test */
    public function it_shows_zoho_as_available_when_not_connected()
    {
        $response = $this->actingAs($this->user)
            ->withSession(['business_id' => $this->business->id])
            ->get(route('business.integrations.index'));

        $response->assertStatus(200);

        // Check that Zoho is shown as available
        $response->assertInertia(fn ($page) => $page
            ->component('Business/Integrations/Index')
            ->where('integrations.1.slug', 'zoho')
            ->where('integrations.1.status', 'available')
        );
    }
}
