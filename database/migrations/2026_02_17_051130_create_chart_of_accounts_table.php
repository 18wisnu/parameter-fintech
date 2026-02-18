<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Contoh: 1001, 4001, 5001
            $table->string('name'); // Contoh: Kas Besar, Biaya Bandwidth, Dana Cadangan
            
            // Tipe Akun Standar Akuntansi
            // asset = Harta (Kas, Bank, Alat)
            // liability = Utang
            // equity = Modal (termasuk Laba Ditahan)
            // revenue = Pendapatan
            // expense = Pengeluaran
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            
            // Penanda khusus: Apakah ini akun Kas/Bank? (Bisa buat bayar)
            $table->boolean('is_cash_account')->default(false); 
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
