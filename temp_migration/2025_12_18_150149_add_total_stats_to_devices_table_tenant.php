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
        Schema::table('devices', function (Blueprint $table) {
            if (!Schema::hasColumn('devices', 'total_users')) {
                $table->integer('total_users')->default(0)->after('last_error');
            }
            if (!Schema::hasColumn('devices', 'total_fingerprints')) {
                $table->integer('total_fingerprints')->default(0)->after('total_users');
            }
            if (!Schema::hasColumn('devices', 'total_logs')) {
                $table->integer('total_logs')->default(0)->after('total_fingerprints');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['total_users', 'total_fingerprints', 'total_logs']);
        });
    }
};
