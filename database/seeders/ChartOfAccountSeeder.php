<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountSeeder extends Seeder
{
    public function run()
    {
        // KITA BUAT AKUN-AKUN STANDAR DISINI
        $accounts = [
            // ASET (HARTA) - Kode Kepala 1
            ['code' => '1001', 'name' => 'Kas Besar (Tunai)', 'type' => 'asset', 'is_cash_account' => true],
            ['code' => '1002', 'name' => 'Bank BCA', 'type' => 'asset', 'is_cash_account' => true],
            ['code' => '1003', 'name' => 'Bank BRI', 'type' => 'asset', 'is_cash_account' => true],
            ['code' => '1101', 'name' => 'Piutang Pelanggan', 'type' => 'asset', 'is_cash_account' => false],
            
            // KEWAJIBAN (HUTANG) - Kode Kepala 2
            ['code' => '2001', 'name' => 'Hutang Dividen Owner', 'type' => 'liability', 'is_cash_account' => false],
            
            // MODAL (EQUITY) - Kode Kepala 3
            ['code' => '3001', 'name' => 'Modal Disetor', 'type' => 'equity', 'is_cash_account' => false],
            ['code' => '3002', 'name' => 'Dana Cadangan (Tabungan)', 'type' => 'equity', 'is_cash_account' => false],
            ['code' => '3003', 'name' => 'Laba Ditahan', 'type' => 'equity', 'is_cash_account' => false],

            // PENDAPATAN (REVENUE) - Kode Kepala 4
            ['code' => '4001', 'name' => 'Pendapatan Internet (PPPoE)', 'type' => 'revenue', 'is_cash_account' => false],
            ['code' => '4002', 'name' => 'Pendapatan Voucher', 'type' => 'revenue', 'is_cash_account' => false],
            ['code' => '4003', 'name' => 'Pendapatan Lain-lain', 'type' => 'revenue', 'is_cash_account' => false],

            // BEBAN (EXPENSE) - Kode Kepala 5
            ['code' => '5001', 'name' => 'Biaya Bandwidth', 'type' => 'expense', 'is_cash_account' => false],
            ['code' => '5002', 'name' => 'Biaya Listrik & Air', 'type' => 'expense', 'is_cash_account' => false],
            ['code' => '5003', 'name' => 'Gaji Pegawai', 'type' => 'expense', 'is_cash_account' => false],
            ['code' => '5004', 'name' => 'Biaya Operasional', 'type' => 'expense', 'is_cash_account' => false],
        ];

        DB::table('chart_of_accounts')->insert($accounts);
    }
}