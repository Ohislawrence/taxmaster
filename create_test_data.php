<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Business;
use App\Models\TaxType;
use App\Models\TaxReturn;

echo "Creating test tax return...\n";

// Get or create user
$user = User::first();
if (!$user) {
    echo "No users found. Creating test user...\n";
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@taxmaster.test',
        'password' => bcrypt('password'),
    ]);
}
echo "✅ User: " . $user->email . "\n";

// Get or create business
$business = Business::where('owner_id', $user->id)->first();
if (!$business) {
    echo "Creating business...\n";
    $business = Business::create([
        'owner_id' => $user->id,
        'name' => 'Test Business Ltd',
        'slug' => 'test-business-ltd',
        'registration_number' => 'RC12345',
        'email' => 'business@taxmaster.test',
        'phone' => '+2348012345678',
        'state' => 'LA',
        'city' => 'Lagos',
        'address' => '123 Test Street, Lagos',
        'tax_identification_number' => '12345678-0001',
        'business_type' => 'company',
        'industry' => 'Technology',
        'status' => 'active',
    ]);
}
echo "✅ Business: " . $business->name . "\n";

// Get tax type
$taxType = TaxType::where('code', 'PAYE')->first();
if (!$taxType) {
    $taxType = TaxType::first();
}
if (!$taxType) {
    echo "❌ No tax types found. Run seeder first.\n";
    exit(1);
}
echo "✅ Tax Type: " . $taxType->code . " - " . $taxType->name . "\n";

// Create tax return
$existingReturn = TaxReturn::where('business_id', $business->id)->first();
if ($existingReturn) {
    echo "✅ Using existing tax return ID: " . $existingReturn->id . "\n";
} else {
    echo "Creating tax return...\n";
    $existingReturn = TaxReturn::create([
        'user_id' => $user->id,
        'business_id' => $business->id,
        'tax_type_id' => $taxType->id,
        'tax_period' => '2026-Q1',
        'return_type' => 'quarterly',
        'due_date' => now()->addMonths(1),
        'gross_income' => 5000000,
        'deductions' => 1000000,
        'taxable_income' => 4000000,
        'total_tax_due' => 960000,
        'total_tax_paid' => 960000,
        'penalties' => 0,
        'interest' => 0,
        'total_amount_due' => 960000,
        'balance' => 0,
        'status' => 'submitted',
    ]);
    echo "✅ Created tax return ID: " . $existingReturn->id . "\n";
}

echo "\n✅ Test data ready. Run: php artisan pdf:generate\n";
