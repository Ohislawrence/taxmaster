<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds AI workflow tracking to all tax return tables
     */
    public function up(): void
    {
        $tables = ['vat_returns', 'paye_returns', 'wht_returns', 'cit_returns'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    // Only add column if it doesn't exist
                    if (!Schema::hasColumn($table, 'ai_workflow_id')) {
                        $blueprint->unsignedBigInteger('ai_workflow_id')
                            ->nullable()
                            ->after('business_id')
                            ->comment('Reference to AI workflow that created this return');
                    }

                    if (!Schema::hasColumn($table, 'is_ai_generated')) {
                        $blueprint->boolean('is_ai_generated')
                            ->default(false)
                            ->after('ai_workflow_id')
                            ->comment('Flag indicating this return was AI-generated');
                    }
                });

                // Add foreign key and indexes separately (in case column exists but constraints don't)
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    // Check if foreign key exists by trying to get connection info
                    $foreignKeys = DB::select("
                        SELECT CONSTRAINT_NAME
                        FROM information_schema.KEY_COLUMN_USAGE
                        WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = '{$table}'
                        AND COLUMN_NAME = 'ai_workflow_id'
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ");

                    if (empty($foreignKeys)) {
                        $blueprint->foreign('ai_workflow_id')
                            ->references('id')
                            ->on('ai_workflows')
                            ->onDelete('set null');
                    }

                    // Check if index exists
                    $indexes = DB::select("
                        SELECT DISTINCT INDEX_NAME
                        FROM information_schema.STATISTICS
                        WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = '{$table}'
                        AND COLUMN_NAME = 'is_ai_generated'
                    ");

                    if (empty($indexes)) {
                        $blueprint->index('is_ai_generated');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['vat_returns', 'paye_returns', 'wht_returns', 'cit_returns'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    // Drop foreign key if it exists
                    $foreignKeys = DB::select("
                        SELECT CONSTRAINT_NAME
                        FROM information_schema.KEY_COLUMN_USAGE
                        WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = '{$table}'
                        AND COLUMN_NAME = 'ai_workflow_id'
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ");

                    if (!empty($foreignKeys)) {
                        $blueprint->dropForeign(['ai_workflow_id']);
                    }

                    // Drop index if it exists
                    $indexes = DB::select("
                        SELECT DISTINCT INDEX_NAME
                        FROM information_schema.STATISTICS
                        WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = '{$table}'
                        AND COLUMN_NAME = 'is_ai_generated'
                    ");

                    if (!empty($indexes)) {
                        $blueprint->dropIndex(['is_ai_generated']);
                    }

                    // Drop columns if they exist
                    if (Schema::hasColumn($table, 'ai_workflow_id')) {
                        $blueprint->dropColumn('ai_workflow_id');
                    }

                    if (Schema::hasColumn($table, 'is_ai_generated')) {
                        $blueprint->dropColumn('is_ai_generated');
                    }
                });
            }
        }
    }
};
