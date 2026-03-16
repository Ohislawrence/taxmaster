<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('affiliate_payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referral_id');
            $table->unsignedBigInteger('business_subscription_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->boolean('paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('referral_id')->references('id')->on('affiliate_referrals')->onDelete('cascade');
            $table->foreign('business_subscription_id')->references('id')->on('business_subscriptions')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('affiliate_payouts');
    }
};
