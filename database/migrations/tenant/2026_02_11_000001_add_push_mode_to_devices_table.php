<?php

// =============================================================================
// Tenant Migration: Add Push Mode Fields to Devices Table
// Adds connection_mode and push configuration fields
// =============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            // Connection mode: pull (server connects to device) or push (device sends to server)
            $table->enum('connection_mode', ['pull', 'push'])->default('pull')
                ->after('protocol')
                ->comment('pull: server fetches from device | push: device sends to server (ICLOCK/ADMS)');

            // Push mode configuration
            $table->string('push_server_url')->nullable()
                ->after('connection_mode')
                ->comment('The URL devices should push to (displayed in setup instructions)');

            $table->integer('push_port')->nullable()
                ->after('push_server_url')
                ->comment('Server port for push mode (displayed in setup instructions)');

            $table->timestamp('last_push_received')->nullable()
                ->after('last_sync')
                ->comment('Last time data was received via push from this device');

            $table->integer('push_records_today')->default(0)
                ->after('total_logs')
                ->comment('Count of push records received today');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'connection_mode',
                'push_server_url',
                'push_port',
                'last_push_received',
                'push_records_today',
            ]);
        });
    }
};
