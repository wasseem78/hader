<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Who is ordering
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->nullOnDelete();

            // What type of order
            $table->enum('type', ['new', 'upgrade', 'downgrade', 'renewal'])->default('new');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');

            // Pricing
            $table->string('currency', 3)->default('USD');
            $table->decimal('amount', 12, 2);

            // Previous plan (for upgrades/downgrades)
            $table->foreignId('previous_plan_id')->nullable()->constrained('plans')->nullOnDelete();

            // Order status workflow
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'expired'])
                ->default('pending');

            // Admin handling
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Customer notes (e.g. "transferred to account X on date Y")
            $table->text('customer_notes')->nullable();

            // Payment reference (bank transfer ref, receipt number etc.)
            $table->string('payment_reference')->nullable();

            // Link to invoice (created when approved)
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_orders');
    }
};
