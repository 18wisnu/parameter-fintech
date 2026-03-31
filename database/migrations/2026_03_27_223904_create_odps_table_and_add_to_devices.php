<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel ODP
        Schema::create('odps', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama ODP (misal: ODP-01-TRT)
            $table->foreignId('site_id')->nullable()->constrained('sites')->onDelete('cascade'); // Sambung ke OLT mana
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Tambahkan kolom odp_id ke tabel devices (Modem)
        Schema::table('devices', function (Blueprint $table) {
            $table->foreignId('odp_id')->nullable()->after('site_id')->constrained('odps')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['odp_id']);
            $table->dropColumn('odp_id');
        });

        Schema::dropIfExists('odps');
    }
};