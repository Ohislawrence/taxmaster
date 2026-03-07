<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the status enum to include pending_payment and pending
        DB::statement("ALTER TABLE business_subscriptions MODIFY COLUMN status ENUM('active', 'inactive', 'cancelled', 'past_due', 'pending_payment', 'pending') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE business_subscriptions MODIFY COLUMN status ENUM('active', 'inactive', 'cancelled', 'past_due') DEFAULT 'active'");
    }
};
