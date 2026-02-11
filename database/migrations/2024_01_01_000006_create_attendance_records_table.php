<?php

// =============================================================================
// Migration: Create Attendance Records Table
// Stores punch-in/punch-out records from ZKTeco devices
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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Relationships
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();

            // Punch Data
            $table->timestamp('punched_at')->comment('Actual punch timestamp');
            $table->date('punch_date')->index()->comment('Date portion for grouping');
            $table->time('punch_time')->comment('Time portion for display');

            // Punch Type
            $table->enum('type', ['in', 'out', 'break_start', 'break_end', 'overtime_start', 'overtime_end'])
                ->default('in');
            $table->enum('verification_type', ['fingerprint', 'face', 'card', 'password', 'manual'])
                ->nullable();

            // Device Record ID (for deduplication)
            $table->string('device_record_id')->nullable()->comment('Original record ID from device');

            // Location (for mobile punches)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();

            // Processing Status
            $table->enum('status', ['pending', 'processed', 'error', 'manual', 'missing_punch_out'])->default('pending');
            $table->boolean('is_late')->default(false);
            $table->boolean('is_early_departure')->default(false);
            $table->integer('late_minutes')->default(0);
            $table->integer('early_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);

            // Calculated Fields (populated by processing job)
            $table->integer('work_duration_minutes')->nullable();
            $table->integer('break_duration_minutes')->nullable();

            // Notes and Adjustments
            $table->text('notes')->nullable();
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('adjusted_at')->nullable();
            $table->text('adjustment_reason')->nullable();

            // Raw device data (for debugging)
            $table->json('raw_data')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['company_id', 'punch_date']);
            $table->index(['user_id', 'punch_date']);
            $table->index(['device_id', 'punched_at']);
            $table->index(['status', 'punch_date']);

            // Prevent duplicate punches (same user, device, time)
            $table->unique(['device_id', 'device_record_id'], 'unique_device_record');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
