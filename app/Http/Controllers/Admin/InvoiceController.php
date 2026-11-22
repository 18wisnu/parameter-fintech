<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\Customer;
use App\Services\Mixradius\ServiceRenewal;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    // 1. LIHAT DAFTAR TAGIHAN
    public function index(Request $request)
    {
        $invoices = $this->filtered($request)->paginate(20)->withQueryString();

        $summary = $this->filtered($request)->reorder()
            ->selectRaw("SUM(CASE WHEN status = 'unpaid' THEN 1 ELSE 0 END) as unpaid_count")
            ->selectRaw("SUM(CASE WHEN status = 'unpaid' THEN amount ELSE 0 END) as unpaid_total")
            ->selectRaw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_total")
            ->selectRaw("SUM(CASE WHEN status = 'void' THEN 1 ELSE 0 END) as void_count")
            ->first();

        return view('admin.invoices.index', compact('invoices', 'summary') + ['methods' => \App\Models\Transaction::PAYMENT_METHODS]);
    }

    /** Cetak satu invoice. format=a4 (default) atau pos (struk thermal 58/80 mm). */
    public function print(Request $request, $id)
    {
        return $this->printView($request, collect([Invoice::with('customer')->findOrFail($id)]));
    }

    /** Cetak semua invoice sesuai filter yang sedang dipakai (maks. 200). */
    public function printList(Request $request)
    {
        return $this->printView($request, $this->filtered($request)->limit(200)->get());
    }

    private function printView(Request $request, $invoices)
    {
        $business = Business::findOrFail(auth()->user()->current_business_id);
        $format = $request->query('format') === 'pos' ? 'pos' : 'a4';
        $paper = in_array($request->query('paper'), ['58', '80'], true) ? $request->query('paper') : '58';

        // Untuk struk lunas: siapa & kapan dibayar (dari buku kas)
        $payments = \App\Models\Transaction::with('user')
            ->whereIn('invoice_id', $invoices->pluck('id'))
            ->where('type', 'income')
            ->get()->keyBy('invoice_id');

        return view($format === 'pos' ? 'admin.invoices.print_pos' : 'admin.invoices.print',
            compact('invoices', 'business', 'format', 'paper', 'payments'));
    }

    /** Filter status, periode (bulan/tahun) & pencarian nama / no. invoice / kode pelanggan. */
    private function filtered(Request $request)
    {
        $query = Invoice::with('customer')->latest('period_date')->latest('id');

        if (in_array($request->status, ['paid', 'unpaid', 'void'], true)) {
            $query->where('status', $request->status);
        }
        if ($request->month) {
            $query->whereMonth('period_date', (int) $request->month);
        }
        if ($request->year) {
            $query->whereYear('period_date', (int) $request->year);
        }
        if ($search = trim((string) $request->search)) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($sq) => $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('customer_id', 'like', "%{$search}%"));
            });
        }

        return $query;
    }

    // 2. BUAT TAGIHAN BULAN INI (manual — normalnya otomatis tiap tanggal 1)
    public function generate()
    {
        $business = Business::findOrFail(auth()->user()->current_business_id);

        try {
            $s = (new \App\Services\Billing\InvoiceGenerator($business))->generate();
        } catch (\App\Services\Mixradius\MixradiusException $e) {
            return redirect()->back()->with('error', 'Tagihan tidak dibuat karena data masa aktif dari server jaringan tidak bisa diambil: ' . $e->getMessage());
        }

        $msg = "{$s['created']} tagihan baru dibuat untuk " . now()->locale('id')->translatedFormat('F Y') . '.';
        if ($s['covered']) {
            $msg .= " {$s['covered']} pelanggan tidak ditagih karena sudah bayar di server.";
        }
        if ($s['voided']) {
            $msg .= " {$s['voided']} tagihan lama ternyata sudah dibayar di server dan ditandai lunas di server.";
        }

        return redirect()->back()->with('success', $msg);
    }

    // 3. BAYAR TAGIHAN (LUNAS, CATAT BUKU KAS & AKTIFKAN INTERNET)
    public function markAsPaid(Request $request, $id)
    {
        $data = $request->validate([
            'payment_method' => 'nullable|in:' . implode(',', array_keys(\App\Models\Transaction::PAYMENT_METHODS)),
            'notes' => 'nullable|string|max:255',
        ]);

        $invoice = Invoice::with('customer')->findOrFail($id);

        if ($invoice->status !== 'unpaid') {
            return redirect()->back()->with('info', 'Tagihan ini sudah lunas.');
        }

        $invoice = \App\Services\Finance\Ledger::payInvoice(
            $invoice, $data['payment_method'] ?? 'cash', auth()->user(), null, $data['notes'] ?? null
        );

        return redirect()->back()->with(...$this->paidMessage($invoice, 'Tagihan lunas dan tercatat di buku kas.'));
    }

    /** Pesan setelah lunas, termasuk hasil perpanjangan layanan. */
    private function paidMessage(Invoice $invoice, string $base): array
    {
        return match ($invoice->renewal_status) {
            'success' => ['success', $base . ' Layanan internet aktif' . ($invoice->renewal_new_expiry ? ' s/d ' . $invoice->renewal_new_expiry->format('d M Y') : '') . '.'],
            'failed' => ['warning', $base . ' TETAPI layanan internet belum berhasil diperpanjang: ' . $invoice->renewal_error . ' Sistem akan mencoba ulang otomatis.'],
            default => ['success', $base . ' Status pelanggan kembali AKTIF.'],
        };
    }

    // 5. COBA ULANG PERPANJANGAN LAYANAN
    public function retryRenewal($id)
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->status !== 'paid' || $invoice->renewal_status !== 'failed') {
            return redirect()->back()->with('info', 'Tidak ada perpanjangan yang perlu diulang untuk tagihan ini.');
        }

        $invoice = (new ServiceRenewal())->attempt($invoice);

        return redirect()->back()->with(...$this->paidMessage($invoice, "Tagihan {$invoice->invoice_number}:"));
    }

    // 6. TANDAI LAYANAN SUDAH AKTIF (setelah dicek manual)
    public function resolveRenewal($id)
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->renewal_status !== 'failed') {
            return redirect()->back();
        }

        ServiceRenewal::markResolvedManually($invoice);

        return redirect()->back()->with('success', "Layanan untuk tagihan {$invoice->invoice_number} ditandai sudah aktif.");
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