<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Reseller/outlet dari server jaringan disimpan sebagai pelanggan bertipe reseller. */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('mixradius_entity_id')->nullable()->after('mixradius_username');
            $table->unique(['business_id', 'mixradius_entity_id']);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['business_id', 'mixradius_entity_id']);
            $table->dropColumn('mixradius_entity_id');
        });
    }
};
