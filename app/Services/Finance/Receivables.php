<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Umur piutang: tagihan belum bayar dikelompokkan per pelanggan & lama tunggakan
 * (dihitung dari tanggal periode tagihan).
 */
class Receivables
{
    public const BUCKETS = [
        '0-30' => '≤ 30 hari',
        '31-60' => '31–60 hari',
        '61-90' => '61–90 hari',
        '90+' => '> 90 hari',
    ];

    public static function bucket(Carbon $period, ?Carbon $today = null): string
    {
        $days = (int) $period->copy()->startOfDay()->diffInDays(($today ?? now())->copy()->startOfDay(), false);

        return match (true) {
            $days <= 30 => '0-30',
            $days <= 60 => '31-60',
            $days <= 90 => '61-90',
            default => '90+',
        };
    }

    /**
     * Filter: collector_id (id | 'none'), area (nama reseller/area), bucket, search.
     *
     * @return array{rows: Collection, buckets: array, total: float, invoice_count: int, collectors: Collection}
     */
    public static function aging(array $filters = []): array
    {
        $invoices = Invoice::query()
            ->where('status', 'unpaid')
            ->whereHas('customer')
            ->with(['customer.collector'])
            ->orderBy('period_date')
            ->get();

        $rows = $invoices->groupBy('customer_id')->map(function (Collection $items) {
            $customer = $items->first()->customer;
            $oldest = Carbon::parse($items->first()->period_date);

            return (object) [
                'customer' => $customer,
                'invoices' => $items,
                'count' => $items->count(),
                'total' => (float) $items->sum('amount'),
                'oldest' => $oldest,
                'days' => (int) $oldest->copy()->startOfDay()->diffInDays(now()->startOfDay()),
                'bucket' => self::bucket($oldest),
            ];
        });

        $rows = $rows->filter(function ($row) use ($filters) {
            $c = $row->customer;
            $collector = $filters['collector_id'] ?? null;
            if ($collector === 'none' && $c->collector_id) {
                return false;
            }
            if ($collector && $collector !== 'none' && (int) $c->collector_id !== (int) $collector) {
                return false;
            }
            if (!empty($filters['area']) && $c->entity_name !== $filters['area']) {
                return false;
            }
            if (!empty($filters['bucket']) && $row->bucket !== $filters['bucket']) {
                return false;
            }
            if (!empty($filters['search'])) {
                $s = mb_strtolower($filters['search']);
                $hay = mb_strtolower(implode(' ', [$c->name, $c->code, $c->customer_id, $c->phone, $c->address]));
                if (!str_contains($hay, $s)) {
                    return false;
                }
            }

            return true;
        })->sortByDesc(fn ($r) => [$r->days, $r->total])->values();

        // Ringkasan per umur dihitung per tagihan (bukan per pelanggan)
        $buckets = array_fill_keys(array_keys(self::BUCKETS), ['amount' => 0.0, 'count' => 0]);
        foreach ($rows as $row) {
            foreach ($row->invoices as $inv) {
                $b = self::bucket(Carbon::parse($inv->period_date));
                $buckets[$b]['amount'] += (float) $inv->amount;
                $buckets[$b]['count']++;
            }
        }

        $collectors = $rows->groupBy(fn ($r) => $r->customer->collector_id ?: 0)->map(fn ($g) => (object) [
            'name' => $g->first()->customer->collector->name ?? 'Belum ada kolektor',
            'id' => $g->first()->customer->collector_id,
            'customers' => $g->count(),
            'total' => (float) $g->sum('total'),
        ])->sortByDesc('total')->values();

        return [
            'rows' => $rows,
            'buckets' => $buckets,
            'total' => (float) $rows->sum('total'),
            'invoice_count' => (int) $rows->sum('count'),
            'collectors' => $collectors,
        ];
    }
}
