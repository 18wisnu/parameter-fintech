<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - Kolom penghubung ke layanan Mixradius (service id & username PPP/hotspot).
 * - Kode pelanggan unik PER TENANT (dua tenant boleh sama-sama punya CUST001).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('mixradius_service_id')->nullable()->after('mixradius_id');
            $table->string('mixradius_username')->nullable()->after('mixradius_service_id');
            $table->timestamp('mixradius_synced_at')->nullable()->after('mixradius_username');

            $table->index(['business_id', 'mixradius_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_code_unique');
            $table->unique(['business_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['business_id', 'code']);
            $table->unique('code');
            $table->dropIndex(['business_id', 'mixradius_id']);
            $table->dropColumn(['mixradius_service_id', 'mixradius_username', 'mixradius_synced_at']);
        });
    }
};
