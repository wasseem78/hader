<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates branches table for multi-branch support per company.
     */
    public function up(): void
    {
        // Create branches table
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable(); // e.g., BR001, HQ, etc.
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('manager_name')->nullable();
            $table->time('work_start_time')->nullable(); // Branch-specific work hours
            $table->time('work_end_time')->nullable();
            $table->string('timezone')->nullable();
            $table->boolean('is_headquarters')->default(false); // Main branch flag
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'is_active']);
            $table->index('code');
        });

        // Add branch_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained('branches')->onDelete('set null');
        });

        // Add branch_id to devices table (devices can be assigned to branches)
        Schema::table('devices', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained('branches')->onDelete('set null');
        });

        // Add branch_id to attendance_records for reporting
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained('branches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign keys first
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::dropIfExists('branches');
    }
};
