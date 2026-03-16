<?php

$projectRoot = dirname(__DIR__);
require $projectRoot . '/vendor/autoload.php';

$app = require $projectRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$result = [
    'has_date_filed' => Schema::hasColumn('tax_returns', 'date_filed'),
    'has_date_paid'  => Schema::hasColumn('tax_returns', 'date_paid'),
];

$first = App\Models\TaxReturn::with('business')->select('id','tax_period','due_date','date_filed','date_paid','status','total_tax_due','total_tax_paid','balance')->first();
$result['first_return'] = $first ? $first->toArray() : null;

echo json_encode($result, JSON_PRETTY_PRINT);
