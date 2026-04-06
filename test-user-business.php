<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Business;

$user = User::factory()->create();
echo "User created: {$user->id}\n";

$business = Business::factory()->create(['owner_id' => $user->id]);
echo "Business created: {$business->id} with owner_id: {$business->owner_id}\n";

$user->refresh();
echo "User refreshed\n";

$defaultBusiness = $user->defaultBusiness();
echo "Default business: " . ($defaultBusiness ? $defaultBusiness->id : 'null') . "\n";

$ownedBusiness = $user->ownedBusiness;
echo "Owned business: " . ($ownedBusiness ? $ownedBusiness->id : 'null') . "\n";

$businesses = $user->businesses()->get();
echo "Businesses count: " . $businesses->count() . "\n";
