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
        Schema::table('invoices', function (Blueprint $table) {
            // FIRS E-Invoicing fields
            $table->string('firs_reference')->nullable()->after('invoice_number')->comment('FIRS reference number');
            $table->string('firs_irn')->nullable()->after('firs_reference')->comment('FIRS Invoice Reference Number');
            $table->string('firs_submission_id')->nullable()->after('firs_irn')->comment('FIRS submission ID');
            $table->string('firs_status')->default('pending')->after('firs_submission_id')->comment('FIRS submission status: pending, submitted, approved, rejected, cancelled');
            $table->timestamp('firs_submitted_at')->nullable()->after('firs_status')->comment('When submitted to FIRS');
            $table->timestamp('firs_approved_at')->nullable()->after('firs_submitted_at')->comment('When approved by FIRS');
            $table->text('firs_validation_errors')->nullable()->after('firs_approved_at')->comment('Validation errors from FIRS (JSON)');
            $table->text('firs_response')->nullable()->after('firs_validation_errors')->comment('Full FIRS response (JSON)');

            // Enhanced invoice fields for FIRS compliance
            $table->string('invoice_type_code')->default('380')->after('invoice_number')->comment('380=Standard, 381=Credit Note, 383=Debit Note');
            $table->string('buyer_email')->nullable()->after('status')->comment('Buyer email address');
            $table->string('buyer_phone')->nullable()->after('buyer_email')->comment('Buyer phone number');
            $table->string('buyer_address')->nullable()->after('buyer_phone')->comment('Buyer address');
            $table->string('buyer_city')->nullable()->after('buyer_address')->comment('Buyer city');
            $table->string('buyer_state')->nullable()->after('buyer_city')->comment('Buyer state');
            $table->string('buyer_postal_code')->nullable()->after('buyer_state')->comment('Buyer postal code');
            $table->string('buyer_country')->default('NG')->after('buyer_postal_code')->comment('Buyer country code');

            // Payment information
            $table->string('payment_means_code')->default('30')->after('buyer_country')->comment('Payment means code (30=Credit Transfer)');
            $table->text('payment_terms')->nullable()->after('payment_means_code')->comment('Payment terms description');
            $table->decimal('vat_rate', 5, 2)->default(7.5)->after('tax')->comment('VAT rate percentage');

            // Digital signature
            $table->text('digital_signature')->nullable()->after('vat_rate')->comment('JAdES digital signature');
            $table->text('qr_code_data')->nullable()->after('digital_signature')->comment('QR code data for verification');

            // Indexes for FIRS queries
            $table->index('firs_reference');
            $table->index('firs_status');
            $table->index('firs_submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['firs_reference']);
            $table->dropIndex(['firs_status']);
            $table->dropIndex(['firs_submitted_at']);

            $table->dropColumn([
                'firs_reference',
                'firs_irn',
                'firs_submission_id',
                'firs_status',
                'firs_submitted_at',
                'firs_approved_at',
                'firs_validation_errors',
                'firs_response',
                'invoice_type_code',
                'buyer_email',
                'buyer_phone',
                'buyer_address',
                'buyer_city',
                'buyer_state',
                'buyer_postal_code',
                'buyer_country',
                'payment_means_code',
                'payment_terms',
                'vat_rate',
                'digital_signature',
                'qr_code_data',
            ]);
        });
    }
};
