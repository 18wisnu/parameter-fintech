@extends('layouts.admin')

@section('content')
@include('partials.flash')
@php
    $rp = fn ($n) => ($n < 0 ? '− ' : '') . 'Rp ' . number_format(abs((float) $n), 0, ',', '.');
    $monthLabel = $month->locale('id')->translatedFormat('F Y');
    $delta = function ($now, $before) {
        if ((float) $before == 0) return null;
        return round(($now - $before) / abs($before) * 100);
    };
    $netDelta = $delta($report['net'], $previous['net']);
    $maxBar = max(1, collect($trend)->flatMap(fn ($t) => [$t['revenue'], $t['expense']])->max());
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5 print:hidden">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Laba Rugi & Tutup Buku</h2>
        <p class="text-sm text-slate-500">Pendapatan dikurangi beban per bulan, dari buku kas.</p>
    </div>
    <div class="flex items-center gap-2">
        <form method="GET">
            <input type="month" name="month" value="{{ $month->format('Y-m') }}" max="{{ now()->format('Y-m') }}" onchange="this.form.submit()" class="py-2 rounded-xl border-slate-200 text-sm text-slate-600">
        </form>
        <button onclick="window.print()" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600 hover:bg-slate-50">Cetak</button>
    </div>
</div>

{{-- Status tutup buku --}}
<div class="print:hidden mb-4">
@if($closing)
    <div class="flex flex-wrap items-center justify-between gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-4">
        <div class="text-sm text-emerald-900">
            <b>🔒 Buku {{ $monthLabel }} sudah ditutup</b> pada {{ $closing->created_at->format('d/m/Y H:i') }} oleh {{ $closing->closer->name ?? '-' }}.
            Transaksi bulan ini tidak bisa ditambah, diubah atau dihapus.
            @if($closing->notes)<div class="text-emerald-800/80 mt-1">Catatan: {{ $closing->notes }}</div>@endif
            @if((float) $closing->net !== (float) $report['net'])
                <div class="text-amber-800 mt-1">Laba bersih saat ditutup: {{ $rp($closing->net) }}.</div>
            @endif
        </div>
        @if($isOwner)
        <form method="POST" action="{{ route('finance.pl.reopen', $closing) }}" onsubmit="return confirm('Buka kembali buku {{ $monthLabel }}? Transaksi bulan itu akan bisa diubah lagi.')">
            @csrf @method('DELETE')
            <button class="px-4 py-2 rounded-xl border border-emerald-300 bg-white text-sm font-semibold text-emerald-800 hover:bg-emerald-100">Buka kembali</button>
        </form>
        @endif
    </div>
@elseif($canClose)
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white border border-slate-200 rounded-2xl px-5 py-4">
        <div class="text-sm text-slate-600">
            <b class="text-slate-800">Buku {{ $monthLabel }} belum ditutup.</b>
            Setelah semua transaksi bulan ini dicek, tutup buku agar angkanya tidak berubah lagi.
        </div>
        @if($isOwner)
        <form method="POST" action="{{ route('finance.pl.close') }}" class="flex flex-wrap gap-2" onsubmit="return confirm('Tutup buku {{ $monthLabel }}? Transaksi bulan itu akan dikunci.')">
            @csrf
            <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
            <input type="text" name="notes" placeholder="Catatan (opsional)" class="py-2 px-3 rounded-xl border-slate-200 text-sm">
            <button class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold">Tutup buku</button>
        </form>
        @else
            <span class="text-xs text-slate-400">Hanya pemilik yang bisa menutup buku.</span>
        @endif
    </div>
@else
    <div class="bg-sky-50 border border-sky-100 rounded-2xl px-5 py-3 text-sm text-sky-800">Bulan berjalan — angka masih bisa bertambah. Tutup buku bisa dilakukan mulai {{ $month->copy()->addMonthNoOverflow()->startOfMonth()->locale('id')->translatedFormat('j F Y') }}.</div>
@endif
</div>

<div class="hidden print:block mb-4">
    <h1 class="text-xl font-bold">Laporan Laba Rugi — {{ $monthLabel }}</h1>
    <div class="text-sm">{{ $appSettings['business_name'] ?? '' }} · dicetak {{ now()->format('d/m/Y H:i') }}{{ $closing ? ' · buku sudah ditutup' : '' }}</div>
</div>

