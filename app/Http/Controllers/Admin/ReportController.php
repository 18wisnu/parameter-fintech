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
        $businessName = $business ? $business->name : 'NetBill';

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

            // Akun lebih dulu: 4001 = langganan (PPPoE / hotspot langganan / DHCP), 4002 = voucher.
            // Kata kunci keterangan hanya untuk transaksi lama di akun lain.
            if ($code == '4002') {
                $voucherIncome += $t->amount;
            } elseif ($code == '4001') {
                $pppoeIncome += $t->amount;
            } elseif (str_contains($desc, 'voucher')) {
                $voucherIncome += $t->amount;
            } elseif (str_contains($desc, 'pppoe') || str_contains($desc, 'tagihan')) {
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

        $safeName = \Illuminate\Support\Str::slug($calc['businessName']) ?: 'netbill';
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

    /**
     * Buku kas: semua pemasukan & pengeluaran dengan detail lengkap,
     * saldo awal periode, saldo berjalan per baris dan saldo akhir.
     */
    public function history(Request $request)
    {
        $filtered = $this->ledgerQuery($request);

        // Saldo awal = semua transaksi sebelum tanggal mulai
        $opening = 0.0;
        if ($request->filled('start_date')) {
            $opening = (float) $this->signedSum(Transaction::query()->where('date', '<', $request->start_date)
                ->when($request->filled('account_id'), fn ($q) => $q->where('account_id', $request->account_id)));
        }

        $totals = (clone $filtered)->reorder()
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense")
            ->selectRaw('COUNT(*) as n')
            ->first();
        $closing = $opening + (float) $totals->income - (float) $totals->expense;

        $transactions = (clone $filtered)->with(['account', 'sourceAccount', 'customer', 'user', 'invoice'])
            ->paginate(25)->withQueryString();

        // Saldo setelah baris teratas halaman ini = saldo akhir − semua baris yang lebih baru (halaman sebelumnya)
        $newer = 0.0;
        $offset = ($transactions->currentPage() - 1) * $transactions->perPage();
        if ($offset > 0) {
            $newer = (float) (clone $filtered)->limit($offset)->get(['type', 'amount'])
                ->sum(fn ($t) => $t->type === 'income' ? $t->amount : ($t->type === 'expense' ? -$t->amount : 0));
        }
        $running = $closing - $newer;
        $balances = [];
        foreach ($transactions as $trx) {
            $balances[$trx->id] = $running;
            $running -= $trx->signedAmount();
        }

        return view('admin.reports.history', [
            'transactions' => $transactions,
            'balances' => $balances,
            'opening' => $opening,
            'closing' => $closing,
            'totals' => $totals,
            'accounts' => \App\Models\ChartOfAccount::whereIn('type', ['revenue', 'expense', 'equity'])->orderBy('code')->get(),
            'staff' => \App\Models\User::membersOfCurrentBusiness()->orderBy('name')->get(),
            'methods' => Transaction::PAYMENT_METHODS,
        ]);
    }

    public function exportHistoryPdf(Request $request)
    {
        $transactions = $this->ledgerQuery($request)->with(['account', 'customer', 'user'])->get();

        $pdf = Pdf::loadView('admin.reports.pdf_history', compact('transactions'));
        return $pdf->download('buku-kas.pdf');
    }

    public function exportHistoryExcel(Request $request)
    {
        $transactions = $this->ledgerQuery($request)->with(['account', 'sourceAccount', 'customer', 'user'])->get();

        $fileName = 'buku-kas-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new \App\Exports\TransactionsExport($transactions), $fileName);
    }

    /** Filter buku kas: tanggal, arus, akun, petugas, metode, kata kunci. Urut terbaru dulu. */
    private function ledgerQuery(Request $request)
    {
        return Transaction::query()
            ->when($request->filled('start_date'), fn ($q) => $q->where('date', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->where('date', '<=', $request->end_date))
            ->when(in_array($request->type, ['income', 'expense'], true), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('account_id'), fn ($q) => $q->where('account_id', $request->account_id))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('payment_method'), fn ($q) => $q->where('payment_method', $request->payment_method))
            ->when(trim((string) $request->search) !== '', function ($q) use ($request) {
                $s = trim((string) $request->search);
                $q->where(fn ($w) => $w->where('description', 'like', "%{$s}%")
                    ->orWhere('reference', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%"));
            })
            ->orderByDesc('date')->orderByDesc('id');
    }

    private function signedSum($query): float
    {
        return (float) $query->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount WHEN type = 'expense' THEN -amount ELSE 0 END), 0) as s")->value('s');
    }

    // ----------------------------------------------------------------------
    // 4. FITUR ANALITIK VOUCHER (TREND & RESELLER TERBANYAK)
    // ----------------------------------------------------------------------

    /**
     * Analisa voucher: reseller/outlet mana yang paling banyak menghasilkan.
     * Sumber utama: statistik tiap reseller di server jaringan (pendapatan, transaksi,
     * voucher dibuat/dipakai, komisi, stok). Pelengkap: setoran voucher di buku kas.
     */
    public function vouchers(Request $request)
    {
        $business = \App\Models\Business::findOrFail(auth()->user()->current_business_id);

        $metrics = [
            'income_month' => 'Pendapatan bulan ini',
            'trx_count_month' => 'Transaksi bulan ini',
            'voucher_gen_month' => 'Voucher dibuat bulan ini',
            'commission_month' => 'Komisi bulan ini',
            'income_today' => 'Pendapatan hari ini',
            'voucher_stock' => 'Stok voucher',
        ];
        $sort = array_key_exists($request->sort, $metrics) ? $request->sort : 'income_month';

        $resellers = collect();
        $networkError = null;
        $limited = false;

        if ($business->hasMixradius()) {
            try {
                [$rows, $limited] = \Illuminate\Support\Facades\Cache::remember(
                    "network:{$business->id}:reseller-stats", 600,
                    fn () => $this->resellerStats($business)
                );
                $resellers = collect($rows);
            } catch (\App\Services\Mixradius\MixradiusException $e) {
                $networkError = $e->getMessage();
            }
        }

        $resellers = $resellers->sortByDesc(fn ($r) => (float) ($r['stats'][$sort] ?? 0))->values();
        $totals = collect(array_keys($metrics))->mapWithKeys(fn ($k) => [$k => $resellers->sum(fn ($r) => (float) ($r['stats'][$k] ?? 0))]);

        // Buku kas: setoran voucher (akun 4002) 6 bulan terakhir, per bulan & per reseller
        $voucherAccount = ChartOfAccount::byCode(ChartOfAccount::PENDAPATAN_VOUCHER);
        $since = now()->subMonths(5)->startOfMonth();
        $ledger = Transaction::with('customer')
            ->where('account_id', $voucherAccount->id)->where('type', 'income')
            ->where('date', '>=', $since->toDateString())
            ->get();

        $trend = collect(range(0, 5))->map(function ($i) use ($since, $ledger) {
            $m = $since->copy()->addMonths($i);
            $rows = $ledger->filter(fn ($t) => Carbon::parse($t->date)->format('Y-m') === $m->format('Y-m'));

            return ['label' => $m->locale('id')->translatedFormat('M Y'), 'total' => (float) $rows->sum('amount'), 'count' => $rows->count()];
        });

        $ledgerResellers = $ledger->groupBy('customer_id')->map(fn ($rows) => [
            'name' => $rows->first()->customer->name ?? 'Tanpa pelanggan',
            'total' => (float) $rows->sum('amount'),
            'count' => $rows->count(),
        ])->sortByDesc('total')->values()->take(10);

        return view('admin.reports.vouchers', compact(
            'resellers', 'metrics', 'sort', 'totals', 'networkError', 'limited', 'trend', 'ledgerResellers'
        ) + ['network' => $business->hasMixradius(), 'isOwner' => auth()->user()->roleIn($business->id) === 'owner']);
    }

    /**
     * Statistik reseller & outlet dari server. Dibatasi 40 mitra per pengambilan
     * (batas 60 permintaan/menit); hasil di-cache 10 menit.
     *
     * @return array{0: array, 1: bool}
     */
    private function resellerStats(\App\Models\Business $business): array
    {
        $client = \App\Services\Mixradius\MixradiusClient::forBusiness($business);
        $partners = [];
        foreach ($client->paginate('/v1/entities') as $e) {
            if (in_array($e['type'] ?? null, ['reseller', 'outlet'], true)) {
                $partners[] = $e;
            }
        }

        $limited = count($partners) > 40;
        $rows = [];
        foreach (array_slice($partners, 0, 40) as $e) {
            $detail = $client->get('/v1/entities/' . (int) $e['id']);
            $rows[] = [
                'id' => (int) $e['id'],
                'name' => $detail['name'] ?? $e['name'] ?? '-',
                'code' => $detail['code'] ?? $e['code'] ?? null,
                'type' => $detail['type'] ?? $e['type'] ?? null,
                'status' => $detail['status'] ?? $e['status'] ?? null,
                'balance' => (float) ($detail['balance'] ?? $e['balance'] ?? 0),
                'stats' => $detail['stats'] ?? [],
            ];
        }

        return [$rows, $limited];
    }
}
