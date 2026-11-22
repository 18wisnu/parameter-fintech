@extends('layouts.admin')

@section('content')
@include('partials.flash')
@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $num = fn ($n) => number_format((float) $n, 0, ',', '.');
    $money = ['income_month', 'commission_month', 'income_today'];
    $fmt = fn ($k, $v) => in_array($k, $money, true) ? $rp($v) : $num($v);
    $max = max(1, (float) ($resellers->max(fn ($r) => (float) ($r['stats'][$sort] ?? 0)) ?? 0));
    $types = ['reseller' => 'Reseller', 'outlet' => 'Outlet'];
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Analisa Voucher</h2>
        <p class="text-sm text-slate-500">Reseller dan outlet mana yang paling banyak menghasilkan.</p>
    </div>
    @if($network)
    <form method="GET">
        <select name="sort" onchange="this.form.submit()" class="py-2 rounded-xl border-slate-200 text-sm text-slate-600">
            @foreach($metrics as $k => $v)<option value="{{ $k }}" @selected($sort === $k)>Urutkan: {{ $v }}</option>@endforeach
        </select>
    </form>
    @endif
</div>

@if(!$network)
    <div class="bg-white rounded-2xl border border-slate-100 p-5 mb-4 text-sm text-slate-600">
        Peringkat reseller diambil dari server jaringan. Hubungkan server dulu di <b>Pengaturan → Server Jaringan</b> (khusus pemilik).
        Sementara itu, di bawah ditampilkan setoran voucher yang tercatat di buku kas.
    </div>
@elseif($networkError)
    <div class="mb-4 p-3 rounded-xl bg-amber-50 text-amber-800 text-sm">Data reseller dari server belum bisa dimuat: {{ $networkError }}</div>
@else
    {{-- Ringkasan semua mitra --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="bg-white rounded-2xl border border-slate-100 px-4 py-3">
            <div class="text-xs text-slate-400">Pendapatan bulan ini</div>
            <div class="text-lg font-bold text-emerald-600">{{ $rp($totals['income_month']) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 px-4 py-3">
            <div class="text-xs text-slate-400">Transaksi bulan ini</div>
            <div class="text-lg font-bold text-slate-800">{{ $num($totals['trx_count_month']) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 px-4 py-3">
            <div class="text-xs text-slate-400">Voucher dibuat bulan ini</div>
            <div class="text-lg font-bold text-slate-800">{{ $num($totals['voucher_gen_month']) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 px-4 py-3">
            <div class="text-xs text-slate-400">Komisi bulan ini</div>
            <div class="text-lg font-bold text-slate-800">{{ $rp($totals['commission_month']) }}</div>
        </div>
    </div>

    {{-- Peringkat --}}
    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden mb-4">
        <div class="px-5 py-4 border-b border-slate-50 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Peringkat reseller & outlet</h3>
            <span class="text-xs text-slate-400">diperbarui tiap 10 menit{{ $limited ? ' · 40 mitra pertama' : '' }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 font-semibold w-8">#</th>
                        <th class="px-4 py-3 font-semibold">Mitra</th>
                        <th class="px-4 py-3 font-semibold min-w-[180px]">{{ $metrics[$sort] }}</th>
                        <th class="px-4 py-3 font-semibold text-right">Pendapatan bln ini</th>
                        <th class="px-4 py-3 font-semibold text-right">Hari ini</th>
                        <th class="px-4 py-3 font-semibold text-right">Transaksi</th>
                        <th class="px-4 py-3 font-semibold text-right">Voucher dibuat</th>
                        <th class="px-4 py-3 font-semibold text-right">Stok</th>
                        <th class="px-4 py-3 font-semibold text-right">Komisi</th>
                        <th class="px-4 py-3 font-semibold text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                @forelse($resellers as $i => $r)
                    @php $s = $r['stats']; $v = (float) ($s[$sort] ?? 0); @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-bold {{ $i < 3 ? 'text-amber-600' : 'text-slate-400' }}">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            @if($isOwner)
                                <a href="{{ route('network.entities.show', $r['id']) }}" class="font-semibold text-slate-800 hover:text-sky-700">{{ $r['name'] }}</a>
                            @else
                                <span class="font-semibold text-slate-800">{{ $r['name'] }}</span>
                            @endif
                            <div class="text-xs text-slate-400">{{ $types[$r['type']] ?? $r['type'] }}{{ $r['code'] ? ' · ' . $r['code'] : '' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-sky-500 rounded-full" style="width: {{ round($v / $max * 100) }}%"></div></div>
                                <span class="text-xs font-semibold text-slate-700 whitespace-nowrap">{{ $fmt($sort, $v) }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap font-semibold text-emerald-700">{{ $rp($s['income_month'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap text-slate-600">{{ $rp($s['income_today'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-right text-slate-600">{{ $num($s['trx_count_month'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-right text-slate-600">{{ $num($s['voucher_gen_month'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-right text-slate-600">{{ $num($s['voucher_stock'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap text-slate-600">{{ $rp($s['commission_month'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap text-slate-600">{{ $rp($r['balance']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-12 text-center text-slate-400">Belum ada reseller atau outlet di server jaringan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- Dari buku kas --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <h3 class="font-semibold text-slate-800 mb-1">Setoran voucher per bulan</h3>
        <p class="text-xs text-slate-400 mb-4">Dari buku kas (akun Pendapatan Voucher), 6 bulan terakhir.</p>
        @php $tmax = max(1, $trend->max('total')); @endphp
        <div class="flex items-end gap-2 h-40">
            @foreach($trend as $t)
                <div class="flex-1 flex flex-col items-center justify-end h-full" title="{{ $rp($t['total']) }} · {{ $t['count'] }} setoran">
                    <div class="w-full bg-sky-500 rounded-t-md" style="height: {{ max(2, round($t['total'] / $tmax * 100)) }}%"></div>
                    <span class="text-[10px] text-slate-400 mt-1 whitespace-nowrap">{{ $t['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <h3 class="font-semibold text-slate-800 mb-1">Penyetor voucher terbanyak</h3>
        <p class="text-xs text-slate-400 mb-4">Setoran kolektor untuk pelanggan bertipe Reseller/Hotspot.</p>
        <div class="space-y-2">
            @forelse($ledgerResellers as $r)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-700">{{ $r['name'] }} <span class="text-xs text-slate-400">· {{ $r['count'] }}×</span></span>
                    <span class="font-semibold text-slate-800">{{ $rp($r['total']) }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">Belum ada setoran voucher di buku kas.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
