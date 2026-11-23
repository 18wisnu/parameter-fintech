@extends('layouts.admin')

@section('content')
@include('partials.flash')
@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $input = 'w-full py-2 px-3 rounded-xl border-slate-200 text-sm';
    $isToday = $date === now()->toDateString();
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Tutup Kas Harian</h2>
        <p class="text-sm text-slate-500">Hitung uang yang diserahkan kolektor, cocokkan dengan setoran yang tercatat.</p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <input type="date" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}" onchange="this.form.submit()" class="py-2 rounded-xl border-slate-200 text-sm text-slate-600">
        @unless($isToday)<a href="{{ route('finance.cash.index') }}" class="text-sm font-semibold text-sky-600 hover:underline">Hari ini</a>@endunless
    </form>
</div>

<div class="space-y-3 mb-8">
    @forelse($rows as $row)
        @php
            $c = $row->closing;
            $stale = $c && ((float) $c->expected_amount !== $row->expected || $c->deposit_count !== $row->count);
        @endphp
        <div class="bg-white rounded-2xl border {{ $c ? ($c->difference == 0 && !$stale ? 'border-emerald-200' : 'border-amber-200') : 'border-slate-100' }} p-4"
             x-data="{ actual: '{{ old('user_id') == $row->user->id ? old('actual_amount') : ($c ? (int) $c->actual_amount : '') }}', expected: {{ $row->expected }} }">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="font-semibold text-slate-800">{{ $row->user->name }}
                        <span class="ml-1 text-xs font-normal text-slate-400 capitalize">{{ $row->user->role }}</span>
                    </div>
                    <div class="text-sm text-slate-500">{{ $row->count }} setoran tercatat ·
                        <span class="font-semibold text-slate-700">{{ $rp($row->expected) }}</span>
                        @if($row->count)
                            · <a href="{{ route('reports.history', ['start_date' => $date, 'end_date' => $date, 'user_id' => $row->user->id]) }}" class="text-sky-600 hover:underline">lihat rincian</a>
                        @endif
                    </div>
                </div>
                @if($c)
                    <div class="text-right text-sm">
                        <div class="font-semibold {{ $c->difference == 0 ? 'text-emerald-700' : ($c->difference < 0 ? 'text-rose-600' : 'text-amber-700') }}">
                            {{ $c->difference == 0 ? 'Cocok' : ($c->difference < 0 ? 'Kurang ' . $rp(abs($c->difference)) : 'Lebih ' . $rp($c->difference)) }}
                        </div>
                        <div class="text-xs text-slate-400">Ditutup {{ $c->updated_at->format('H:i') }} oleh {{ $c->closer->name ?? '-' }} · diterima {{ $rp($c->actual_amount) }}</div>
                    </div>
                @endif
            </div>

            @if($stale)
                <p class="mt-3 text-xs text-amber-800 bg-amber-50 rounded-lg px-3 py-2">Ada setoran baru setelah kas ditutup (saat ditutup: {{ $c->deposit_count }} setoran, {{ $rp($c->expected_amount) }}). Tutup ulang untuk memperbarui.</p>
            @endif

            <form method="POST" action="{{ route('finance.cash.store') }}" class="mt-3 grid grid-cols-1 sm:grid-cols-[180px_1fr_auto] gap-2 items-start">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <input type="hidden" name="user_id" value="{{ $row->user->id }}">
                <div>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-sm text-slate-400">Rp</span>
                        <input type="number" name="actual_amount" min="0" step="1" required x-model="actual" placeholder="Uang diterima" class="{{ $input }} pl-9">
                    </div>
                    <div class="mt-1 text-xs" x-show="actual !== ''" x-cloak>
                        <span x-show="Number(actual) === expected" class="text-emerald-600">Cocok</span>
                        <span x-show="Number(actual) < expected" class="text-rose-600" x-text="'Kurang Rp ' + (expected - Number(actual)).toLocaleString('id-ID')"></span>
                        <span x-show="Number(actual) > expected" class="text-amber-700" x-text="'Lebih Rp ' + (Number(actual) - expected).toLocaleString('id-ID')"></span>
                    </div>
                </div>
                <input type="text" name="notes" value="{{ old('user_id') == $row->user->id ? old('notes') : $c?->notes }}" placeholder="Catatan (wajib bila ada selisih)" class="{{ $input }}">
                <div class="flex gap-2">
                    <button type="button" @click="actual = String(expected)" class="px-3 py-2 rounded-xl border border-slate-200 text-sm text-slate-600 hover:bg-slate-50" title="Isi sama dengan tercatat">= Tercatat</button>
                    <button class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold">{{ $c ? 'Tutup ulang' : 'Tutup kas' }}</button>
                </div>
            </form>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-10 text-center text-slate-400 text-sm">
            Belum ada kolektor atau setoran pada tanggal ini. Tambahkan pegawai dengan peran kolektor di Data Pegawai.
        </div>
    @endforelse
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-50">
            <h3 class="font-semibold text-slate-800">Total selisih per kolektor</h3>
            <p class="text-xs text-slate-400">Akumulasi semua tutup kas. Minus = uang kurang setor.</p>
        </div>
        <table class="w-full text-sm">
            <tbody class="divide-y divide-slate-50">
            @forelse($outstanding as $o)
                <tr>
                    <td class="px-5 py-3 text-slate-700">{{ $o->user->name ?? '-' }}<div class="text-xs text-slate-400">{{ $o->n }} hari berselisih</div></td>
                    <td class="px-5 py-3 text-right font-semibold whitespace-nowrap {{ $o->diff < 0 ? 'text-rose-600' : 'text-amber-700' }}">{{ $rp($o->diff) }}</td>
                </tr>
            @empty
                <tr><td class="px-5 py-8 text-center text-slate-400">Tidak ada selisih.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-50"><h3 class="font-semibold text-slate-800">Riwayat tutup kas</h3></div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-xs text-slate-400 text-left">
                <tr><th class="px-5 py-2 font-medium">Tanggal</th><th class="px-3 py-2 font-medium">Kolektor</th><th class="px-3 py-2 font-medium text-right">Tercatat</th><th class="px-3 py-2 font-medium text-right">Diterima</th><th class="px-5 py-2 font-medium text-right">Selisih</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
            @forelse($history as $h)
                <tr>
                    <td class="px-5 py-3 whitespace-nowrap"><a href="{{ route('finance.cash.index', ['date' => $h->date->toDateString()]) }}" class="text-sky-600 hover:underline">{{ $h->date->format('d/m/Y') }}</a></td>
                    <td class="px-3 py-3 text-slate-700">{{ $h->user->name ?? '-' }}@if($h->notes)<div class="text-xs text-slate-400">{{ $h->notes }}</div>@endif</td>
                    <td class="px-3 py-3 text-right whitespace-nowrap">{{ $rp($h->expected_amount) }}</td>
                    <td class="px-3 py-3 text-right whitespace-nowrap">{{ $rp($h->actual_amount) }}</td>
                    <td class="px-5 py-3 text-right whitespace-nowrap font-semibold {{ $h->difference == 0 ? 'text-emerald-600' : ($h->difference < 0 ? 'text-rose-600' : 'text-amber-700') }}">{{ $h->difference == 0 ? '✓' : $rp($h->difference) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada tutup kas.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
