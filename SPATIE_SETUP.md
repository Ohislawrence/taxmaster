# Spatie Permissions & Roles Setup Guide

## Installation

Spatie permissions should already be set up with Jetstream. If not:

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

---

## Role Structure

### Roles
1. **admin** - System administrator
   - Full access to all businesses
   - View all reports
   - Manage subscriptions
   - View activity logs for any business

2. **business** - Business owner/manager
   - Access only their own business data
   - Manage tax returns
   - Process payments
   - Manage staff
   - Update settings

---

## Permissions

### Admin Permissions
```
- view-all-businesses
- manage-businesses
- manage-users
- view-reports
- manage-subscriptions
- view-activity-logs
```

### Business Permissions
```
- manage-own-business
- manage-tax-returns
- make-payments
- manage-staff
- update-settings
- view-ai-analysis
```

---

## Seeder for Roles & Permissions

Create file: `database/seeders/RoleAndPermissionSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        Permission::firstOrCreate(['name' => 'view-admin']);
        Permission::firstOrCreate(['name' => 'manage-businesses']);
        Permission::firstOrCreate(['name' => 'manage-users']);
        Permission::firstOrCreate(['name' => 'view-reports']);
        Permission::firstOrCreate(['name' => 'manage-subscriptions']);
        
        Permission::firstOrCreate(['name' => 'manage-business']);
        Permission::firstOrCreate(['name' => 'manage-returns']);
        Permission::firstOrCreate(['name' => 'make-payments']);
        Permission::firstOrCreate(['name' => 'manage-staff']);

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $business = Role::firstOrCreate(['name' => 'business']);

        // Assign permissions to admin
        $admin->givePermissionTo([
            'view-admin',
            'manage-businesses',
            'manage-users',
            'view-reports',
            'manage-subscriptions',
        ]);

        // Assign permissions to business
        $business->givePermissionTo([
            'manage-business',
            'manage-returns',
            'make-payments',
            'manage-staff',
        ]);
    }
}
```

Run seeder:
```bash
php artisan db:seed --class=RoleAndPermissionSeeder
```

---

## User Model Modification

The User model should extend `HasRoles` and `HasPermissions` from Spatie:

```php
<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;  // Add this trait
    
    // ... rest of model
}
```

---

## Assign Roles to Users

### During Registration
```php
$user->assignRole('business');  // For regular businesses
$user->assignRole('admin');     // For admins
```

### Via Middleware
The middleware automatically checks roles:
```php
// Protect routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Admin routes
});

Route::middleware(['auth', 'role:business'])->group(function () {
    // Business routes
});
```

---

## Check Roles in Code

```php
// Check single role
if (auth()->user()->hasRole('admin')) {
    // Admin code
}

// Check multiple roles
if (auth()->user()->hasAnyRole(['admin', 'moderator'])) {
    // Code
}

// Check permission
if (auth()->user()->hasPermissionTo('manage-businesses')) {
    // Authorized
}
```

---

## Authorize Actions in Controllers

```php
// Check in controller
public function update(User $user)
{
    $this->authorize('update', $user);
    // Update logic
}

// Using middleware
Route::put('/users/{user}', [UserController::class, 'update'])
    ->middleware('permission:edit-users');
```

---

## Policy-based Authorization

Create a policy for Business:
```bash
php artisan make:policy BusinessPolicy --model=Business
```

Grant access based on role:
```php
public function viewAny(User $user): bool
{
    if ($user->hasRole('admin')) {
        return true;  // Admin sees all
    }
    return false;
}

public function view(User $user, Business $business): bool
{
    if ($user->hasRole('admin')) {
        return true;
    }
    return $user->id === $business->owner_id;  // Owner sees own business
}
```

---

## User Relationship to Business

Add to User model:
```php
public function ownedBusiness()
{
    return $this->hasOne(Business::class, 'owner_id');
}

public function businesses()
{
    return $this->belongsToMany(Business::class);
}
```

---

## Next Steps

1. Run the RoleAndPermissionSeeder
2. Create test users with different roles
3. Test middleware protection
4. Implement proper authorization checks in all controllers
5. Add policy checks where needed

---

## Testing Roles

### Create Test User with Admin Role
```php
$adminUser = User::factory()->create();
$adminUser->assignRole('admin');
```

### Create Test User with Business Role
```php
$businessUser = User::factory()->create();
$businessUser->assignRole('business');

// Optionally create a business for them
$business = Business::create([
    'owner_id' => $businessUser->id,
    'name' => 'Test Business',
    // ... other fields
]);
```

---

## Important Notes

- Roles are stored in `roles` table
- Permissions are stored in `permissions` table  
- User-role relationships in `model_has_roles`
- Cache permissions for performance: `php artisan permission:cache-reset`
- Always verify user has role before accessing admin features
- Policies should handle both role and ownership checks
