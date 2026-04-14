<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shopify_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopify_connection_id')->constrained()->onDelete('cascade');

            // Sync Details
            $table->string('sync_type'); // full, incremental, manual
            $table->string('entity_type'); // order, product, customer, inventory
            $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued');

            // Progress Tracking
            $table->integer('total_records')->default(0);
            $table->integer('processed_records')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->integer('skipped_count')->default(0);

            // Timing
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();

            // Error Tracking
            $table->text('error_message')->nullable();
            $table->json('errors')->nullable(); // Array of individual errors

            // Sync Range
            $table->date('sync_from_date')->nullable();
            $table->date('sync_to_date')->nullable();

            // Results
            $table->json('summary')->nullable(); // Sync summary details (orders synced, revenue, etc)
            $table->json('metadata')->nullable(); // Additional Shopify-specific data

            $table->timestamps();

            // Indexes
            $table->index(['shopify_connection_id', 'created_at']);
            $table->index(['entity_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify_sync_logs');
    }
};
