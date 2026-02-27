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
        // 1. Ambil Setoran dari Kolektor yang masih menunggu konfirmasi
        $pendingDeposits = Deposit::where('status', 'pending')->with('user')->latest()->get();
        $totalPending = $pendingDeposits->sum('amount');

        // 2. Identifikasi Akun Kas Biasa vs Cadangan (Untuk Saldo Real)
        $akunCadangan = ChartOfAccount::where('name', 'like', '%Cadangan%')->first();
        $idCadangan = $akunCadangan ? $akunCadangan->id : 0;

        $totalPemasukan = Transaction::where('type', 'income')
                                    ->where('account_id', '!=', $idCadangan)
                                    ->sum('amount');
        
        $totalPengeluaran = Transaction::where('type', 'expense')
                                     ->where('account_id', '!=', $idCadangan)
                                     ->sum('amount');

        $labaOperasional = $totalPemasukan - $totalPengeluaran;
        
        // Asumsi 10% sudah diamankan untuk cadangan secara sistem
        $jatahSepuluhPersen = $labaOperasional > 0 ? $labaOperasional * 0.10 : 0;
        $saldoReal = $labaOperasional - $jatahSepuluhPersen;

        // ---------------------------------------------------------
        // 3. HITUNG DANA CADANGAN (AMBIL LANGSUNG DARI TABEL LOG)
        // ---------------------------------------------------------
        // Ini akan menjumlahkan semua hasil Simpan Laporan + Suntik Dana Manual
        $totalMasukCadangan = \App\Models\ReserveFundLog::where('type', 'in')->sum('amount');
        $totalKeluarCadangan = \App\Models\ReserveFundLog::where('type', 'out')->sum('amount');
        
        // TOTAL FIX DANA CADANGAN UNTUK DASHBOARD
        $totalCadangan = $totalMasukCadangan - $totalKeluarCadangan;

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