<?php

// =============================================================================
// Migration: Create Devices (ZKTeco) Table
// Stores ZKTeco fingerprint/face recognition device configurations
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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('Public UUID for API usage');

            // Tenant Relationship
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Device Information
            $table->string('name');
            $table->string('serial_number')->nullable();
            $table->string('model')->nullable()->comment('Device model (e.g., ZK-F22, UFace800)');
            $table->string('location')->nullable()->comment('Physical location description');

            // Network Configuration
            $table->string('ip_address');
            $table->integer('port')->default(4370)->comment('Default ZKTeco port');
            $table->enum('protocol', ['tcp', 'udp', 'http'])->default('tcp');

            // Authentication
            $table->string('auth_key')->nullable()->comment('Communication key/password');
            $table->string('comm_password')->nullable()->comment('Device communication password');

            // Device Settings
            $table->string('timezone')->default('UTC');
            $table->boolean('sync_time')->default(true)->comment('Auto-sync time with server');
            $table->integer('sync_interval')->default(5)->comment('Minutes between syncs');

            // Status Tracking
            $table->enum('status', ['online', 'offline', 'error', 'syncing'])->default('offline');
            $table->timestamp('last_seen')->nullable();
            $table->timestamp('last_sync')->nullable();
            $table->text('last_error')->nullable();

            // Statistics
            $table->integer('total_users')->default(0);
            $table->integer('total_fingerprints')->default(0);
            $table->integer('total_logs')->default(0);

            // Capabilities (JSON)
            $table->json('capabilities')->nullable()->comment('Device features: fingerprint, face, card, etc.');

            // Meta
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'is_active']);
            $table->index('status');
            $table->index('ip_address');
            $table->unique(['company_id', 'ip_address', 'port']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
