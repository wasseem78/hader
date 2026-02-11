<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Companies table (Tenant specific - holds settings)
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('domain')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('trial_ends_at')->nullable();
            $table->integer('max_devices')->default(5);
            $table->integer('max_employees')->default(10);
            $table->integer('max_users')->default(2);
            $table->softDeletes();
            $table->timestamps();
        });

        // Users table (Tenant specific)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('locale')->default('en');
            $table->string('timezone')->default('UTC');
            $table->string('employee_id')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('device_user_id')->nullable();
            $table->string('card_number')->nullable();
            $table->integer('fingerprint_count')->default(0);
            $table->boolean('face_enrolled')->default(false);
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Shifts table
        Schema::create('shifts', function (Blueprint $table) {
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
            $table->json('working_days')->nullable(); // [1,2,3,4,5]
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('color')->default('#3b82f6');
            $table->softDeletes();
            $table->timestamps();
        });

        // Shift User Pivot
        Schema::create('shift_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // Time Off Requests
        Schema::create('time_off_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // annual, sick, etc.
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_half_day')->default(false);
            $table->string('half_day_period')->nullable(); // am, pm
            $table->decimal('total_days', 4, 1);
            $table->text('reason')->nullable();
            $table->string('attachment')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, cancelled
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Devices table (Tenant specific) - Must be before api_tokens due to FK
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('serial_number')->nullable();
            $table->string('model')->nullable();
            $table->string('location')->nullable();
            $table->string('ip_address');
            $table->integer('port')->default(4370);
            $table->string('protocol')->default('udp');
            $table->string('status')->default('offline');
            $table->timestamp('last_seen')->nullable();
            $table->timestamp('last_sync')->nullable();
            $table->text('last_error')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // API Tokens
        Schema::create('api_tokens', function (Blueprint $table) {
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

        // Spatie Permission Tables
        $tableNames = config('permission.table_names', [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ]);

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->foreign('permission_id')->references('id')->on($tableNames['permissions'])->onDelete('cascade');
            $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->foreign('role_id')->references('id')->on($tableNames['roles'])->onDelete('cascade');
            $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->foreign('permission_id')->references('id')->on($tableNames['permissions'])->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on($tableNames['roles'])->onDelete('cascade');
            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });

        // Invoices Table
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->nullable(); // Can't constrain if Plan is central, or use unsignedBigInteger
            $table->string('stripe_invoice_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('receipt_url')->nullable();
            $table->string('pdf_url')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->json('line_items')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Attendance Records (Tenant specific)
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('shift_id')->nullable(); // Assuming shifts table exists or will exist
            $table->dateTime('punched_at');
            $table->date('punch_date')->nullable();
            $table->time('punch_time')->nullable();
            $table->string('type'); // in, out
            $table->string('verification_type')->default('manual');
            $table->string('device_record_id')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();
            $table->string('status')->default('pending'); // pending, processed
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
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('users');
    }
};
