<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Business;
use App\Models\VatReturn;
use App\Models\PayeReturn;
use App\Models\BusinessStaff;
use App\Models\PayeSchedule;
use Illuminate\Support\Facades\DB;

class ExportEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_vat_form002_exports_csv_and_xml()
    {
        $this->withoutMiddleware();

        $user = User::factory()->create();

        // Prepare fake business relation on the user (we'll use business_id = 1 in DB rows)
        $fakeBusiness = new Business();
        $fakeBusiness->forceFill([
            'id' => 1,
            'name' => 'Test Business Ltd',
            'tax_identification_number' => 'TIN-12345',
        ]);
        $user->setRelation('ownedBusiness', $fakeBusiness);

        // Disable FK checks so we can insert returns without populating full business row
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $vat = VatReturn::create([
            'business_id' => 1,
            'period' => date('Y-m'),
            'sales_turnover' => 100000,
            'purchases_turnover' => 20000,
        ]);
        $vat->performCalculations();
        $vat->save();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->actingAs($user);
        // ensure the authenticated user instance has the in-memory ownedBusiness relation
        auth()->setUser($user);
        $user->setRelation('ownedBusiness', $fakeBusiness);

        // CSV
        $csvResp = $this->get('/business/vat/export/form002?format=csv');
        $csvResp->assertStatus(200);
        $this->assertStringContainsString('text/csv', $csvResp->headers->get('Content-Type'));
        $this->assertStringContainsString('Business Name,TIN,Period,Total Sales,Output VAT,Input VAT,Net VAT,Form002Ref', $csvResp->getContent());
        $this->assertStringContainsString('Test Business Ltd', $csvResp->getContent());

        // XML
        $xmlResp = $this->get('/business/vat/export/form002?format=xml');
        $xmlResp->assertStatus(200);
        $this->assertStringContainsString('application/xml', $xmlResp->headers->get('Content-Type'));
        $this->assertStringContainsString('<Form002s', $xmlResp->getContent());
        $this->assertStringContainsString('Test Business Ltd', $xmlResp->getContent());
    }

    public function test_paye_schedules_exports_csv_and_xml()
    {
        $this->withoutMiddleware();

        $user = User::factory()->create();

        // Prepare fake business relation
        $fakeBusiness = new Business();
        $fakeBusiness->forceFill([
            'id' => 1,
            'name' => 'Payroll Co',
            'tax_identification_number' => 'TIN-99999',
        ]);
        $user->setRelation('ownedBusiness', $fakeBusiness);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $staff = BusinessStaff::create([
            'business_id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'monthly_salary' => 50000,
            'email' => 'john@example.com',
            'employment_type' => 'full_time',
            'designation' => 'Developer',
            'date_employed' => now()->toDateString(),
            'tax_identification_number' => 'STF-1',
        ]);

        $paye = PayeReturn::create([
            'business_id' => 1,
            'period' => date('Y-m'),
            'total_gross_pay' => 50000,
            'total_tax_deducted' => 5000,
            'staff_count' => 1,
        ]);

        PayeSchedule::create([
            'paye_return_id' => $paye->id,
            'business_staff_id' => $staff->id,
            'gross_pay' => 50000,
            'allowances' => [],
            'tax_reliefs' => [],
            'taxable_income' => 50000,
            'paye_due' => 5000,
            'cumulative_gross' => 50000,
            'cumulative_tax' => 5000,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->actingAs($user);
        // ensure the authenticated user instance has the in-memory ownedBusiness relation
        auth()->setUser($user);
        $user->setRelation('ownedBusiness', $fakeBusiness);

        // CSV bulk export across returns
        $csv = $this->get('/business/paye/export/schedules?format=csv');
        $csv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $csv->headers->get('Content-Type'));
        $this->assertStringContainsString('ReturnPeriod,Business,Staff ID,Staff Name,TIN,Gross Pay,PAYE Due', $csv->getContent());
        $this->assertStringContainsString('John Doe', $csv->getContent());

        // XML single-return export requires policy; skip in this feature test
    }
}
