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

        // Cari ID Akun Dana Cadangan
        $akunCadangan = ChartOfAccount::where('name', 'like', '%Cadangan%')->first();
        $idCadangan = $akunCadangan ? $akunCadangan->id : 0;

        // 2. PEMASUKAN & PENGELUARAN KAS BIASA (Pisahkan dari Cadangan)
        $masukKasBiasa = Transaction::where('type', 'income')
                                    ->where('account_id', '!=', $idCadangan)
                                    ->sum('amount');
        
        $keluarKasBiasa = Transaction::where('type', 'expense')
                                     ->where('account_id', '!=', $idCadangan)
                                     ->sum('amount');

        $saldoRealAwal = $masukKasBiasa - $keluarKasBiasa;

        // 3. HITUNG DANA CADANGAN
        // A. Potongan 10% murni dari Pemasukan Kas Biasa saja
        $potonganSepuluhPersen = $masukKasBiasa * 0.10;

        // B. Suntikan Dana Manual HANYA untuk Cadangan
        $modalCadanganManual = Transaction::where('account_id', $idCadangan)
                                          ->where('type', 'income')
                                          ->sum('amount');
        
        $totalCadangan = $modalCadanganManual + $potonganSepuluhPersen;

        // 4. KOREKSI KAS BESAR
        // Karena 10% uangnya dipindah ke Cadangan, Saldo Kas Besar harus dikurangi 10% tersebut
        $saldoReal = $saldoRealAwal - $potonganSepuluhPersen;

        return view('dashboard', compact('pendingDeposits', 'totalPending', 'saldoReal', 'totalCadangan'));
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

        Transaction::create([
            'date' => now(),
            'account_id' => $pendapatanAkun->id,
            'source_account_id' => $kasAkun->id,
            'description' => 'Setoran dari ' . $deposit->user->name . ': ' . $deposit->description,
            'amount' => $deposit->amount,
            'type' => 'income',
            'is_locked' => true,
        ]);

        return redirect()->back()->with('success', 'Uang diterima & Jurnal tercatat otomatis!');
    }
}