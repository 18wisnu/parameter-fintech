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

        return view('dashboard', compact(
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
        $deposit = Deposit::findOrFail($id);
        
        if ($deposit->status == 'approved') {
            return redirect()->back();
        }

        $deposit->status = 'approved';
        $deposit->approved_by = auth()->id();
        $deposit->approved_at = now();
        $deposit->save();

        $kasAkun = ChartOfAccount::where('code', '1001')->first(); 
        $pendapatanAkun = ChartOfAccount::where('code', '4001')->first(); 

        // JIKA SETORAN TERHUBUNG KE TAGIHAN (INVOICE)
        if ($deposit->invoice_id) {
            $invoice = \App\Models\Invoice::with('customer')->find($deposit->invoice_id);
            
            if ($invoice && $invoice->status == 'unpaid') {
                // 1. Update status tagihan jadi Paid
                $invoice->update(['status' => 'paid']);

                // 2. Jika isolir, aktifkan kembali
                if ($invoice->customer->is_isolated == 1) {
                    $invoice->customer->update(['is_isolated' => 0]);
                }

                // 3. Catat di Jurnal (Cukup Sekali di sini)
                Transaction::create([
                    'date' => now(),
                    'account_id' => $pendapatanAkun->id,
                    'source_account_id' => $kasAkun->id,
                    'customer_id' => $invoice->customer_id,
                    'description' => 'Pembayaran PPPoE via Setoran: ' . $invoice->customer->name . ' (' . $invoice->invoice_number . ')',
                    'amount' => $deposit->amount,
                    'type' => 'income',
                    'is_locked' => true,
                ]);

                return redirect()->back()->with('success', 'Setoran disetujui & Tagihan ' . $invoice->invoice_number . ' otomatis LUNAS!');
            }
        }

        // JIKA SETORAN UMUM (NON-TAGIHAN)
        Transaction::create([
            'date' => now(),
            'account_id' => $pendapatanAkun->id,
            'source_account_id' => $kasAkun->id,
            'description' => 'Setoran dari ' . $deposit->user->name . ' (' . $deposit->description . ')',
            'amount' => $deposit->amount,
            'type' => 'income',
            'is_locked' => true,
        ]);

        return redirect()->back()->with('success', 'Setoran berhasil disetujui dan saldo diperbarui!');
    }
}