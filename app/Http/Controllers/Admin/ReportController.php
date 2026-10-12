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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProfitSharingFinancialExport;
use Carbon\Carbon;

class ReportController extends Controller
{
    // ----------------------------------------------------------------------
    // 1. FITUR BAGI HASIL (DIVIDEN)
    // ----------------------------------------------------------------------

    private function calculateProfitSharingData($month, $year)
    {
        $business = auth()->user()->currentBusiness;
        $businessName = $business ? $business->name : 'Parameter Fintech';

        // Identifikasi Akun Dana Cadangan agar tidak tercampur ke operasional
        $akunCadangan = ChartOfAccount::where('name', 'like', '%Cadangan%')->first();
        $idCadangan = $akunCadangan ? $akunCadangan->id : 0;

        // Ambil transaksi Pemasukan
        $incomeTransactions = Transaction::where('type', 'income')
            ->where('account_id', '!=', $idCadangan)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('account')
            ->get();

        // Rincian Pendapatan
        $voucherIncome = 0;
        $pppoeIncome = 0;
        $pasangBaruIncome = 0;
        $otherIncome = 0;

        foreach ($incomeTransactions as $t) {
            $desc = strtolower($t->description ?? '');
            $code = $t->account->code ?? '';

            if ($code == '4002' || str_contains($desc, 'voucher') || str_contains($desc, 'hotspot')) {
                $voucherIncome += $t->amount;
            } elseif ($code == '4001' || str_contains($desc, 'pppoe') || str_contains($desc, 'tagihan')) {
                $pppoeIncome += $t->amount;
            } elseif (in_array($code, ['4003', '4004']) || str_contains($desc, 'pasang') || str_contains($desc, 'instalasi') || str_contains($desc, 'registrasi')) {
                $pasangBaruIncome += $t->amount;
            } else {
                $otherIncome += $t->amount;
            }
        }
        $revenue = $voucherIncome + $pppoeIncome + $pasangBaruIncome + $otherIncome;

        // Ambil transaksi Pengeluaran
        $expenseTransactions = Transaction::where('type', 'expense')
            ->where('account_id', '!=', $idCadangan)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('account')
            ->get();

        // Rincian Pengeluaran
        $tagihanExpense = 0;
        $gajiExpense = 0;
        $otherExpense = 0;

        foreach ($expenseTransactions as $t) {
            $desc = strtolower($t->description ?? '');
            $code = $t->account->code ?? '';

            if ($code == '5003' || str_contains($desc, 'gaji') || str_contains($desc, 'salary')) {
                $gajiExpense += $t->amount;
            } elseif (in_array($code, ['5001', '5002', '5004']) || str_contains($desc, 'bandwidth') || str_contains($desc, 'listrik') || str_contains($desc, 'tagihan') || str_contains($desc, 'operasional')) {
                $tagihanExpense += $t->amount;
            } else {
                $otherExpense += $t->amount;
            }
        }
        $expense = $tagihanExpense + $gajiExpense + $otherExpense;

        // Persentase simpanan dari Bisnis aktif
        $reservePercentage = $business ? (float)$business->reserve_percentage : 10.0;
        $stakeholders = \App\Models\ProfitSharingStakeholder::all();

        // Kalkulasi Pembagian Laba
        $netProfit = $revenue - $expense;

        $shares = [];
        if ($netProfit > 0) {
            $reserveFund   = $netProfit * ($reservePercentage / 100);
            $distributable = $netProfit - $reserveFund;
            
            foreach ($stakeholders as $stakeholder) {
                $shares[] = [
                    'name' => $stakeholder->name,
                    'amount' => $distributable * ($stakeholder->percentage / 100),
                    'percentage' => $stakeholder->percentage
                ];
            }
            $shareA = isset($shares[0]) ? $shares[0]['amount'] : 0;
            $shareB = isset($shares[1]) ? $shares[1]['amount'] : 0;
        } else {
            $reserveFund = 0;
            $distributable = 0;
            $shareA = 0;
            $shareB = 0;
        }

        $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');

        return compact(
            'businessName', 'month', 'year', 'monthName',
            'revenue', 'expense', 'netProfit',
            'voucherIncome', 'pppoeIncome', 'pasangBaruIncome', 'otherIncome',
            'tagihanExpense', 'gajiExpense', 'otherExpense',
            'reservePercentage', 'reserveFund', 'distributable',
            'stakeholders', 'shares', 'shareA', 'shareB'
        );
    }

    public function index(Request $request)
    {
        $month = $request->month ?? date('n');
        $year  = $request->year ?? date('Y');

        $data = $this->calculateProfitSharingData($month, $year);
        $data['history'] = ProfitDistribution::latest('period')->get();

        return view('admin.reports.index', $data);
    }

    public function exportExcel(Request $request)
    {
        $month = $request->month ?? date('n');
        $year  = $request->year ?? date('Y');

        $calc = $this->calculateProfitSharingData($month, $year);
        
        $exportData = [
            'businessName' => $calc['businessName'],
            'periodLabel'  => $calc['monthName'],
            'printedAt'    => now()->timezone('Asia/Jakarta')->format('d M Y H:i'),
            'voucherIncome' => $calc['voucherIncome'],
            'pppoeIncome'   => $calc['pppoeIncome'],
            'pasangBaruIncome' => $calc['pasangBaruIncome'],
            'otherIncome'   => $calc['otherIncome'],
            'totalIncome'   => $calc['revenue'],
            'tagihanExpense' => $calc['tagihanExpense'],
            'gajiExpense'   => $calc['gajiExpense'],
            'otherExpense'  => $calc['otherExpense'],
            'totalExpense'  => $calc['expense'],
            'netProfit'     => $calc['netProfit'],
            'reservePercentage' => $calc['reservePercentage'],
            'reserveFund'   => $calc['reserveFund'],
            'distributable' => $calc['distributable'],
            'shares'        => $calc['shares'],
        ];

        $safeName = \Illuminate\Support\Str::slug($calc['businessName']) ?: 'parameter-fintech';
        $filename = "Laporan-Bagi-Hasil-{$safeName}-{$month}-{$year}.xlsx";

        return Excel::download(new ProfitSharingFinancialExport($exportData), $filename);
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

    public function exportHistoryExcel(Request $request)
    {
        $query = Transaction::query()->with(['account', 'sourceAccount', 'customer', 'user']);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $transactions = $query->orderBy('date', 'desc')->get();
        
        $fileName = 'riwayat-transaksi-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new \App\Exports\TransactionsExport($transactions), $fileName);
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