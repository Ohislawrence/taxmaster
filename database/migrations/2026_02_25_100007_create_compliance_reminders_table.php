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
        Schema::create('compliance_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->foreignId('tax_type_id')->constrained('tax_types')->onDelete('cascade');
            $table->foreignId('tax_return_id')->nullable()->constrained('tax_returns')->onDelete('set null');
            $table->enum('reminder_type', ['upcoming', 'due_today', 'overdue', 'custom']);
            $table->date('due_date');
            $table->date('reminder_date'); // When reminder should be sent
            $table->enum('status', ['pending', 'sent', 'dismissed'])->default('pending');
            $table->string('notification_channel'); // email, sms, both
            $table->timestamp('sent_at')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index('reminder_date');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_reminders');
    }
};
