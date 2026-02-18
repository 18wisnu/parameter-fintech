<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tambah Kolom di Tabel Customers
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_id')->nullable()->after('id')->index(); // ID Pelanggan (misal: 10055)
            $table->date('due_date')->nullable()->after('address'); // Tanggal Jatuh Tempo
            $table->boolean('is_isolated')->default(false)->after('type'); // Status Isolir
        });

        // 2. Buat Tabel Invoices (Tagihan)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique(); // Format: INV-202602-0001
            $table->decimal('amount', 15, 2);
            $table->date('period_date'); // Tanggal tagihan (misal: 2026-02-01)
            $table->enum('status', ['unpaid', 'paid', 'expired'])->default('unpaid');
            $table->timestamps();
        });

        // 3. Buat Tabel Log Dana Cadangan (Reserve Fund Logs)
        Schema::create('reserve_fund_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['in', 'out']); // 'in' = Dari Bagi Hasil, 'out' = Dipakai
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable(); // Misal: "Alokasi Profit Feb 2026" atau "Beli Server Baru"
            $table->date('transaction_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reserve_fund_logs');
        Schema::dropIfExists('invoices');
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['customer_id', 'due_date', 'is_isolated']);
        });
    }
};