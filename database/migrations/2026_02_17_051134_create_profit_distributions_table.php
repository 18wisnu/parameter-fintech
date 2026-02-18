<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('profit_distributions', function (Blueprint $table) {
            $table->id();
            $table->date('period'); // Periode (Misal: 2026-02-01)
            
            $table->decimal('total_revenue', 15, 2); // Total Pendapatan
            $table->decimal('total_expense', 15, 2); // Total Pengeluaran
            $table->decimal('net_profit', 15, 2);    // Laba Bersih Awal
            
            // Dana Cadangan (10%)
            $table->decimal('reserve_fund_amount', 15, 2);
            
            // Dividen (Sisa Laba yang dibagi)
            $table->decimal('distributable_profit', 15, 2);
            
            // Jatah Owner
            $table->decimal('share_group_a', 15, 2); // Junaidi-Eka (60%)
            $table->decimal('share_group_b', 15, 2); // Bagus (40%)
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('profit_distributions');
    }
};
