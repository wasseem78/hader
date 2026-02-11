<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the pivot table for user-shift many-to-many relationship
     */
    public function up(): void
    {
        if (Schema::hasTable('shift_user')) {
            return;
        }
        Schema::create('shift_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            // Unique constraint to prevent duplicate assignments
            $table->unique(['user_id', 'shift_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_user');
    }
};
