<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->date('date'); // Tanggal Transaksi
            
            // Relasi: Transaksi ini masuk kategori apa? (Misal: Biaya Listrik)
            $table->foreignId('account_id')->constrained('chart_of_accounts'); 
            
            // Relasi: Uangnya diambil dari mana/masuk ke mana? (Misal: Kas Besar / Dana Cadangan)
            $table->foreignId('source_account_id')->nullable()->constrained('chart_of_accounts');
            
            // Relasi: Transaksi ini terkait pelanggan siapa? (Opsional)
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            
            $table->text('description')->nullable(); // Keterangan
            $table->decimal('amount', 15, 2); // Nominal Uang (Support sampai Triliunan)
            
            // Tipe: Pemasukan (income) atau Pengeluaran (expense)
            $table->enum('type', ['income', 'expense', 'transfer']);
            
            // FITUR KUNCI SALDO AWAL & TUTUP BUKU
            $table->boolean('is_locked')->default(false); 
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
