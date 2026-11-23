@extends('layouts.admin')

@section('content')
@include('partials.flash')
@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $sel = 'py-2 rounded-xl border-slate-200 text-sm text-slate-600';
    $tone = ['0-30' => 'text-slate-700 bg-slate-100', '31-60' => 'text-amber-800 bg-amber-100', '61-90' => 'text-orange-800 bg-orange-100', '90+' => 'text-rose-800 bg-rose-100'];
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Umur Piutang</h2>
        <p class="text-sm text-slate-500">Tagihan belum bayar, dikelompokkan menurut lama tunggakan, kolektor dan area.</p>
    </div>
    <a href="{{ route('finance.receivables.print', request()->query()) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold">Cetak daftar tagih</a>
</div>

{{-- Ringkasan per umur --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-slate-100 px-4 py-3">
        <div class="text-xs text-slate-400">Total piutang</div>
        <div class="text-lg font-bold text-slate-800">{{ $rp($total) }}</div>
        <div class="text-xs text-slate-400">{{ $rows->count() }} pelanggan · {{ $invoice_count }} tagihan</div>
    </div>
    @foreach($bucketLabels as $key => $label)
        <a href="{{ route('finance.receivables.index', array_merge($filters, ['bucket' => ($filters['bucket'] ?? null) === $key ? null : $key])) }}"
           class="bg-white rounded-2xl border px-4 py-3 hover:border-sky-300 {{ ($filters['bucket'] ?? null) === $key ? 'border-sky-400 ring-1 ring-sky-200' : 'border-slate-100' }}">
            <div class="text-xs text-slate-400">{{ $label }}</div>
            <div class="text-lg font-bold {{ $key === '90+' ? 'text-rose-600' : ($key === '0-30' ? 'text-slate-700' : 'text-amber-700') }}">{{ $rp($buckets[$key]['amount']) }}</div>
            <div class="text-xs text-slate-400">{{ $buckets[$key]['count'] }} tagihan</div>
        </a>
    @endforeach
</div>

<form method="GET" class="bg-white rounded-2xl border border-slate-100 p-3 mb-4 flex flex-wrap items-center gap-2">
    <select name="collector_id" class="{{ $sel }}">
        <option value="">Semua kolektor</option>
        <option value="none" @selected(($filters['collector_id'] ?? '') === 'none')>Belum ada kolektor</option>
        @foreach($collectorOptions as $u)<option value="{{ $u->id }}" @selected(($filters['collector_id'] ?? '') == $u->id)>{{ $u->name }}</option>@endforeach
    </select>
    @if($areas->isNotEmpty())
    <select name="area" class="{{ $sel }}">
        <option value="">Semua area / reseller</option>
        @foreach($areas as $a)<option value="{{ $a }}" @selected(($filters['area'] ?? '') === $a)>{{ $a }}</option>@endforeach
    </select>
    @endif
    <select name="bucket" class="{{ $sel }}">
        <option value="">Semua umur</option>
        @foreach($bucketLabels as $k => $v)<option value="{{ $k }}" @selected(($filters['bucket'] ?? '') === $k)>{{ $v }}</option>@endforeach
    </select>
    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama / ID / alamat…" class="flex-1 min-w-[180px] py-2 px-3 rounded-xl border-slate-200 text-sm">
    <button class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">Terapkan</button>
    @if($filters)<a href="{{ route('finance.receivables.index') }}" class="text-sm text-slate-500 hover:underline">Reset</a>@endif
</form>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
    <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-xs text-slate-400 text-left border-b border-slate-50">
                <tr>
                    <th class="px-5 py-3 font-medium">Pelanggan</th>
                    <th class="px-3 py-3 font-medium">Kolektor</th>
                    <th class="px-3 py-3 font-medium">Tunggakan</th>
                    <th class="px-5 py-3 font-medium text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
            @forelse($rows as $row)
                @php $c = $row->customer; @endphp
                <tr class="align-top">
                    <td class="px-5 py-3">
                        <a href="{{ route('customers.show', $c) }}" class="font-semibold text-slate-800 hover:text-sky-700">{{ $c->name }}</a>
                        <div class="text-xs text-slate-400">{{ $c->displayCode() }}{{ $c->phone ? ' · ' . $c->phone : '' }}{{ $c->entity_name ? ' · ' . $c->entity_name : '' }}</div>
                        @if($c->address)<div class="text-xs text-slate-400 truncate max-w-xs">{{ $c->address }}</div>@endif
                    </td>
                    <td class="px-3 py-3 text-slate-600">{!! $c->collector ? e($c->collector->name) : '<span class="text-slate-300">—</span>' !!}</td>
                    <td class="px-3 py-3">
                        <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full {{ $tone[$row->bucket] }}">{{ $row->days }} hari</span>
                        <div class="text-xs text-slate-400 mt-1">{{ $row->count }} bulan: {{ $row->invoices->map(fn ($i) => \Carbon\Carbon::parse($i->period_date)->locale('id')->translatedFormat('M y'))->implode(', ') }}</div>
                    </td>
                    <td class="px-5 py-3 text-right font-semibold text-slate-800 whitespace-nowrap">{{ $rp($row->total) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-5 py-12 text-center text-slate-400">Tidak ada tunggakan. 🎉</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden self-start">
        <div class="px-5 py-4 border-b border-slate-50">
            <h3 class="font-semibold text-slate-800">Per kolektor</h3>
            <p class="text-xs text-slate-400">Atur kolektor di halaman edit pelanggan.</p>
        </div>
        <table class="w-full text-sm">
            <tbody class="divide-y divide-slate-50">
            @forelse($collectors as $col)
                <tr>
                    <td class="px-5 py-3">
                        <a href="{{ route('finance.receivables.index', array_merge($filters, ['collector_id' => $col->id ?: 'none'])) }}" class="text-slate-700 hover:text-sky-700">{{ $col->name }}</a>
                        <div class="text-xs text-slate-400">{{ $col->customers }} pelanggan</div>
                    </td>
                    <td class="px-5 py-3 text-right font-semibold whitespace-nowrap">{{ $rp($col->total) }}</td>
                </tr>
            @empty
                <tr><td class="px-5 py-8 text-center text-slate-400">—</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
