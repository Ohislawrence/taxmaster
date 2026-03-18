<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Business;
use App\Models\TaxReturn;
use App\Models\TaxType;
use App\Services\ComplianceService;

class ComplianceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cached_compliance_status_updates_after_tax_return_created()
    {
        $user = User::factory()->create();

        $business = Business::create([
            'owner_id' => $user->id,
            'name' => 'Acme Ltd',
            'slug' => 'acme-ltd',
            'registration_number' => 'RC123456',
            'description' => 'Test business',
            'business_type' => 'company',
            'email' => 'info@acme.test',
            'phone' => '08000000000',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'address' => '1 Test Street',
            'industry' => 'Software',
        ]);

        $service = app(ComplianceService::class);

        // Ensure cache is clear
        $service->clearComplianceStatusCache($business);

        $initial = $service->getComplianceStatusCached($business, 600);
        $this->assertIsArray($initial);
        $this->assertArrayHasKey('status_counts', $initial);

        // Create a tax type and an overdue tax return for the business
        $taxType = TaxType::create([
            'code' => 'VAT',
            'name' => 'Value Added Tax',
            'calculation_method' => 'percentage',
            'frequency' => 'monthly',
            'flat_rate' => null,
            'due_day' => 21,
            'is_active' => true,
        ]);

        $dueDate = now()->subDays(10)->toDateString();

        TaxReturn::create([
            'business_id' => $business->id,
            'tax_type_id' => $taxType->id,
            'return_type' => 'monthly',
            'tax_period' => now()->subMonth()->format('Y-m'),
            'due_date' => $dueDate,
            'gross_income' => 100000,
            'deductions' => 0,
            'taxable_income' => 100000,
            'total_tax_due' => 10000,
            'total_tax_paid' => 0,
            'balance' => 10000,
            'status' => 'submitted',
        ]);

        // Because observers clear cache on create, the cached value should reflect the new overdue return
        $after = $service->getComplianceStatusCached($business, 600);

        $this->assertGreaterThanOrEqual(0, $initial['status_counts']['overdue']);
        $this->assertArrayHasKey('status_counts', $after);
        $this->assertTrue(($after['status_counts']['overdue'] ?? 0) >= 1, 'After creating an overdue return, overdue count should be at least 1');
    }
}
