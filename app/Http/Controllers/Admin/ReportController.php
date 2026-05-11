<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReserveFundLog;
use App\Models\Transaction; 
use App\Models\ChartOfAccount;
use App\Models\ProfitDistribution;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    // ----------------------------------------------------------------------
    // 1. FITUR BAGI HASIL (DIVIDEN)
    // ----------------------------------------------------------------------

    public function index(Request $request)
    {
        // 1. Filter Waktu (Default bulan & tahun berjalan)
        $month = $request->month ?? date('n');
        $year  = $request->year ?? date('Y');

        // Identifikasi Akun Dana Cadangan agar tidak tercampur ke operasional
        $akunCadangan = ChartOfAccount::where('name', 'like', '%Cadangan%')->first();
        $idCadangan = $akunCadangan ? $akunCadangan->id : 0;

        // 2. Hitung Pemasukan (Semua Income kecuali transaksi internal cadangan)
        $revenue = Transaction::where('type', 'income')
                                ->where('account_id', '!=', $idCadangan)
                                ->whereYear('date', $year)
                                ->whereMonth('date', $month)
                                ->sum('amount');

        // 3. Hitung Pengeluaran Operasional
        $expense = Transaction::where('type', 'expense')
                                 ->where('account_id', '!=', $idCadangan)
                                 ->whereYear('date', $year)
                                 ->whereMonth('date', $month)
                                 ->sum('amount');

        // 1. Ambil Pengaturan dari Database
        $config = \App\Models\ProfitSharingSetting::where('key', 'reserve_percentage')->first();
        $reservePercentage = $config ? (float)$config->value : 10;
        $stakeholders = \App\Models\ProfitSharingStakeholder::all();

        // 4. Kalkulasi Pembagian Laba
        $netProfit = $revenue - $expense;

        $shares = [];
        if ($netProfit > 0) {
            $reserveFund   = $netProfit * ($reservePercentage / 100);
            $distributable = $netProfit - $reserveFund; // Sisa yang siap dibagi
            
            foreach ($stakeholders as $stakeholder) {
                $shares[] = [
                    'name' => $stakeholder->name,
                    'amount' => $distributable * ($stakeholder->percentage / 100),
                    'percentage' => $stakeholder->percentage
                ];
            }

            // Untuk kompatibilitas dengan form store (yang mungkin masih pakai share_a/b)
            // Kita ambil 2 pertama jika ada
            $shareA = isset($shares[0]) ? $shares[0]['amount'] : 0;
            $shareB = isset($shares[1]) ? $shares[1]['amount'] : 0;
        } else {
            $reserveFund = 0;
            $distributable = 0;
            $shareA = 0;
            $shareB = 0;
        }

        // 5. Ambil History Laporan yang sudah disimpan
        $history = ProfitDistribution::latest('period')->get();

        return view('admin.reports.index', compact(
            'month', 'year', 'revenue', 'expense', 'netProfit',
            'reserveFund', 'distributable', 'shareA', 'shareB', 'history',
            'reservePercentage', 'stakeholders', 'shares'
        ));
    }

    public function store(Request $request)
    {
        $period = Carbon::createFromDate($request->year, $request->month, 1)->format('Y-m-d');
        
        if (ProfitDistribution::where('period', $period)->exists()) {
            return redirect()->back()->with('error', 'Laporan periode ini sudah pernah disimpan!');
        }

        ProfitDistribution::create([
            'period'               => $period,
            'total_revenue'        => $request->revenue,
            'total_expense'        => $request->expense,
            'net_profit'           => $request->net_profit,
            'reserve_fund_amount'  => $request->reserve_fund,
            'distributable_profit' => $request->distributable,
            'share_group_a'        => $request->share_a,
            'share_group_b'        => $request->share_b,
            'shares_details'       => json_decode($request->shares_details, true),
        ]);

        // Otomatis masukkan ke log Dana Cadangan jika ada alokasi
        if ($request->reserve_fund > 0) {
            ReserveFundLog::create([
                'type' => 'in',
                'amount' => $request->reserve_fund,
                'description' => 'Profit Sharing Periode ' . date('F Y', strtotime($period)),
                'transaction_date' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Laporan berhasil disimpan!');
    }

    // ----------------------------------------------------------------------
    // 2. FITUR DANA CADANGAN
    // ----------------------------------------------------------------------

    public function reserve()
    {
        $totalMasuk = ReserveFundLog::where('type', 'in')->sum('amount');
        $totalKeluar = ReserveFundLog::where('type', 'out')->sum('amount');
        $saldoCadangan = $totalMasuk - $totalKeluar;

        $logs = ReserveFundLog::latest('transaction_date')->paginate(10);

        return view('admin.reports.reserve', compact('saldoCadangan', 'logs'));
    }

    public function storeUsage(Request $request)
    {
        if (auth()->user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Akses ditolak! Anda bukan Owner.');
        }

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

    // FITUR BARU: Simpan Suntikan Dana Cadangan (Uang Masuk Manual)
    public function storeInjection(Request $request)
    {
        // 🔒 CEK ROLE OWNER
        if (auth()->user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Akses ditolak! Anda bukan Owner.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required',
            'transaction_date' => 'required|date',
        ]);

        // Simpan ke database
        ReserveFundLog::create([
            'type' => 'in', // 'in' berarti uang masuk
            'amount' => $request->amount,
            'description' => 'Suntikan: ' . $request->description,
            'transaction_date' => $request->transaction_date,
        ]);

        return redirect()->back()->with('success', 'Suntikan Dana Cadangan berhasil dicatat.');
    }

    public function exportReservePdf()
    {
        $logs = ReserveFundLog::orderBy('transaction_date', 'asc')->get();
        $pdf = Pdf::loadView('admin.reports.pdf_reserve', compact('logs'));
        return $pdf->download('laporan-dana-cadangan.pdf');
    }

    // ----------------------------------------------------------------------
    // 3. FITUR RIWAYAT TRANSAKSI (FIX PEMASUKAN/PENGELUARAN)
    // ----------------------------------------------------------------------

    public function history(Request $request)
    {
        $query = Transaction::query()->with(['account', 'sourceAccount']);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $transactions = $query->orderBy('date', 'desc')->paginate(15);

        return view('admin.reports.history', compact('transactions'));
    }

    public function exportHistoryPdf(Request $request)
    {
        $query = Transaction::query();

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $transactions = $query->orderBy('date', 'desc')->get();
        
        $pdf = Pdf::loadView('admin.reports.pdf_history', compact('transactions'));
        return $pdf->download('laporan-riwayat-transaksi.pdf');
    }

    // ----------------------------------------------------------------------
    // 4. FITUR ANALITIK VOUCHER (TREND & RESELLER TERBANYAK)
    // ----------------------------------------------------------------------

    public function vouchers(Request $request)
    {
        // 1. Cari Akun Pendapatan Voucher (Code: 4002)
        $voucherAccount = ChartOfAccount::where('code', '4002')->first();
        
        if (!$voucherAccount) {
            return redirect()->back()->with('error', 'Akun Pendapatan Voucher (4002) tidak ditemukan di Chart of Accounts!');
        }

        $timeframe = $request->timeframe ?? 'monthly';

        // 2. Data Trend Voucher berdasarkan Timeframe
        $query = Transaction::where('account_id', $voucherAccount->id)
            ->where('type', 'income');

        if ($timeframe === 'weekly') {
            $voucherTrend = $query->select(
                DB::raw("DATE_FORMAT(date, '%u-%Y') as label"), 
                DB::raw("SUM(amount) as total_amount"),
                DB::raw("COUNT(*) as transaction_count"),
                DB::raw("MIN(date) as sort_date")
            )
            ->groupBy('label')
            ->orderBy('sort_date', 'desc')
            ->take(12)
            ->get()
            ->reverse();
        } elseif ($timeframe === 'six_monthly') {
            $voucherTrend = $query->select(
                DB::raw("IF(MONTH(date) <= 6, CONCAT('S1-', YEAR(date)), CONCAT('S2-', YEAR(date))) as label"), 
                DB::raw("SUM(amount) as total_amount"),
                DB::raw("COUNT(*) as transaction_count"),
                DB::raw("MIN(date) as sort_date")
            )
            ->groupBy('label')
            ->orderBy('sort_date', 'desc')
            ->take(6)
            ->get()
            ->reverse();
        } else { // monthly
            $voucherTrend = $query->select(
                DB::raw("DATE_FORMAT(date, '%Y-%m') as label"), 
                DB::raw("SUM(amount) as total_amount"),
                DB::raw("COUNT(*) as transaction_count")
            )
            ->groupBy('label')
            ->orderBy('label', 'desc')
            ->take(8)
            ->get()
            ->reverse();
        }

        // 3. Top Reseller Voucher (Berdasarkan Total Nominal)
        $topResellers = Transaction::where('account_id', $voucherAccount->id)
            ->where('type', 'income')
            ->whereNotNull('customer_id')
            ->with('customer')
            ->select(
                'customer_id',
                DB::raw("SUM(amount) as total_spent"),
                DB::raw("COUNT(*) as total_vouchers")
            )
            ->groupBy('customer_id')
            ->orderBy('total_spent', 'desc')
            ->take(10)
            ->get();

        return view('admin.reports.vouchers', compact('voucherTrend', 'topResellers', 'voucherAccount', 'timeframe'));
    }
}