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

echo "Starting Tenant Fix (Full Schema)...\n";

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
        
        // 1. Update Users Table
        echo "  -> Checking 'users' table columns...\n";
        $columnsToAdd = [
            'uuid' => 'uuid',
            'phone' => 'string',
            'avatar' => 'string',
            'locale' => 'string',
            'timezone' => 'string',
            'employee_id' => 'string',
            'department' => 'string',
            'position' => 'string',
            'hire_date' => 'date',
            'device_user_id' => 'string',
            'card_number' => 'string',
            'fingerprint_count' => 'integer',
            'face_enrolled' => 'boolean',
            'is_active' => 'boolean',
        ];

        Schema::connection('tenant_fix')->table('users', function (Blueprint $table) use ($columnsToAdd) {
            foreach ($columnsToAdd as $col => $type) {
                if (!Schema::connection('tenant_fix')->hasColumn('users', $col)) {
                    echo "     + Adding '$col'...\n";
                    if ($type == 'uuid') $table->uuid($col)->nullable()->unique(); // Nullable first to avoid error on existing rows
                    elseif ($type == 'string') $table->string($col)->nullable();
                    elseif ($type == 'date') $table->date($col)->nullable();
                    elseif ($type == 'integer') $table->integer($col)->default(0);
                    elseif ($type == 'boolean') $table->boolean($col)->default(false);
                }
            }
        });

        // 2. Create Shifts Table
        if (!Schema::connection('tenant_fix')->hasTable('shifts')) {
            Schema::connection('tenant_fix')->create('shifts', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('code')->nullable();
                $table->text('description')->nullable();
                $table->time('start_time');
                $table->time('end_time');
                $table->boolean('next_day_end')->default(false);
                $table->decimal('work_hours', 4, 2)->default(8.00);
                $table->time('break_start')->nullable();
                $table->time('break_end')->nullable();
                $table->integer('break_duration_minutes')->default(60);
                $table->boolean('break_deducted')->default(true);
                $table->integer('grace_period_minutes')->default(15);
                $table->integer('early_departure_threshold')->default(15);
                $table->integer('overtime_threshold_minutes')->default(60);
                $table->json('working_days')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->string('color')->default('#3b82f6');
                $table->softDeletes();
                $table->timestamps();
            });
            echo "  -> Created 'shifts' table.\n";
        }

        // 3. Create Shift User Pivot
        if (!Schema::connection('tenant_fix')->hasTable('shift_user')) {
            Schema::connection('tenant_fix')->create('shift_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('shift_id')->constrained()->onDelete('cascade');
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
            });
            echo "  -> Created 'shift_user' table.\n";
        }

        // 4. Create Time Off Requests
        if (!Schema::connection('tenant_fix')->hasTable('time_off_requests')) {
            Schema::connection('tenant_fix')->create('time_off_requests', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('type');
                $table->date('start_date');
                $table->date('end_date');
                $table->boolean('is_half_day')->default(false);
                $table->string('half_day_period')->nullable();
                $table->decimal('total_days', 4, 1);
                $table->text('reason')->nullable();
                $table->string('attachment')->nullable();
                $table->string('status')->default('pending');
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->dateTime('approved_at')->nullable();
                $table->text('approval_notes')->nullable();
                $table->dateTime('cancelled_at')->nullable();
                $table->text('cancellation_reason')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
            echo "  -> Created 'time_off_requests' table.\n";
        }

        // 5. Create API Tokens
        if (!Schema::connection('tenant_fix')->hasTable('api_tokens')) {
            Schema::connection('tenant_fix')->create('api_tokens', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('device_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->string('token_prefix', 8)->nullable();
                $table->json('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->string('last_used_ip')->nullable();
                $table->integer('usage_count')->default(0);
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('revoked_at')->nullable();
                $table->foreignId('revoked_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
            echo "  -> Created 'api_tokens' table.\n";
        }

    } catch (\Exception $e) {
        echo "  -> ERROR: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
