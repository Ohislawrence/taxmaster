<?php

namespace Tests\Feature;

use App\Jobs\SendBroadcastEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_queue_broadcast_to_role()
    {
        Bus::fake();

        // ensure roles exist
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'business']);

        // create admin user and recipient
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $recipient = User::factory()->create(['email_verified_at' => now()]);
        $recipient->assignRole('business');

        $payload = [
            'subject' => 'Test Broadcast',
            'body' => '<p>Hello {first_name}</p>',
            'roles' => ['business'],
            'subscribed' => 'all',
        ];

        $response = $this->actingAs($admin)->post(route('admin.broadcast.send'), $payload);

        $response->assertSessionHas('success');

        // Assert a job was dispatched for the recipient
        Bus::assertDispatched(SendBroadcastEmail::class, function ($job) use ($recipient) {
            return $job->userId === $recipient->id && $job->subject === 'Test Broadcast';
        });
    }
}
