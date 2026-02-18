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
        // Filter Status (Unpaid/Paid)
        $query = Invoice::with('customer')->latest();
        
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(20);
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
        $invoice = Invoice::findOrFail($id);
        
        // Update status invoice jadi PAID
        $invoice->update(['status' => 'paid']);
        
        // LOGIKA AUTO AKTIF:
        // Jika pelanggan statusnya ISOLIR, kita kembalikan jadi AKTIF (0)
        if ($invoice->customer->is_isolated == 1) {
            $invoice->customer->update(['is_isolated' => 0]);
            $pesan = 'Tagihan lunas! Status Pelanggan kembali AKTIF.';
        } else {
            $pesan = 'Tagihan berhasil ditandai Lunas.';
        }

        // Opsional: Perbarui due_date ke bulan depan otomatis?
        // $newDueDate = Carbon::parse($invoice->customer->due_date)->addMonth();
        // $invoice->customer->update(['due_date' => $newDueDate]);

        return redirect()->back()->with('success', $pesan);
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