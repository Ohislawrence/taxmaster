<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Business;

class BusinessSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_switch_to_unmanaged_business()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        // give the user an owned business so middleware for business setup does not redirect
        Business::create([
            'owner_id' => $user->id,
            'name' => 'Owned Co',
            'slug' => 'owned-co',
            'registration_number' => 'REG-OWNED-001',
            'business_type' => 'company',
            'email' => 'owner+owned@example.test',
            'phone' => '+234000000099',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'address' => '99 Owned St',
            'industry' => 'services',
        ]);

        $business = Business::create([
            'owner_id' => $other->id,
            'name' => 'Private Co',
            'slug' => 'private-co',
            'registration_number' => 'REG-PRIVATE-001',
            'business_type' => 'company',
            'email' => 'owner+private@example.test',
            'phone' => '+234000000004',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'address' => '4 Private Rd',
            'industry' => 'services',
        ]);

        $response = $this->actingAs($user)->post(route('business.switch'), [
            'business_id' => $business->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_can_switch_to_managed_business()
    {
        $owner = User::factory()->create();
        $acct = User::factory()->create();

        $business = Business::create([
            'owner_id' => $owner->id,
            'name' => 'Client Co',
            'slug' => 'client-co',
            'registration_number' => 'REG-CLIENT-002',
            'business_type' => 'company',
            'email' => 'owner+client@example.test',
            'phone' => '+234000000005',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'address' => '5 Client Blvd',
            'industry' => 'tech',
        ]);

        $acct->managedBusinesses()->attach($business->id);

        // ensure the accountant has an owned business so middleware does not redirect
        Business::create([
            'owner_id' => $acct->id,
            'name' => 'Acct Owned',
            'slug' => 'acct-owned',
            'registration_number' => 'REG-ACCT-001',
            'business_type' => 'company',
            'email' => 'acct+owned@example.test',
            'phone' => '+234000000010',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'address' => '10 Acct Rd',
            'industry' => 'services',
        ]);

        $response = $this->actingAs($acct)->post(route('business.switch'), [
            'business_id' => $business->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('business_id', $business->id);
    }
}
