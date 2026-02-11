<?php

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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable(); // For hierarchical departments
            $table->string('name');
            $table->string('code')->nullable(); // Department code (e.g., HR, IT, FIN)
            $table->string('color', 7)->default('#6366f1'); // Hex color for UI
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('location')->nullable(); // Office location/floor
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('company_id');
            $table->index('branch_id');
            $table->index('parent_id');
            $table->index('is_active');
            $table->unique(['company_id', 'code']);
        });

        // Add department_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('branch_id');
            $table->index('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::dropIfExists('departments');
    }
};
