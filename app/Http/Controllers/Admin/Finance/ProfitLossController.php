<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\PeriodClosing;
use App\Services\Finance\ProfitLoss;
use Carbon\Carbon;
use Illuminate\Http\Request;

/** Laba rugi bulanan + tutup buku (mengunci transaksi bulan itu). */
class ProfitLossController extends Controller
{
    public function index(Request $request)
    {
        $businessId = (int) auth()->user()->current_business_id;
        $month = $this->month($request->input('month')) ?? now()->subMonthNoOverflow()->startOfMonth();

        $report = ProfitLoss::forMonth($businessId, $month);
        $closing = PeriodClosing::with('closer')->whereDate('period', $month->toDateString())->first();
        $closings = PeriodClosing::with('closer')->orderByDesc('period')->limit(24)->get();

        // 6 bulan s/d bulan terpilih untuk tren
        $trend = collect(range(5, 0))->map(function ($i) use ($businessId, $month) {
            $m = $month->copy()->subMonthsNoOverflow($i);
            $r = ProfitLoss::forMonth($businessId, $m);

            return ['month' => $m->format('Y-m'), 'label' => $m->locale('id')->translatedFormat('M Y'),
                'revenue' => $r['total_revenue'], 'expense' => $r['total_expense'], 'net' => $r['net']];
        });

        return view('admin.finance.profit_loss', [
            'report' => $report,
            'previous' => $trend[4],
            'month' => $month,
            'closing' => $closing,
            'closings' => $closings,
            'trend' => $trend,
            'canClose' => $month->lt(now()->startOfMonth()),
            'isOwner' => $this->isOwner(),
        ]);
    }

    public function close(Request $request)
    {
        abort_unless($this->isOwner(), 403, 'Hanya pemilik yang bisa menutup buku.');

        $data = $request->validate(['month' => 'required|date_format:Y-m', 'notes' => 'nullable|string|max:500']);
        $month = Carbon::createFromFormat('Y-m-d', $data['month'] . '-01')->startOfMonth();

        if (!$month->lt(now()->startOfMonth())) {
            return back()->with('error', 'Bulan berjalan belum bisa ditutup. Tutup buku dilakukan setelah bulan berakhir.');
        }
        if (PeriodClosing::whereDate('period', $month->toDateString())->exists()) {
            return back()->with('info', 'Buku bulan ini sudah ditutup.');
        }

        $report = ProfitLoss::forMonth((int) auth()->user()->current_business_id, $month);

        PeriodClosing::create([
            'period' => $month->toDateString(),
            'revenue' => $report['total_revenue'],
            'expense' => $report['total_expense'],
            'net' => $report['net'],
            'cash_balance' => $report['cash_balance'],
            'snapshot' => ['revenue' => $report['revenue'], 'expense' => $report['expense'], 'other' => $report['other'], 'count' => $report['count']],
            'notes' => $data['notes'] ?? null,
            'closed_by' => auth()->id(),
        ]);

        return redirect()->route('finance.pl.index', ['month' => $data['month']])
            ->with('success', 'Buku ' . $month->locale('id')->translatedFormat('F Y') . ' ditutup. Transaksi bulan itu sekarang terkunci.');
    }

    public function reopen(PeriodClosing $closing)
    {
        abort_unless($this->isOwner(), 403, 'Hanya pemilik yang bisa membuka kembali tutup buku.');

        $label = $closing->period->locale('id')->translatedFormat('F Y');
        $month = $closing->period->format('Y-m');
        $closing->delete();

        return redirect()->route('finance.pl.index', ['month' => $month])
            ->with('warning', "Tutup buku {$label} dibuka kembali. Transaksi bulan itu bisa diubah; tutup lagi setelah koreksi selesai.");
    }

    private function isOwner(): bool
    {
        $user = auth()->user();

        return $user->roleIn($user->current_business_id) === 'owner';
    }

    private function month(?string $value): ?Carbon
    {
        if (!$value || !preg_match('/^\d{4}-\d{2}$/', $value)) {
            return null;
        }

        $m = Carbon::createFromFormat('Y-m-d', $value . '-01')->startOfMonth();

        return $m->gt(now()) ? now()->startOfMonth() : $m;
    }
}
