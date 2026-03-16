<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Ensure Spatie models are available
    $permissionClass = \Spatie\Permission\Models\Permission::class;
    $roleClass = \Spatie\Permission\Models\Role::class;

    $permissionClass::findOrCreate('manage-system-settings');

    $role = $roleClass::where('name', 'admin')->first();
    if ($role) {
        $role->givePermissionTo('manage-system-settings');
        echo "Granted permission to role 'admin'.\n";
    } else {
        echo "Role 'admin' not found.\n";
    }

    $user = \App\Models\User::find(1);
    if ($user) {
        $user->givePermissionTo('manage-system-settings');
        echo "Granted 'manage-system-settings' to user ID 1 (" . $user->email . ").\n";
    } else {
        echo "User ID 1 not found.\n";
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
