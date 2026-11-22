<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaturan koneksi Mixradius Evo per tenant.
 * API key & secret disimpan terenkripsi (cast 'encrypted' di model Business),
 * jadi kolomnya text.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('mixradius_enabled')->default(false);
            $table->string('mixradius_base_url')->nullable();
            $table->text('mixradius_api_key')->nullable();
            $table->text('mixradius_api_secret')->nullable();
            $table->timestamp('mixradius_last_sync_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'mixradius_enabled',
                'mixradius_base_url',
                'mixradius_api_key',
                'mixradius_api_secret',
                'mixradius_last_sync_at',
            ]);
        });
    }
};
