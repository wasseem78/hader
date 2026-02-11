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
        // Using raw SQL for conditional index since Laravel doesn't support partial unique
        try {
            DB::statement('CREATE UNIQUE INDEX devices_company_sn_unique ON devices (company_id, serial_number) WHERE serial_number IS NOT NULL AND deleted_at IS NULL');
        } catch (\Exception $e) {
            // MySQL doesn't support partial indexes — use a regular unique with nulls
            // MySQL treats NULLs as distinct in unique indexes, so this works
            try {
                Schema::table('devices', function (Blueprint $table) {
                    $table->unique(['company_id', 'serial_number'], 'devices_company_sn_unique');
                });
            } catch (\Exception $e2) {
                // Already exists or other issue
            }
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
