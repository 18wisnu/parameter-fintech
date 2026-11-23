<?php

namespace App\Services\Finance;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Laba rugi bulanan dari buku kas.
 *  - Pendapatan : transaksi masuk ke akun bertipe revenue
 *  - Beban      : transaksi keluar dari akun bertipe expense
 *  - Di luar laba rugi: modal, dana cadangan, dll. (akun equity/asset) — tetap memengaruhi saldo kas
 */
class ProfitLoss
{
    /**
     * @return array{period: Carbon, revenue: array, expense: array, other: array,
     *               total_revenue: float, total_expense: float, net: float, cash_balance: float, count: int}
     */
    public static function forMonth(int $businessId, Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $rows = DB::table('transactions as t')
            ->join('chart_of_accounts as a', 'a.id', '=', 't.account_id')
            ->where('t.business_id', $businessId)
            ->whereBetween('t.date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('t.type', ['income', 'expense'])
            ->groupBy('a.id', 'a.code', 'a.name', 'a.type', 't.type')
            ->orderBy('a.code')
            ->selectRaw('a.id, a.code, a.name, a.type as account_type, t.type as flow, SUM(t.amount) as total, COUNT(*) as n')
            ->get();

        $map = fn ($r) => ['id' => $r->id, 'code' => $r->code, 'name' => $r->name, 'total' => (float) $r->total, 'count' => (int) $r->n, 'flow' => $r->flow];

        $revenue = $rows->filter(fn ($r) => $r->account_type === 'revenue' && $r->flow === 'income')->map($map)->values()->all();
        $expense = $rows->filter(fn ($r) => $r->account_type === 'expense' && $r->flow === 'expense')->map($map)->values()->all();
        $other = $rows->reject(fn ($r) => ($r->account_type === 'revenue' && $r->flow === 'income') || ($r->account_type === 'expense' && $r->flow === 'expense'))
            ->map($map)->values()->all();

        $totalRevenue = array_sum(array_column($revenue, 'total'));
        $totalExpense = array_sum(array_column($expense, 'total'));

        $cash = (float) DB::table('transactions')
            ->where('business_id', $businessId)
            ->where('date', '<=', $end->toDateString())
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount WHEN type = 'expense' THEN -amount ELSE 0 END), 0) as s")
            ->value('s');

        return [
            'period' => $start,
            'revenue' => $revenue,
            'expense' => $expense,
            'other' => $other,
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'net' => $totalRevenue - $totalExpense,
            'cash_balance' => $cash,
            'count' => (int) $rows->sum('n'),
        ];
    }
}
