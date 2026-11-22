@extends('layouts.admin')

@section('content')
@include('partials.flash')

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Tagihan</h2>
        <p class="text-sm text-slate-500">Tagihan bulanan pelanggan. Pembayaran di sini langsung mengaktifkan internet pelanggan.</p>
    </div>
    <div class="flex items-center gap-2">
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600 hover:bg-slate-50">Lainnya ▾</button>
            <div x-show="open" x-cloak x-transition class="absolute right-0 mt-2 w-60 bg-white border border-slate-100 rounded-xl shadow-lg py-1 z-20 text-sm">
                <a href="{{ route('invoices.printList', request()->only(['status', 'month', 'year', 'search'])) }}" target="_blank" class="block px-4 py-2 hover:bg-slate-50">Cetak semua sesuai filter</a>
                <form action="{{ route('invoices.checkIsolir') }}" method="POST" onsubmit="return confirm('Isolir semua pelanggan yang lewat jatuh tempo dan belum bayar?')">
                    @csrf
                    <button class="w-full text-left px-4 py-2 hover:bg-slate-50">Cek isolir sekarang</button>
                </form>
            </div>
        </div>
        <form action="{{ route('invoices.generate') }}" method="POST">
            @csrf
            <button class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">+ Buat Tagihan Bulan Ini</button>
        </form>
    </div>
</div>

{{-- Ringkasan --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-slate-100 px-4 py-3">
        <div class="text-xs text-slate-400">Belum dibayar</div>
        <div class="text-lg font-bold text-rose-600">Rp {{ number_format($summary->unpaid_total ?? 0, 0, ',', '.') }}</div>
        <div class="text-xs text-slate-400">{{ (int) ($summary->unpaid_count ?? 0) }} tagihan</div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 px-4 py-3">
        <div class="text-xs text-slate-400">Sudah dibayar</div>
        <div class="text-lg font-bold text-emerald-600">Rp {{ number_format($summary->paid_total ?? 0, 0, ',', '.') }}</div>
        <div class="text-xs text-slate-400">sesuai filter</div>
    </div>
</div>

{{-- Filter --}}
<form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
    <div class="flex bg-white border border-slate-200 rounded-xl p-1">
        @foreach(['' => 'Semua', 'unpaid' => 'Belum bayar', 'paid' => 'Lunas'] as $k => $v)
            <a href="{{ route('invoices.index', array_filter(array_merge(request()->only(['month', 'year', 'search']), ['status' => $k]))) }}"
               class="px-3 py-1.5 rounded-lg text-sm whitespace-nowrap {{ (string) request('status') === (string) $k ? 'bg-slate-800 text-white font-semibold' : 'text-slate-500 hover:text-slate-800' }}">{{ $v }}</a>
        @endforeach
    </div>
    <input type="hidden" name="status" value="{{ request('status') }}">
    <select name="month" onchange="this.form.submit()" class="py-2 rounded-xl border-slate-200 text-sm text-slate-600">
        <option value="">Semua bulan</option>
        @foreach(range(1, 12) as $m)
            <option value="{{ $m }}" @selected(request('month') == $m)>{{ \Carbon\Carbon::create(null, $m, 1)->locale('id')->translatedFormat('F') }}</option>
        @endforeach
    </select>
    <select name="year" onchange="this.form.submit()" class="py-2 rounded-xl border-slate-200 text-sm text-slate-600">
        <option value="">Semua tahun</option>
        @foreach(range(date('Y') - 1, date('Y') + 1) as $y)
            <option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}</option>
        @endforeach
    </select>
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode, no. invoice…" class="flex-1 min-w-[200px] py-2 px-3 rounded-xl border-slate-200 text-sm">
</form>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3 font-semibold">Pelanggan</th>
                    <th class="px-4 py-3 font-semibold">Periode</th>
                    <th class="px-4 py-3 font-semibold text-right">Nominal</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
            @forelse($invoices as $inv)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        @if($inv->customer)
                            <a href="{{ route('customers.show', $inv->customer) }}" class="font-semibold text-slate-800 hover:text-sky-700">{{ $inv->customer->name }}</a>
                        @else
                            <span class="text-slate-400">Pelanggan dihapus</span>
                        @endif
                        <div class="text-xs text-slate-400 font-mono">{{ $inv->invoice_number }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($inv->period_date)->locale('id')->translatedFormat('F Y') }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-800 whitespace-nowrap">Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($inv->status === 'paid')
                            <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700">Lunas</span>
                            @if($inv->renewal_status === 'success')
                                <div class="text-xs text-slate-400 mt-1">Internet aktif{{ $inv->renewal_new_expiry ? ' s/d ' . $inv->renewal_new_expiry->locale('id')->translatedFormat('d M Y') : '' }}</div>
                            @elseif($inv->renewal_status === 'pending')
                                <div class="text-xs text-amber-600 mt-1">Mengaktifkan internet…</div>
                            @elseif($inv->renewal_status === 'failed')
                                <div class="text-xs text-rose-600 mt-1" title="{{ $inv->renewal_error }}">Aktivasi internet gagal</div>
                            @endif
                        @else
                            <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-rose-50 text-rose-700">Belum bayar</span>
                            @if($inv->customer?->displayStatus() === 'isolated')<div class="text-xs text-amber-600 mt-1">Pelanggan diisolir</div>@endif
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-3">
                            @if($inv->status === 'unpaid')
                                <form action="{{ route('invoices.paid', $inv->id) }}" method="POST" onsubmit="return confirm('Tandai lunas: {{ addslashes($inv->customer->name ?? '') }} Rp {{ number_format($inv->amount, 0, ',', '.') }}?')">
                                    @csrf
                                    <button class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold">Bayar</button>
                                </form>
                            @elseif($inv->renewal_status === 'failed')
                                <form action="{{ route('invoices.renewal.retry', $inv->id) }}" method="POST">
                                    @csrf
                                    <button class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold">Aktifkan ulang</button>
                                </form>
                                <form action="{{ route('invoices.renewal.resolve', $inv->id) }}" method="POST" onsubmit="return confirm('Pastikan layanan pelanggan ini sudah aktif. Tandai selesai?')">
                                    @csrf
                                    <button class="text-xs text-slate-500 hover:text-slate-800 font-semibold">Sudah aktif</button>
                                </form>
                            @endif
                            <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="text-xs text-slate-500 hover:text-slate-800 font-semibold">Cetak</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-14 text-center text-slate-400">Belum ada tagihan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())<div class="p-4 border-t border-slate-50">{{ $invoices->links() }}</div>@endif
</div>
@endsection
