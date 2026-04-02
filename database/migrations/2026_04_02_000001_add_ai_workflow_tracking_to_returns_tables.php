<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreignId('ai_workflow_id')
                        ->nullable()
                        ->after('business_id')
                        ->constrained('ai_workflows')
                        ->onDelete('set null')
                        ->comment('Reference to AI workflow that created this return');

                    $blueprint->boolean('is_ai_generated')
                        ->default(false)
                        ->after('ai_workflow_id')
                        ->comment('Flag indicating this return was AI-generated');

                    $blueprint->index('ai_workflow_id');
                    $blueprint->index('is_ai_generated');
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
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropForeign(['ai_workflow_id']);
                    $blueprint->dropIndex(['ai_workflow_id']);
                    $blueprint->dropIndex(['is_ai_generated']);
                    $blueprint->dropColumn(['ai_workflow_id', 'is_ai_generated']);
                });
            }
        }
    }
};
