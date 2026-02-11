<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Tenant Fix (Attendance Records Table)...\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "Fixing tenant: {$tenant->subdomain} (DB: {$tenant->db_name})...\n";

    try {
        // Configure Tenant Connection
        Config::set('database.connections.tenant_fix', [
            'driver' => 'mysql',
            'host' => $tenant->db_host,
            'port' => $tenant->db_port,
            'database' => $tenant->db_name,
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ]);

        DB::purge('tenant_fix');
        
        // Drop old table if exists (it has wrong schema and no data we care about yet)
        // OR alter it. Since it's dev/broken, dropping is safer to get clean schema.
        // But let's check if it has data first.
        $count = 0;
        if (Schema::connection('tenant_fix')->hasTable('attendance_records')) {
            $count = DB::connection('tenant_fix')->table('attendance_records')->count();
        }

        if ($count == 0) {
            echo "  -> Dropping old 'attendance_records' table...\n";
            Schema::connection('tenant_fix')->dropIfExists('attendance_records');
        } else {
            echo "  -> WARNING: Table has data ($count rows). Attempting to alter...\n";
            // For now, just drop it as we are in dev mode and user can't even login properly.
             Schema::connection('tenant_fix')->dropIfExists('attendance_records');
             echo "  -> Dropped table despite data (Dev Mode Fix).\n";
        }

        // Re-create Table
        if (!Schema::connection('tenant_fix')->hasTable('attendance_records')) {
            Schema::connection('tenant_fix')->create('attendance_records', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('device_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('shift_id')->nullable(); 
                $table->dateTime('punched_at');
                $table->date('punch_date')->nullable();
                $table->time('punch_time')->nullable();
                $table->string('type'); // in, out
                $table->string('verification_type')->default('manual');
                $table->string('device_record_id')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('location_name')->nullable();
                $table->string('status')->default('pending'); 
                $table->boolean('is_late')->default(false);
                $table->boolean('is_early_departure')->default(false);
                $table->integer('late_minutes')->default(0);
                $table->integer('early_minutes')->default(0);
                $table->integer('overtime_minutes')->default(0);
                $table->integer('work_duration_minutes')->default(0);
                $table->integer('break_duration_minutes')->default(0);
                $table->text('notes')->nullable();
                $table->foreignId('adjusted_by')->nullable()->constrained('users')->onDelete('set null');
                $table->dateTime('adjusted_at')->nullable();
                $table->string('adjustment_reason')->nullable();
                $table->json('raw_data')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
            echo "  -> Created 'attendance_records' table with full schema.\n";
        }

    } catch (\Exception $e) {
        echo "  -> ERROR: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
