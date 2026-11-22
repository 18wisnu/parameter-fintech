<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Transaction; 
use App\Models\ChartOfAccount; 
use App\Models\ProfitDistribution;
use App\Services\Finance\DepositProcessor;

class DashboardController extends Controller
{
    public function index()
    {
        // 🔒 SATPAM: Kalau yang masuk Staff/Teknisi, Tendang ke Menu Setoran!
        if (in_array(auth()->user()->role, ['staff', 'teknisi'])) {
            return redirect()->route('mobile.home');
        }
        
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

        // 4. HITUNG PENDAPATAN & PENGELUARAN BULAN INI (TANPA POTONGAN)
        $incomeThisMonth = Transaction::where('type', 'income')
                                    ->whereYear('date', now()->year)
                                    ->whereMonth('date', now()->month)
                                    ->sum('amount');
        
        $expenseThisMonth = Transaction::where('type', 'expense')
                                     ->whereYear('date', now()->year)
                                     ->whereMonth('date', now()->month)
                                     ->sum('amount');

        $depositsToday = Deposit::where('status', 'approved')->whereDate('created_at', now()->toDateString())->sum('amount');
        $recentTransactions = Transaction::with(['account', 'user'])->latest('id')->limit(8)->get();

        return view('dashboard', compact(
            'depositsToday',
            'recentTransactions',
            'pendingDeposits',
            'totalPending', 
            'saldoReal', 
            'totalCadangan', 
            'totalPemasukan', 
            'totalPengeluaran',
            'incomeThisMonth',
            'expenseThisMonth'
        ));
    }

    public function approveDeposit($id)
    {
        [$type, $message] = DepositProcessor::approve(Deposit::findOrFail($id), auth()->user());

        return redirect()->back()->with($type, $message);
    }

    /** Terima semua setoran yang masih menunggu (sisa dari saat konfirmasi masih aktif). */
    public function approveAllDeposits()
    {
        $pending = Deposit::where('status', 'pending')->oldest()->get();
        $warnings = [];
        foreach ($pending as $deposit) {
            [$type, $message] = DepositProcessor::approve($deposit, auth()->user());
            if ($type === 'warning') {
                $warnings[] = $message;
            }
        }

        $msg = $pending->count() . ' setoran diterima dan tercatat di buku kas.';

        return redirect()->back()->with($warnings ? 'warning' : 'success', $msg . ($warnings ? ' ' . implode(' ', $warnings) : ''));
    }
}
