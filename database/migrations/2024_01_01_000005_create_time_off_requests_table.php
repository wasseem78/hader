<?php

// =============================================================================
// Migration: Create Time Off Requests Table
// Leave/vacation request management and approval workflow
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
        Schema::create('time_off_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Relationships
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Request Type
            $table->enum('type', [
                'annual_leave',
                'sick_leave',
                'personal_leave',
                'maternity_leave',
                'paternity_leave',
                'bereavement_leave',
                'unpaid_leave',
                'work_from_home',
                'other'
            ])->default('annual_leave');

            // Dates
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_half_day')->default(false);
            $table->enum('half_day_period', ['morning', 'afternoon'])->nullable();
            $table->decimal('total_days', 5, 2)->comment('Calculated total days');

            // Request Details
            $table->text('reason')->nullable();
            $table->string('attachment')->nullable()->comment('Supporting document');

            // Approval Workflow
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // Cancellation
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['user_id', 'start_date', 'end_date']);
            $table->index(['status', 'start_date']);
        });

        // Leave balance tracking
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->year('year');
            $table->string('leave_type');
            $table->decimal('entitled_days', 5, 2)->default(0);
            $table->decimal('used_days', 5, 2)->default(0);
            $table->decimal('pending_days', 5, 2)->default(0);
            $table->decimal('carried_over', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'year', 'leave_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('time_off_requests');
    }
};
