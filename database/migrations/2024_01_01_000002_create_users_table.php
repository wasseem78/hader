<?php

// =============================================================================
// Migration: Add Tenant Fields to Users Table
// Extends default users table for multi-tenant SaaS
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('Public UUID for API usage');

            // Tenant Relationship
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();

            // Basic Info
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // Profile
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('locale')->default('en');
            $table->string('timezone')->default('UTC');

            // Employee Info (for employee users)
            $table->string('employee_id')->nullable()->comment('Employee ID for device enrollment');
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->date('hire_date')->nullable();

            // Device Enrollment
            $table->integer('device_user_id')->nullable()->comment('User ID on ZKTeco devices');
            $table->string('card_number')->nullable()->comment('RFID card number');
            $table->integer('fingerprint_count')->default(0);
            $table->boolean('face_enrolled')->default(false);

            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();

            // Two-Factor Auth
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'is_active']);
            $table->index('employee_id');
            $table->index('device_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
