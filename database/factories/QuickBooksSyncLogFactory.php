<?php

namespace Database\Factories;

use App\Models\QuickBooksConnection;
use App\Models\QuickBooksSyncLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class QuickBooksSyncLogFactory extends Factory
{
    protected $model = QuickBooksSyncLog::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['completed', 'failed']);
        $totalRecords = $this->faker->numberBetween(100, 500);
        $processedRecords = $status === 'failed' ? $this->faker->numberBetween(10, 50) : $totalRecords;
        $successCount = $status === 'failed' ? $this->faker->numberBetween(0, $processedRecords) : $processedRecords;
        $failureCount = $processedRecords - $successCount;
        $skippedCount = $totalRecords - $processedRecords;

        return [
            'quickbooks_connection_id' => QuickBooksConnection::factory(),
            'sync_type' => $this->faker->randomElement(['manual', 'full', 'incremental']),
            'entity_type' => $this->faker->randomElement(['invoice', 'bill', 'payment', 'transaction']),
            'started_at' => Carbon::now()->subMinutes(10),
            'completed_at' => Carbon::now()->subMinutes(5),
            'status' => $status,
            'total_records' => $totalRecords,
            'processed_records' => $processedRecords,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'skipped_count' => $skippedCount,
            'duration_seconds' => $this->faker->numberBetween(30, 600),
            'error_message' => $status === 'failed' ? $this->faker->sentence() : null,
            'errors' => $status === 'failed' ? [
                ['code' => $this->faker->numberBetween(400, 500), 'message' => $this->faker->sentence()],
            ] : null,
            'sync_from_date' => Carbon::now()->subDays(30)->format('Y-m-d'),
            'sync_to_date' => Carbon::now()->format('Y-m-d'),
            'summary' => [
                'invoices_synced' => $status === 'failed' ? 0 : $this->faker->numberBetween(5, 100),
                'bills_synced' => $status === 'failed' ? 0 : $this->faker->numberBetween(5, 100),
                'customers_synced' => $status === 'failed' ? 0 : $this->faker->numberBetween(10, 200),
                'vendors_synced' => $status === 'failed' ? 0 : $this->faker->numberBetween(10, 200),
                'api_calls' => $this->faker->numberBetween(10, 100),
            ],
        ];
    }

    /**
     * Indicate that the sync was successful
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'total_records' => 100,
            'processed_records' => 100,
            'success_count' => 100,
            'failure_count' => 0,
            'skipped_count' => 0,
            'error_message' => null,
            'errors' => null,
        ]);
    }

    /**
     * Indicate that the sync failed
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'total_records' => 100,
            'processed_records' => 20,
            'success_count' => 0,
            'failure_count' => 20,
            'skipped_count' => 80,
            'error_message' => 'Sync failed: ' . $this->faker->sentence(),
            'errors' => [
                ['code' => 401, 'message' => 'Authentication failed'],
                ['code' => 500, 'message' => 'Internal QuickBooks error'],
            ],
            'summary' => [
                'invoices_synced' => 0,
                'bills_synced' => 0,
                'customers_synced' => 0,
                'vendors_synced' => 0,
            ],
        ]);
    }

    /**
     * Indicate that the sync is still in progress
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
            'started_at' => Carbon::now()->subMinutes(5),
            'completed_at' => null,
            'total_records' => 100,
            'processed_records' => 50,
            'success_count' => 45,
            'failure_count' => 5,
            'skipped_count' => 0,
        ]);
    }

    /**
     * Indicate that the sync was manual
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'sync_type' => 'manual',
        ]);
    }

    /**
     * Indicate that the sync was automatic
     */
    public function automatic(): static
    {
        return $this->state(fn (array $attributes) => [
            'sync_type' => 'full',
        ]);
    }
}
