<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Buku kas lengkap:
 * - akun default (chart of accounts) dipastikan ada — production sebelumnya kosong
 * - transaksi menyimpan petugas, tagihan/setoran/pengeluaran asal, referensi, metode, bukti & catatan
 * - pengeluaran menyimpan akun biaya, sumber dana, pencatat & bukti
 * - tagihan bisa berstatus 'void' (sudah dibayar di server jaringan, bukan piutang)
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        foreach ([
            ['1001', 'Kas Besar (Tunai)', 'asset', true],
            ['1002', 'Bank BCA', 'asset', true],
            ['1003', 'Bank BRI', 'asset', true],
            ['1101', 'Piutang Pelanggan', 'asset', false],
            ['2001', 'Hutang Dividen Owner', 'liability', false],
            ['3001', 'Modal Disetor', 'equity', false],
            ['3002', 'Dana Cadangan (Tabungan)', 'equity', false],
            ['3003', 'Laba Ditahan', 'equity', false],
            ['4001', 'Pendapatan Internet (PPPoE)', 'revenue', false],
            ['4002', 'Pendapatan Voucher', 'revenue', false],
            ['4003', 'Pendapatan Lain-lain', 'revenue', false],
            ['5001', 'Biaya Bandwidth', 'expense', false],
            ['5002', 'Biaya Listrik & Air', 'expense', false],
            ['5003', 'Gaji Pegawai', 'expense', false],
            ['5004', 'Biaya Operasional', 'expense', false],
            ['5005', 'Perangkat & Material Jaringan', 'expense', false],
            ['5006', 'Transportasi & Konsumsi', 'expense', false],
        ] as [$code, $name, $type, $cash]) {
            DB::table('chart_of_accounts')->insertOrIgnore([
                'code' => $code, 'name' => $name, 'type' => $type, 'is_cash_account' => $cash,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('invoice_id')->nullable()->after('user_id')->index();
            $table->unsignedBigInteger('deposit_id')->nullable()->after('invoice_id')->index();
            $table->unsignedBigInteger('expense_id')->nullable()->after('deposit_id')->index();
            $table->string('reference', 60)->nullable()->after('expense_id');
            $table->string('payment_method', 30)->nullable()->after('reference');
            $table->string('proof_image')->nullable()->after('payment_method');
            $table->text('notes')->nullable()->after('proof_image');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->after('category');
            $table->unsignedBigInteger('source_account_id')->nullable()->after('account_id');
            $table->foreignId('user_id')->nullable()->after('source_account_id')->constrained('users')->nullOnDelete();
            $table->string('proof_image')->nullable()->after('user_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status', 20)->default('unpaid')->change();
            $table->string('void_reason')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('void_reason');
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['account_id', 'source_account_id', 'proof_image']);
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['invoice_id', 'deposit_id', 'expense_id', 'reference', 'payment_method', 'proof_image', 'notes']);
        });
    }
};
