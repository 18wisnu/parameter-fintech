<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Customer;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    // 1. LIHAT DAFTAR TAGIHAN
    public function index(Request $request)
    {
        // Filter & Search
        $query = Invoice::with('customer')->latest();
        
        // 1. Filter Status (unpaid/paid)
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // 2. Filter Periode (Bulan & Tahun)
        if ($request->month) {
            $query->whereMonth('period_date', $request->month);
        }
        if ($request->year) {
            $query->whereYear('period_date', $request->year);
        }

        // 3. Search (By Name or Invoice Number)
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate(20)->withQueryString(); // Keep filters in pagination links
        return view('admin.invoices.index', compact('invoices'));
    }

    // 2. GENERATE TAGIHAN (AUTO)
    // Fungsi ini akan dipanggil tombol "Buat Tagihan Bulan Ini"
    public function generate()
    {
        $bulanIni = Carbon::now()->format('Y-m-01'); // Tanggal 1 bulan ini
        $pelangganAktif = Customer::where('is_isolated', 0)->get();
        $jumlahTerbuat = 0;

        foreach ($pelangganAktif as $pelanggan) {
            // Cek apakah invoice bulan ini sudah ada?
            $cek = Invoice::where('customer_id', $pelanggan->id)
                          ->where('period_date', $bulanIni)
                          ->exists();

            if (!$cek && $pelanggan->package_fee > 0) {
                // Buat Invoice Baru
                Invoice::create([
                    'customer_id' => $pelanggan->id,
                    'invoice_number' => 'INV-' . date('Ym') . '-' . str_pad($pelanggan->id, 4, '0', STR_PAD_LEFT),
                    'period_date' => $bulanIni,
                    'amount' => $pelanggan->package_fee,
                    'status' => 'unpaid',
                ]);
                $jumlahTerbuat++;
            }
        }

        return redirect()->back()->with('success', "Berhasil membuat $jumlahTerbuat tagihan baru untuk periode ini.");
    }

    // 3. BAYAR TAGIHAN (LUNAS & AUTO AKTIF)
    public function markAsPaid($id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);
        
        if ($invoice->status == 'paid') {
            return redirect()->back()->with('info', 'Tagihan ini sudah lunas.');
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($invoice) {
            // 1. Update status invoice jadi PAID
            $invoice->update(['status' => 'paid']);
            
            // 2. LOGIKA AUTO AKTIF:
            // Jika pelanggan statusnya ISOLIR, kita kembalikan jadi AKTIF (0)
            if ($invoice->customer->is_isolated == 1) {
                $invoice->customer->update(['is_isolated' => 0]);
            }

            // 3. OTOMATIS CATAT KE KEUANGAN (TRANSAKSI)
            // Ambil akun Kas (1001) dan akun Pendapatan PPPoE (4001)
            $kasAkun = \App\Models\ChartOfAccount::where('code', '1001')->first(); 
            $pendapatanAkun = \App\Models\ChartOfAccount::where('code', '4001')->first(); 

            \App\Models\Transaction::create([
                'date' => now(),
                'account_id' => $pendapatanAkun->id,
                'source_account_id' => $kasAkun->id,
                'customer_id' => $invoice->customer_id,
                'description' => 'Pembayaran PPPoE: ' . $invoice->customer->name . ' (' . $invoice->invoice_number . ')',
                'amount' => $invoice->amount,
                'type' => 'income',
                'is_locked' => true,
            ]);
        });

        return redirect()->back()->with('success', 'Tagihan lunas! Status Pelanggan kembali AKTIF dan transaksi tercatat.');
    }

    // 4. CEK ISOLIR OTOMATIS (Berdasarkan Tanggal Jatuh Tempo)
    public function checkIsolir()
    {
        $hariIni = date('Y-m-d');
        
        // Cari semua invoice yang BELUM LUNAS (Unpaid)
        // Dan Tanggal Jatuh Tempo pelanggannya sudah LEWAT hari ini
        $tagihanNunggak = Invoice::where('status', 'unpaid')
            ->whereHas('customer', function($q) use ($hariIni) {
                $q->where('due_date', '<', $hariIni) // Lewat Jatuh Tempo
                  ->where('is_isolated', 0);         // Dan statusnya masih Aktif
            })
            ->get();

        $jumlahIsolir = 0;

        foreach ($tagihanNunggak as $inv) {
            // Isolir Pelanggan
            $inv->customer->update(['is_isolated' => 1]);
            $jumlahIsolir++;
        }

        if ($jumlahIsolir > 0) {
            return redirect()->back()->with('warning', "Sistem mengisolir $jumlahIsolir pelanggan yang menunggak.");
        } else {
            return redirect()->back()->with('info', 'Semua aman. Belum ada yang melewati jatuh tempo.');
        }
    }
}