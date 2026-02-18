<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Transaction; 
use App\Models\ChartOfAccount; 
use App\Models\ProfitDistribution;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Setoran Pending
        $pendingDeposits = Deposit::where('status', 'pending')->with('user')->latest()->get();
        $totalPending = $pendingDeposits->sum('amount');

        // 2. Saldo Kas Besar (Real)
        $kasId = ChartOfAccount::where('code', '1001')->value('id');
        $masuk = Transaction::where('source_account_id', $kasId)->where('type', 'income')->sum('amount');
        $keluar = Transaction::where('source_account_id', $kasId)->where('type', 'expense')->sum('amount');
        $saldoReal = $masuk - $keluar;

        // 3. TOTAL DANA CADANGAN (Akumulasi dari Laporan Bagi Hasil)
        // Kita ambil total dari semua laporan yang pernah disimpan
        $totalCadangan = ProfitDistribution::sum('reserve_fund_amount');

        return view('dashboard', compact('pendingDeposits', 'totalPending', 'saldoReal', 'totalCadangan'));
    }

    // Fungsi untuk menyetujui setoran
    public function approveDeposit($id)
    {
        $deposit = Deposit::findOrFail($id);
        
        // 1. Cek apakah sudah diapprove sebelumnya? (Biar gak double jurnal)
        if ($deposit->status == 'approved') {
            return redirect()->back();
        }

        // 2. Ubah Status Setoran jadi Approved
        $deposit->status = 'approved';
        $deposit->approved_by = auth()->id();
        $deposit->approved_at = now();
        $deposit->save();

        // 3. AUTO-JOURNAL: Masukkan ke Buku Kas
        // Kita asumsikan uang masuk ke "Kas Besar (1001)" dan sumbernya "Pendapatan Internet (4001)"
        // Nanti bisa dibuat lebih dinamis, tapi untuk sekarang kita hardcode dulu biar jalan.
        
        $kasAkun = ChartOfAccount::where('code', '1001')->first(); // Kas Besar
        $pendapatanAkun = ChartOfAccount::where('code', '4001')->first(); // Pendapatan Internet

        Transaction::create([
            'date' => now(),
            'account_id' => $pendapatanAkun->id, // Kategori: Pendapatan
            'source_account_id' => $kasAkun->id, // Masuk ke: Kas Besar
            'description' => 'Setoran dari ' . $deposit->user->name . ': ' . $deposit->description,
            'amount' => $deposit->amount,
            'type' => 'income', // Pemasukan
            'is_locked' => true, // Kunci biar gak bisa diedit sembarangan
        ]);

        return redirect()->back()->with('success', 'Uang diterima & Jurnal tercatat otomatis!');
    }
}