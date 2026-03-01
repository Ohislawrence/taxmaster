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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('registration_number')->unique();
            $table->text('description')->nullable();
            $table->string('business_type'); // sole_proprietor, partnership, company, etc
            $table->string('email');
            $table->string('phone');
            $table->string('country')->default('NG');
            $table->string('state');
            $table->string('city');
            $table->string('address');
            $table->string('tax_identification_number')->nullable()->unique();
            $table->decimal('annual_revenue', 15, 2)->nullable();
            $table->string('industry');
            $table->string('logo_path')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->boolean('email_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->json('settings')->nullable(); // Store business-specific settings
            $table->timestamps();
            $table->softDeletes();

            $table->index('owner_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
