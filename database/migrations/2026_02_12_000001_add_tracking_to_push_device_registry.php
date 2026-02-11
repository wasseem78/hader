<?php

// =============================================================================
// Migration: Add tracking columns to push_device_registry
// Adds last_ip and last_seen_at for heartbeat monitoring
// =============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->table('push_device_registry', function (Blueprint $table) {
            $table->string('last_ip')->nullable()->after('is_active');
            $table->timestamp('last_seen_at')->nullable()->after('last_ip');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->table('push_device_registry', function (Blueprint $table) {
            $table->dropColumn(['last_ip', 'last_seen_at']);
        });
    }
};
