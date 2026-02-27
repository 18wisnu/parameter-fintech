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

        // 2. Cari Akun Cadangan
        $akunCadangan = ChartOfAccount::where('name', 'like', '%Cadangan%')->first();
        $idCadangan = $akunCadangan ? $akunCadangan->id : 0;

        // 3. TOTAL PENDAPATAN (SEMUA WAKTU) - Biar tidak kosong kalau ganti bulan
        // Kita ambil semua 'income' dari kategori PPPOE dan HOTSPOT
        $totalPemasukan = Transaction::where('type', 'income')
                                    ->where('account_id', '!=', $idCadangan)
                                    ->sum('amount');
        
        $totalPengeluaran = Transaction::where('type', 'expense')
                                    ->where('account_id', '!=', $idCadangan)
                                    ->sum('amount');

        // LABA BERSIH (Pemasukan - Pengeluaran Operasional)
        $labaBersih = $totalPemasukan - $totalPengeluaran;

        // 4. HITUNG DANA CADANGAN (Dinamis dari Laba Bersih)
        // Jatah 10% dari Laba Bersih yang pernah didapat
        $jatahCadangan = $labaBersih > 0 ? $labaBersih * 0.10 : 0;

        // Suntikan manual ke akun cadangan (jika ada)
        $masukCadanganManual = Transaction::where('account_id', $idCadangan)
                                        ->where('type', 'income')
                                        ->sum('amount');

        // Pengeluaran yang ditarik dari tabungan cadangan
        $keluarCadangan = Transaction::where('account_id', $idCadangan)
                                    ->where('type', 'expense')
                                    ->sum('amount');

        // ANGKA UNTUK DASHBOARD
        $totalCadangan = ($jatahCadangan + $masukCadanganManual) - $keluarCadangan;
        $saldoReal = $labaBersih - $jatahCadangan;

        return view('dashboard', compact(
            'pendingDeposits', 
            'totalPending', 
            'saldoReal', 
            'totalCadangan',
            'totalPemasukan',
            'totalPengeluaran'
        ));
    }

    public function approveDeposit($id)
    {
        $deposit = Deposit::findOrFail($id);
        
        if ($deposit->status == 'approved') {
            return redirect()->back();
        }

        $deposit->status = 'approved';
        $deposit->approved_by = auth()->id();
        $deposit->approved_at = now();
        $deposit->save();

        // Ambil akun Kas (1001) dan akun Pendapatan (4001)
        $kasAkun = ChartOfAccount::where('code', '1001')->first(); 
        $pendapatanAkun = ChartOfAccount::where('code', '4001')->first(); 

        // Otomatis catat ke Jurnal Transaksi
        Transaction::create([
            'date' => now(),
            'account_id' => $pendapatanAkun->id,
            'source_account_id' => $kasAkun->id,
            'description' => 'Setoran dari ' . $deposit->user->name . ' (' . strtoupper($deposit->type ?? 'UMUM') . ')',
            'amount' => $deposit->amount,
            'type' => 'income',
            'is_locked' => true,
        ]);

        return redirect()->back()->with('success', 'Setoran berhasil disetujui dan saldo diperbarui!');
    }
}