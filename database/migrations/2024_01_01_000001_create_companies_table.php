<?php

// =============================================================================
// Migration: Create Companies (Tenants) Table
// Central table for multi-tenant SaaS architecture
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
        Schema::create('companies', function (Blueprint $table) {
            // Primary key
            $table->id();
            $table->uuid('uuid')->unique()->comment('Public UUID for API usage');

            // Company Information
            $table->string('name');
            $table->string('slug')->unique()->comment('URL-friendly identifier');
            $table->string('domain')->nullable()->unique()->comment('Custom domain if applicable');
            $table->string('subdomain')->nullable()->unique()->comment('Subdomain for tenant');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('locale')->default('en');

            // Database Configuration (for DB-per-tenant)
            $table->string('database')->nullable()->comment('Tenant database name');

            // Billing & Subscription Fields
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('stripe_customer_id')->nullable()->index();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('stripe_subscription_status')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();

            // Plan Limits Tracking
            $table->integer('max_devices')->default(1);
            $table->integer('max_employees')->default(10);

            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();

            // Settings (JSON)
            $table->json('settings')->nullable()->comment('Company-specific settings');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('is_active');
            $table->index(['subdomain', 'is_active']);
            $table->index(['domain', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
