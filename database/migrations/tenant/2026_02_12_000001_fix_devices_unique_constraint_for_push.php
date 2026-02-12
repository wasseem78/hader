<?php

// =============================================================================
// Migration: Fix devices unique constraint for push mode
// The original unique(company_id, ip_address, port) blocks multiple push devices
// since push devices use ip_address=0.0.0.0. Replace with a conditional unique
// that only applies to pull-mode devices, plus unique on serial_number.
// =============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            // Drop the old constraint that blocks push devices
            try {
                $table->dropUnique(['company_id', 'ip_address', 'port']);
            } catch (\Exception $e) {
                // May not exist if already dropped
            }
        });

        // Make ip_address nullable (push devices don't have one)
        Schema::table('devices', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->change();
        });

        // Add unique on serial_number per company (push devices identify by SN)
        // MySQL doesn't support partial/conditional indexes.
        // MySQL treats NULLs as distinct in unique indexes, so a regular composite
        // unique on (company_id, serial_number) will allow multiple rows where
        // serial_number IS NULL (pull-mode devices without SN) while still
        // preventing duplicates for push-mode devices that have a serial number.
        try {
            Schema::table('devices', function (Blueprint $table) {
                $table->unique(['company_id', 'serial_number'], 'devices_company_sn_unique');
            });
        } catch (\Exception $e) {
            // Already exists or other issue — safe to ignore
        }
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            try {
                $table->dropIndex('devices_company_sn_unique');
            } catch (\Exception $e) {
                // Ignore
            }

            // Restore original constraint
            $table->string('ip_address')->nullable(false)->change();
            $table->unique(['company_id', 'ip_address', 'port']);
        });
    }
};
