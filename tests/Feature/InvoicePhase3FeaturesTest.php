<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Invoice;
use App\Models\Business;
use Illuminate\Support\Str;

class InvoicePhase3FeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_qr_code_endpoint_returns_data()
    {
        $business = Business::factory()->create();
        $invoice = Invoice::factory()->create(['business_id' => $business->id]);
        $response = $this->actingAs($business->owner)->get("/business/invoices/{$invoice->id}/qr");
        $response->assertStatus(200);
        $response->assertJsonStructure(['qr']);
        $this->assertTrue(Str::startsWith($response->json('qr'), 'data:image/png;base64,'));
    }

    public function test_admin_invoice_show_page_loads_with_qr_and_nrs_status()
    {
        $business = Business::factory()->create();
        $invoice = Invoice::factory()->create(['business_id' => $business->id]);
        $admin = \App\Models\User::factory()->admin()->create();
        $response = $this->actingAs($admin)->get("/admin/invoices/{$invoice->id}");
        $response->assertStatus(200);
        // The actual Vue rendering is not tested here, but the endpoint is reachable
    }

    public function test_nrs_reporting_command_runs()
    {
        $business = Business::factory()->create();
        $invoice = Invoice::factory()->create(['business_id' => $business->id]);
        $this->artisan('invoices:report-nrs')->assertExitCode(0);
    }
}
