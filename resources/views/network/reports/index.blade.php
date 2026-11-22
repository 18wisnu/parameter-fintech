@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => 'Laporan Jaringan',
    'subtitle' => 'Ringkasan pendapatan langganan & voucher serta kondisi pelanggan.',
])

@php
    $rp = fn ($v) => 'Rp ' . number_format($v ?? 0, 0, ',', '.');
    $rev = $revenue['revenue_items'] ?? [];
    $exp = $revenue['expense_items'] ?? [];
    $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
@endphp

<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-center">
    <select name="month" class="px-3 py-2 rounded-xl border-slate-200 text-sm">
        @foreach($months as $i => $m)<option value="{{ $i + 1 }}" @selected($month === $i + 1)>{{ $m }}</option>@endforeach
    </select>
    <select name="year" class="px-3 py-2 rounded-xl border-slate-200 text-sm">
        @for($y = now()->year; $y >= now()->year - 4; $y--)<option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>@endfor
    </select>
    <button class="bg-slate-900 hover:bg-slate-700 text-white rounded-xl text-xs font-black uppercase tracking-widest px-5 py-2.5">Tampilkan</button>
</form>

<h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-3">Keuangan {{ $months[$month - 1] }} {{ $year }}</h3>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @foreach([
        ['Pendapatan Langganan', $rp($rev['invoice']['invoice_revenue'] ?? 0), ($rev['invoice']['invoice_count'] ?? 0) . ' tagihan'],
        ['Pendapatan Voucher', $rp($rev['voucher']['voucher_revenue'] ?? 0), ($rev['voucher']['voucher_count'] ?? 0) . ' voucher'],
        ['Total Pengeluaran', $rp($exp['total_expense'] ?? 0), 'termasuk komisi ' . $rp($exp['commission'] ?? 0)],
        ['Laba Bersih', $rp($revenue['net_profit'] ?? 0), 'pendapatan total ' . $rp($rev['total_revenue'] ?? 0)],
    ] as [$label, $value, $hint])
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $label }}</p>
        <p class="mt-2 text-xl font-black text-slate-800 tabular-nums">{{ $value }}</p>
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    </div>
    @endforeach
</div>

<h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-3">Pelanggan Saat Ini</h3>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Pelanggan</p>
        <p class="mt-2 text-3xl font-black text-slate-800 tabular-nums">{{ number_format($customers['total_customers'] ?? 0, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-slate-500"><span class="text-emerald-600 font-bold">{{ $customers['online_now'] ?? 0 }} online</span> · {{ $customers['new_this_month'] ?? 0 }} baru bulan ini</p>
    </div>
    @foreach([
        ['Per Status', $customers['by_status'] ?? [], ['active' => 'Aktif', 'suspended' => 'Isolir', 'inactive' => 'Nonaktif', 'terminated' => 'Berhenti']],
        ['Per Jenis Layanan', $customers['by_service_type'] ?? [], ['ppp' => 'PPPoE', 'hotspot' => 'Hotspot', 'dhcp' => 'DHCP', 'static' => 'Static']],
    ] as [$title, $data, $labels])
    @php $max = max(1, ...array_values($data ?: [0])); @endphp
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">{{ $title }}</p>
        <div class="space-y-2">
            @foreach($data as $k => $v)
            <div>
                <div class="flex justify-between text-xs"><span class="text-slate-600">{{ $labels[$k] ?? $k }}</span><span class="font-bold text-slate-800 tabular-nums">{{ number_format($v, 0, ',', '.') }}</span></div>
                <div class="h-2 bg-slate-100 rounded-full mt-1"><div class="h-2 rounded-full bg-sky-500" style="width: {{ round($v / $max * 100) }}%"></div></div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection
