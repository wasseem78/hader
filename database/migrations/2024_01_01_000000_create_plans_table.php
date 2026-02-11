<?php

// =============================================================================
// Migration: Create Plans (Subscription) Table
// Defines subscription tiers with limits and pricing
// =============================================================================

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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Plan Information
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->string('currency')->default('USD');

            // Stripe Integration
            $table->string('stripe_price_monthly_id')->nullable();
            $table->string('stripe_price_yearly_id')->nullable();
            $table->string('stripe_product_id')->nullable();

            // Plan Limits
            $table->integer('max_devices')->default(1);
            $table->integer('max_employees')->default(10);
            $table->integer('max_users')->default(3)->comment('Admin/manager users');
            $table->integer('retention_days')->default(90)->comment('Data retention period');
            $table->boolean('api_access')->default(false);
            $table->boolean('advanced_reports')->default(false);
            $table->boolean('custom_branding')->default(false);
            $table->boolean('priority_support')->default(false);

            // Features (JSON for flexible feature flags)
            $table->json('features')->nullable();

            // Trial
            $table->integer('trial_days')->default(14);

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
