<?php

// =============================================================================
// Migration: Create Push Device Registry Table
// Central DB table for fast serial-number → tenant mapping
// Used by ZKTeco push mode to quickly route incoming data
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
        Schema::connection('mysql')->create('push_device_registry', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique()->comment('Device serial number (SN)');
            $table->unsignedBigInteger('tenant_id')->comment('FK to tenants.id');
            $table->unsignedBigInteger('device_id')->comment('Device ID in tenant DB');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['serial_number', 'is_active']);
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('push_device_registry');
    }
};
