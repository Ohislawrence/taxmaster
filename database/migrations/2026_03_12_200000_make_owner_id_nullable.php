<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `businesses` MODIFY `owner_id` BIGINT UNSIGNED NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE businesses ALTER COLUMN owner_id DROP NOT NULL');
        } else {
            // For sqlite or others, skip — tests/environments using sqlite may need manual migration
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `businesses` MODIFY `owner_id` BIGINT UNSIGNED NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE businesses ALTER COLUMN owner_id SET NOT NULL');
        } else {
            // no-op for sqlite
        }
    }
};
