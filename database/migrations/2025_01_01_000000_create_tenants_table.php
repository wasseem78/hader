<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('domain')->nullable()->unique();
            $table->string('subdomain')->unique();
            
            // Billing
            $table->foreignId('plan_id')->nullable();
            $table->string('stripe_customer_id')->nullable();
            
            // Database Connection Details
            $table->string('db_name');
            $table->string('db_host')->default('127.0.0.1');
            $table->string('db_port')->default('3306');
            $table->text('db_username_enc')->nullable(); // Encrypted
            $table->text('db_password_enc')->nullable(); // Encrypted
            
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('status')->default('active'); // active, suspended, provisioning
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
