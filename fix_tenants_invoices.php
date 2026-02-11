<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Tenant Fix (Invoices)...\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "Fixing tenant: {$tenant->subdomain} (DB: {$tenant->db_name})...\n";

    try {
        Config::set('database.connections.tenant_fix', [
            'driver' => 'mysql',
            'host' => $tenant->db_host,
            'port' => $tenant->db_port,
            'database' => $tenant->db_name,
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ]);

        DB::purge('tenant_fix');

        if (!Schema::connection('tenant_fix')->hasTable('invoices')) {
            Schema::connection('tenant_fix')->create('invoices', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('company_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('plan_id')->nullable();
                $table->string('stripe_invoice_id')->nullable();
                $table->string('stripe_payment_intent_id')->nullable();
                $table->string('number')->nullable();
                $table->date('invoice_date')->nullable();
                $table->date('due_date')->nullable();
                $table->date('paid_date')->nullable();
                $table->string('currency', 3)->default('USD');
                $table->decimal('subtotal', 10, 2)->default(0);
                $table->decimal('tax', 10, 2)->default(0);
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('total', 10, 2)->default(0);
                $table->string('status')->default('pending');
                $table->string('payment_method')->nullable();
                $table->string('receipt_url')->nullable();
                $table->string('pdf_url')->nullable();
                $table->date('period_start')->nullable();
                $table->date('period_end')->nullable();
                $table->json('line_items')->nullable();
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
            echo "  -> Created 'invoices' table.\n";
        } else {
            echo "  -> 'invoices' table already exists.\n";
        }

    } catch (\Exception $e) {
        echo "  -> ERROR: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
