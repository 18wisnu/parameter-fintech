<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Kolom Role: Owner, Admin, Teknisi, Kolektor
            $table->enum('role', ['owner', 'admin', 'teknisi', 'kolektor'])
                ->default('teknisi')
                ->after('email'); // Ditaruh setelah kolom email
                
            // Status: Aktif/Nonaktif (misal pegawai resign)
            $table->boolean('is_active')->default(true)->after('password');
            
            // Persiapan Login Google
            $table->string('google_id')->nullable()->after('id');
            $table->string('avatar')->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active', 'google_id', 'avatar']);
        });
    }
};
