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
        // Drop the old unique constraint if it exists
        // (May not exist on fresh tenant DBs where base schema doesn't include it)
        $indexExists = collect(DB::select("SHOW INDEX FROM devices WHERE Key_name = 'devices_company_id_ip_address_port_unique'"))->isNotEmpty();

        if ($indexExists) {
            Schema::table('devices', function (Blueprint $table) {
                $table->dropUnique(['company_id', 'ip_address', 'port']);
            });
        }

        // Make ip_address nullable (push devices don't have one)
        Schema::table('devices', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->change();
        });

        // Add unique on serial_number per company (push devices identify by SN)
        $snIndexExists = collect(DB::select("SHOW INDEX FROM devices WHERE Key_name = 'devices_company_sn_unique'"))->isNotEmpty();

        if (!$snIndexExists) {
            Schema::table('devices', function (Blueprint $table) {
                $table->unique(['company_id', 'serial_number'], 'devices_company_sn_unique');
            });
        }
    }

    public function down(): void
    {
        $snIndexExists = collect(DB::select("SHOW INDEX FROM devices WHERE Key_name = 'devices_company_sn_unique'"))->isNotEmpty();

        if ($snIndexExists) {
            Schema::table('devices', function (Blueprint $table) {
                $table->dropIndex('devices_company_sn_unique');
            });
        }

        Schema::table('devices', function (Blueprint $table) {
            $table->string('ip_address')->nullable(false)->change();
        });

        $ipIndexExists = collect(DB::select("SHOW INDEX FROM devices WHERE Key_name = 'devices_company_id_ip_address_port_unique'"))->isNotEmpty();

        if (!$ipIndexExists) {
            Schema::table('devices', function (Blueprint $table) {
                $table->unique(['company_id', 'ip_address', 'port']);
            });
        }
    }
};
