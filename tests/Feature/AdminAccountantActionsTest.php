<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Business;
use Spatie\Permission\Models\Role;

class AdminAccountantActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist for tests
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'business', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
    }

    public function test_admin_can_detach_accountant_from_business()
    {
        // ensure roles exist
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'business', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $acct = User::factory()->create();
        $acct->assignRole('accountant');

        $business = Business::create([
            'owner_id' => $admin->id,
            'name' => 'Detach Biz',
            'slug' => 'detach-biz',
            'registration_number' => 'REG-DET-001',
            'business_type' => 'company',
            'email' => 'detach@example.test',
            'phone' => '+234000000011',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'address' => '11 Detach Rd',
            'industry' => 'services',
        ]);

        $acct->managedBusinesses()->attach($business->id);

        // allow admin manageUsers gate for test
        \Illuminate\Support\Facades\Gate::define('manageUsers', function ($user) {
            return true;
        });

        $this->actingAs($admin)->post("/admin/accountants/{$acct->id}/detach/{$business->id}");

        $this->assertDatabaseMissing('accountant_business', [
            'user_id' => $acct->id,
            'business_id' => $business->id,
        ]);
    }

    public function test_admin_can_enable_billing_and_transfer_ownership()
    {
        // ensure roles exist
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'business', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $acct = User::factory()->create();
        $acct->assignRole('accountant');

        $business = Business::create([
            'owner_id' => $admin->id,
            'name' => 'Transfer Biz',
            'slug' => 'transfer-biz',
            'registration_number' => 'REG-TRF-001',
            'business_type' => 'company',
            'email' => 'transfer@example.test',
            'phone' => '+234000000012',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'address' => '12 Transfer Rd',
            'industry' => 'services',
            'created_by_accountant_id' => $acct->id,
            'billing_managed_by_platform' => false,
        ]);

        // allow admin manageUsers gate for test
        \Illuminate\Support\Facades\Gate::define('manageUsers', function ($user) {
            return true;
        });

        // enable billing
        $this->actingAs($admin)->post("/admin/accountants/{$acct->id}/enable-billing/{$business->id}");
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'billing_managed_by_platform' => 1,
        ]);

        // transfer ownership
        $this->actingAs($admin)->post("/admin/accountants/{$acct->id}/transfer/{$business->id}", [
            'new_owner_id' => $admin->id,
            'enable_billing' => true,
        ]);

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'owner_id' => $admin->id,
        ]);
    }

    public function test_admin_can_assign_business_to_accountant()
    {
        // ensure roles exist
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'business', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $acct = User::factory()->create();
        $acct->assignRole('accountant');

        $business = Business::create([
            'owner_id' => $admin->id,
            'name' => 'Assignable Biz',
            'slug' => 'assignable-biz',
            'registration_number' => 'REG-ASG-001',
            'business_type' => 'company',
            'email' => 'assign@example.test',
            'phone' => '+234000000013',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'address' => '13 Assign Rd',
            'industry' => 'services',
        ]);

        // allow admin manageUsers gate for test
        \Illuminate\Support\Facades\Gate::define('manageUsers', function ($user) {
            return true;
        });

        $this->actingAs($admin)->post("/admin/accountants/{$acct->id}/assign", [
            'business_id' => $business->id,
        ]);

        $this->assertDatabaseHas('accountant_business', [
            'user_id' => $acct->id,
            'business_id' => $business->id,
        ]);
    }
}
