<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Business;

class ManagesBusinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_who_owns_business_is_considered_manager()
    {
        $user = User::factory()->create();
        $business = Business::create([
            'owner_id' => $user->id,
            'name' => 'Acme Co',
            'slug' => 'acme-co',
            'registration_number' => 'REG-ACME-001',
            'business_type' => 'company',
            'email' => 'owner+acme@example.test',
            'phone' => '+234000000001',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'address' => '1 Acme Street',
            'industry' => 'services',
        ]);

        $this->assertTrue($user->managesBusiness($business->id));
    }

    public function test_user_assigned_via_pivot_is_considered_manager()
    {
        $owner = User::factory()->create();
        $acct = User::factory()->create();

        $business = Business::create([
            'owner_id' => $owner->id,
            'name' => 'Client Ltd',
            'slug' => 'client-ltd',
            'registration_number' => 'REG-CLIENT-001',
            'business_type' => 'company',
            'email' => 'owner+client@example.test',
            'phone' => '+234000000002',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'address' => '2 Client Road',
            'industry' => 'tech',
        ]);

        // attach via pivot
        $acct->managedBusinesses()->attach($business->id);

        $this->assertTrue($acct->managesBusiness($business->id));
        $this->assertFalse($owner->managesBusiness(999999));
    }

    public function test_user_not_related_returns_false()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $business = Business::create([
            'owner_id' => $other->id,
            'name' => 'Other Biz',
            'slug' => 'other-biz',
            'registration_number' => 'REG-OTHER-001',
            'business_type' => 'company',
            'email' => 'owner+other@example.test',
            'phone' => '+234000000003',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'address' => '3 Other Ave',
            'industry' => 'retail',
        ]);

        $this->assertFalse($user->managesBusiness($business->id));
    }
}
