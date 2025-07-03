<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('trial_period')->nullable();
            $table->string('grace_period')->nullable();
            $table->string('status');
            $table->boolean('is_popular')->nullable();
            $table->boolean('is_default')->nullable();
            $table->boolean('is_free')->nullable();
            $table->integer('sort')->unique();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('plan_prices', function (Blueprint $table) {
            $table->id();
            $table->string('plan_id');
            $table->string('amount');
            $table->string('timeline_id');
            $table->string('provider_id')->nullable();
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('plan_country_prices', function (Blueprint $table) {
            $table->id();
            $table->string('country_id');
            $table->string('price_id');
            $table->double('price');
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('plan_id');
            $table->string('plan_price_id');
            $table->string('expires_at')->nullable();
            $table->string('starts_at')->nullable();
            $table->string('grace_ends_at')->nullable();
            $table->string('trial_ends_at')->nullable();
            $table->string('cancelled_at')->nullable();
            $table->string('provider');
            $table->string('reference')->nullable();
            $table->boolean('auto_renews');
            $table->json('meta')->nullable();
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('susbcription_histories', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->string('description')->nullable();
            $table->string('subscription_id');
            $table->json('meta');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
        Schema::dropIfExists('plan_prices');
        Schema::dropIfExists('plan_country_prices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('susbcription_histories');
    }
};
