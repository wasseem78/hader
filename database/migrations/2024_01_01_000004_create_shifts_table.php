<?php

// =============================================================================
// Migration: Create Shifts Table
// Defines work schedules and shift patterns
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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Tenant Relationship
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Shift Information
            $table->string('name');
            $table->string('code')->nullable()->comment('Short code for display');
            $table->text('description')->nullable();

            // Timing
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('next_day_end')->default(false)->comment('End time is on next day');
            $table->integer('work_hours')->default(8)->comment('Expected work hours');

            // Break Configuration
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->integer('break_duration_minutes')->default(60);
            $table->boolean('break_deducted')->default(true);

            // Grace Period & Flexibility
            $table->integer('grace_period_minutes')->default(15)->comment('Minutes allowed for late arrival');
            $table->integer('early_departure_threshold')->default(15)->comment('Minutes before end considered early');
            $table->integer('overtime_threshold_minutes')->default(30)->comment('Minutes after end to count overtime');

            // Working Days (JSON array of day numbers: 0=Sunday, 6=Saturday)
            $table->json('working_days')->nullable()->comment('[1,2,3,4,5] for Mon-Fri');

            // Status
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            // Color for UI
            $table->string('color')->default('#3B82F6');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'is_active']);
            $table->unique(['company_id', 'code']);
        });

        // Pivot table for user-shift assignments
        Schema::create('shift_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['shift_id', 'user_id', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_user');
        Schema::dropIfExists('shifts');
    }
};
