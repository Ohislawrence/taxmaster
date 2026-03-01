<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache before seeding
        app()['cache']->forget('spatie.permission.cache');

        // Create permissions for Admin
        $adminPermissions = [
            'view-admin',
            'manage-businesses',
            'manage-users',
            'manage-subscriptions',
            'view-reports',
            'view-all-activity-logs',
            'manage-system-settings',
        ];

        foreach ($adminPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create permissions for Business
        $businessPermissions = [
            'manage-own-business',
            'manage-tax-returns',
            'make-payments',
            'manage-staff',
            'update-business-settings',
            'view-own-activity-logs',
            'access-ai-analysis',
        ];

        foreach ($businessPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $business = Role::firstOrCreate(['name' => 'business', 'guard_name' => 'web']);

        // Assign all admin permissions to admin role
        $admin->syncPermissions($adminPermissions);

        // Assign all business permissions to business role
        $business->syncPermissions($businessPermissions);

        // Clear cache after seeding
        app()['cache']->forget('spatie.permission.cache');

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
