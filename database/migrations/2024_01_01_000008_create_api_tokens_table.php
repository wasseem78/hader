<?php

// =============================================================================
// Migration: Create API Tokens Table
// Device API tokens for webhook authentication
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
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Relationships
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();

            // Token Data
            $table->string('name');
            $table->string('token', 64)->unique()->comment('Hashed token');
            $table->string('token_prefix', 8)->comment('First 8 chars for identification');

            // Permissions & Scopes
            $table->json('abilities')->nullable()->comment('Token abilities/scopes');

            // Usage Tracking
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip')->nullable();
            $table->integer('usage_count')->default(0);

            // Expiration
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);

            // Revocation
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Indexes
            $table->index(['company_id', 'is_active']);
            $table->index('token_prefix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
