<?php

// =============================================================================
// Migration: Create Invoices Table
// Billing invoices linked to Stripe
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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Relationships
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();

            // Stripe References
            $table->string('stripe_invoice_id')->nullable()->index();
            $table->string('stripe_payment_intent_id')->nullable();

            // Invoice Details
            $table->string('number')->nullable()->comment('Invoice number');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->date('paid_date')->nullable();

            // Amounts
            $table->string('currency')->default('USD');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            // Status
            $table->enum('status', ['draft', 'pending', 'paid', 'failed', 'refunded', 'cancelled'])
                ->default('pending');

            // Payment Details
            $table->string('payment_method')->nullable();
            $table->string('receipt_url')->nullable();
            $table->string('pdf_url')->nullable();

            // Billing Period
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            // Line Items (JSON)
            $table->json('line_items')->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['invoice_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
