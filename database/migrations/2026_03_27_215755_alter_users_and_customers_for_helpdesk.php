<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ubah enum role di tabel users menggunakan Raw SQL 
        // (Cara paling aman untuk modifikasi Enum di Laravel)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'admin', 'teknisi', 'kolektor', 'client') DEFAULT 'client'");

        // 2. Tambahkan user_id ke tabel customers agar pelanggan bisa login
        Schema::table('customers', function (Blueprint $table) {
            // Cek dulu biar gak error kalau ternyata kolomnya sudah ada
            if (!Schema::hasColumn('customers', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')
                      ->constrained('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'admin', 'teknisi', 'kolektor') DEFAULT 'teknisi'");
    }
};