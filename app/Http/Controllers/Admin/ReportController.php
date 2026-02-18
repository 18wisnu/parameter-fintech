<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReserveFundLog;
use App\Models\Income;
use App\Models\Expense;
use App\Models\ProfitDistribution;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    // ----------------------------------------------------------------------
    // 1. FITUR BAGI HASIL (DIVIDEN)
    // ----------------------------------------------------------------------

    // Halaman Utama Laporan Bagi Hasil
    public function index(Request $request)
    {
        // 1. Filter Waktu
        $month = $request->month ?? date('n'); // 1-12
        $year  = $request->year ?? date('Y');

        // 2. Hitung Pemasukan & Pengeluaran Real-time
        $revenue = Income::whereYear('transaction_date', $year)
                         ->whereMonth('transaction_date', $month)
                         ->sum('amount');

        $expense = Expense::whereYear('transaction_date', $year)
                          ->whereMonth('transaction_date', $month)
                          ->sum('amount');

        // 3. Kalkulasi Pembagian
        $netProfit = $revenue - $expense;

        if ($netProfit > 0) {
            $reserveFund   = $netProfit * 0.10; // 10% Cadangan
            $distributable = $netProfit - $reserveFund; // Sisa dibagi
            
            $shareA = $distributable * 0.60; // Junaidi & Eka (60%)
            $shareB = $distributable * 0.40; // Bagus (40%)
        } else {
            $reserveFund = 0;
            $distributable = 0;
            $shareA = 0;
            $shareB = 0;
        }

        // 4. Ambil History Laporan (Dari Tabel profit_distributions)
        $history = ProfitDistribution::latest('period')->get();

        return view('admin.reports.index', compact(
            'month', 'year', 'revenue', 'expense', 'netProfit',
            'reserveFund', 'distributable', 'shareA', 'shareB', 'history'
        ));
    }

    // Simpan / Kunci Laporan Bagi Hasil
    public function store(Request $request)
    {
        // Cek Duplikasi Periode
        $period = Carbon::createFromDate($request->year, $request->month, 1)->format('Y-m-d');
        
        if (ProfitDistribution::where('period', $period)->exists()) {
            return redirect()->back()->with('error', 'Laporan periode ini sudah pernah disimpan!');
        }

        // Simpan ke Database
        ProfitDistribution::create([
            'period'               => $period,
            'total_revenue'        => $request->revenue,
            'total_expense'        => $request->expense,
            'net_profit'           => $request->net_profit,
            'reserve_fund_amount'  => $request->reserve_fund,
            'distributable_profit' => $request->distributable,
            'share_group_a'        => $request->share_a,
            'share_group_b'        => $request->share_b,
        ]);

        // Opsional: Catat otomatis ke Log Tabungan Perusahaan jika ada profit
        if ($request->reserve_fund > 0) {
            ReserveFundLog::create([
                'type' => 'in',
                'amount' => $request->reserve_fund,
                'description' => 'Profit Sharing Periode ' . date('F Y', strtotime($period)),
                'transaction_date' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Laporan berhasil disimpan dan dikunci.');
    }


    // ----------------------------------------------------------------------
    // 2. FITUR DANA CADANGAN
    // ----------------------------------------------------------------------

    // Halaman Dana Cadangan
    public function reserve()
    {
        $totalMasuk = ReserveFundLog::where('type', 'in')->sum('amount');
        $totalKeluar = ReserveFundLog::where('type', 'out')->sum('amount');
        $saldoCadangan = $totalMasuk - $totalKeluar;

        $logs = ReserveFundLog::latest('transaction_date')->paginate(10);

        return view('admin.reports.reserve', compact('saldoCadangan', 'logs'));
    }

    // Simpan Penggunaan Dana Cadangan (Uang Keluar dari Tabungan)
    public function storeUsage(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required',
            'transaction_date' => 'required|date',
        ]);

        ReserveFundLog::create([
            'type' => 'out',
            'amount' => $request->amount,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
        ]);

        return redirect()->back()->with('success', 'Penggunaan Dana Cadangan tercatat.');
    }

    // Export PDF Dana Cadangan
    public function exportReservePdf()
    {
        $logs = ReserveFundLog::orderBy('transaction_date', 'asc')->get();
        $pdf = Pdf::loadView('admin.reports.pdf_reserve', compact('logs'));
        return $pdf->download('laporan-dana-cadangan.pdf');
    }


    // ----------------------------------------------------------------------
    // 3. FITUR RIWAYAT TRANSAKSI (INCOME & EXPENSE)
    // ----------------------------------------------------------------------

    // Halaman History Transaksi
    public function history(Request $request)
    {
        $queryIn = Income::select('id', 'amount', 'description', 'transaction_date', DB::raw("'in' as type"));
        $queryOut = Expense::select('id', 'amount', 'description', 'transaction_date', DB::raw("'out' as type"));

        // Filter Tanggal
        if ($request->start_date && $request->end_date) {
            $queryIn->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
            $queryOut->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
        }

        // Gabungkan dan Urutkan
        $transactions = $queryIn->union($queryOut)
                                ->orderBy('transaction_date', 'desc')
                                ->paginate(15);

        return view('admin.reports.history', compact('transactions'));
    }

    // Export PDF History Transaksi
    public function exportHistoryPdf(Request $request)
    {
        $queryIn = Income::select('amount', 'description', 'transaction_date', DB::raw("'Pemasukan' as type"));
        $queryOut = Expense::select('amount', 'description', 'transaction_date', DB::raw("'Pengeluaran' as type"));

        if ($request->start_date && $request->end_date) {
            $queryIn->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
            $queryOut->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
        }

        $transactions = $queryIn->union($queryOut)->orderBy('transaction_date', 'desc')->get();
        
        $pdf = Pdf::loadView('admin.reports.pdf_history', compact('transactions'));
        return $pdf->download('laporan-riwayat-transaksi.pdf');
    }
}