{{-- Ringkasan --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-slate-100 px-4 py-3">
        <div class="text-xs text-slate-400">Pendapatan</div>
        <div class="text-lg font-bold text-emerald-600">{{ $rp($report['total_revenue']) }}</div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 px-4 py-3">
        <div class="text-xs text-slate-400">Beban</div>
        <div class="text-lg font-bold text-rose-600">{{ $rp($report['total_expense']) }}</div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 px-4 py-3">
        <div class="text-xs text-slate-400">Laba bersih</div>
        <div class="text-lg font-bold {{ $report['net'] < 0 ? 'text-rose-700' : 'text-slate-800' }}">{{ $rp($report['net']) }}</div>
        @if($netDelta !== null)<div class="text-xs {{ $netDelta >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $netDelta >= 0 ? '▲' : '▼' }} {{ abs($netDelta) }}% dari {{ $previous['label'] }}</div>@endif
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 px-4 py-3">
        <div class="text-xs text-slate-400">Saldo kas akhir bulan</div>
        <div class="text-lg font-bold text-slate-700">{{ $rp($report['cash_balance']) }}</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Laporan --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <table class="w-full text-sm">
            <tbody>
                <tr class="bg-emerald-50/60"><td colspan="2" class="px-5 py-2 text-xs font-bold uppercase tracking-wider text-emerald-800">Pendapatan</td></tr>
                @forelse($report['revenue'] as $r)
                    <tr class="border-b border-slate-50">
                        <td class="px-5 py-2.5 text-slate-700"><span class="text-xs text-slate-400 font-mono mr-2">{{ $r['code'] }}</span>
                            <a href="{{ route('reports.history', ['start_date' => $month->toDateString(), 'end_date' => $month->copy()->endOfMonth()->toDateString(), 'account_id' => $r['id']]) }}" class="hover:text-sky-700">{{ $r['name'] }}</a>
                            <span class="text-xs text-slate-400">· {{ $r['count'] }} transaksi</span></td>
                        <td class="px-5 py-2.5 text-right whitespace-nowrap">{{ $rp($r['total']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="px-5 py-3 text-slate-400">Tidak ada pendapatan.</td></tr>
                @endforelse
                <tr class="font-semibold"><td class="px-5 py-2.5">Total pendapatan</td><td class="px-5 py-2.5 text-right text-emerald-700 whitespace-nowrap">{{ $rp($report['total_revenue']) }}</td></tr>

                <tr class="bg-rose-50/60"><td colspan="2" class="px-5 py-2 text-xs font-bold uppercase tracking-wider text-rose-800">Beban</td></tr>
                @forelse($report['expense'] as $r)
                    <tr class="border-b border-slate-50">
                        <td class="px-5 py-2.5 text-slate-700"><span class="text-xs text-slate-400 font-mono mr-2">{{ $r['code'] }}</span>
                            <a href="{{ route('reports.history', ['start_date' => $month->toDateString(), 'end_date' => $month->copy()->endOfMonth()->toDateString(), 'account_id' => $r['id']]) }}" class="hover:text-sky-700">{{ $r['name'] }}</a>
                            <span class="text-xs text-slate-400">· {{ $r['count'] }} transaksi</span></td>
                        <td class="px-5 py-2.5 text-right whitespace-nowrap">{{ $rp($r['total']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="px-5 py-3 text-slate-400">Tidak ada beban.</td></tr>
                @endforelse
                <tr class="font-semibold"><td class="px-5 py-2.5">Total beban</td><td class="px-5 py-2.5 text-right text-rose-700 whitespace-nowrap">{{ $rp($report['total_expense']) }}</td></tr>

                <tr class="border-t-2 border-slate-200 text-base font-bold"><td class="px-5 py-3">Laba (rugi) bersih</td><td class="px-5 py-3 text-right whitespace-nowrap {{ $report['net'] < 0 ? 'text-rose-700' : 'text-slate-900' }}">{{ $rp($report['net']) }}</td></tr>

                @if($report['other'])
                    <tr class="bg-slate-50"><td colspan="2" class="px-5 py-2 text-xs font-bold uppercase tracking-wider text-slate-500">Di luar laba rugi (modal, dana cadangan, dll.)</td></tr>
                    @foreach($report['other'] as $r)
                        <tr class="border-b border-slate-50 text-slate-500">
                            <td class="px-5 py-2"><span class="text-xs font-mono mr-2">{{ $r['code'] }}</span>{{ $r['name'] }} <span class="text-xs">· {{ $r['flow'] === 'income' ? 'masuk' : 'keluar' }}</span></td>
                            <td class="px-5 py-2 text-right whitespace-nowrap">{{ $r['flow'] === 'income' ? '+' : '−' }} {{ $rp($r['total']) }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <div class="space-y-4 print:hidden">
        {{-- Tren 6 bulan --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5">
            <h3 class="font-semibold text-slate-800 mb-1">6 bulan terakhir</h3>
            <div class="flex items-center gap-3 text-xs text-slate-500 mb-3">
                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span>Pendapatan</span>
                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-rose-400"></span>Beban</span>
            </div>
            <div class="space-y-2.5">
                @foreach($trend as $t)
                    <a href="{{ route('finance.pl.index', ['month' => $t['month']]) }}" class="block group {{ $t['month'] === $month->format('Y-m') ? '' : 'opacity-80 hover:opacity-100' }}">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="{{ $t['month'] === $month->format('Y-m') ? 'font-bold text-slate-800' : 'text-slate-500' }}">{{ $t['label'] }}</span>
                            <span class="{{ $t['net'] < 0 ? 'text-rose-600' : 'text-slate-700' }} font-semibold">{{ $rp($t['net']) }}</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-emerald-500 mb-0.5" style="width: {{ max(1, $t['revenue'] / $maxBar * 100) }}%"></div>
                        <div class="h-1.5 rounded-full bg-rose-400" style="width: {{ max(1, $t['expense'] / $maxBar * 100) }}%"></div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Riwayat tutup buku --}}
        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-50"><h3 class="font-semibold text-slate-800">Bulan yang sudah ditutup</h3></div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-50">
                @forelse($closings as $c)
                    <tr>
                        <td class="px-5 py-2.5"><a href="{{ route('finance.pl.index', ['month' => $c->period->format('Y-m')]) }}" class="text-sky-700 hover:underline">🔒 {{ $c->period->locale('id')->translatedFormat('F Y') }}</a>
                            <div class="text-xs text-slate-400">{{ $c->closer->name ?? '-' }} · {{ $c->created_at->format('d/m/Y') }}</div></td>
                        <td class="px-5 py-2.5 text-right whitespace-nowrap font-semibold {{ $c->net < 0 ? 'text-rose-600' : 'text-slate-700' }}">{{ $rp($c->net) }}</td>
                    </tr>
                @empty
                    <tr><td class="px-5 py-6 text-center text-slate-400">Belum ada.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
