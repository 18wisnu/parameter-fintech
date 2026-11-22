@extends('layouts.admin')

@section('content')
@include('partials.flash')
@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $sel = 'py-2 rounded-xl border-slate-200 text-sm text-slate-600';
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Buku Kas</h2>
        <p class="text-sm text-slate-500">Semua pemasukan, setoran kolektor dan pengeluaran beserta detailnya.</p>
    </div>
    <div class="flex items-center gap-2">
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600 hover:bg-slate-50">Export ▾</button>
            <div x-show="open" x-cloak class="absolute right-0 mt-2 w-44 bg-white border border-slate-100 rounded-xl shadow-lg py-1 z-20 text-sm">
                <a href="{{ route('reports.history.excel', request()->query()) }}" class="block px-4 py-2 hover:bg-slate-50">Excel (lengkap)</a>
                <a href="{{ route('reports.history.pdf', request()->query()) }}" class="block px-4 py-2 hover:bg-slate-50">PDF</a>
            </div>
        </div>
        <a href="{{ route('incomes.create') }}" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-emerald-700 hover:bg-emerald-50">+ Pemasukan</a>
        <a href="{{ route('expenses.create') }}" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold">+ Pengeluaran</a>
    </div>
</div>

{{-- Ringkasan --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-slate-100 px-4 py-3">
        <div class="text-xs text-slate-400">Saldo awal</div>
        <div class="text-lg font-bold text-slate-700">{{ $rp($opening) }}</div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 px-4 py-3">
        <div class="text-xs text-slate-400">Masuk</div>
        <div class="text-lg font-bold text-emerald-600">{{ $rp($totals->income) }}</div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 px-4 py-3">
        <div class="text-xs text-slate-400">Keluar</div>
        <div class="text-lg font-bold text-rose-600">{{ $rp($totals->expense) }}</div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 px-4 py-3">
        <div class="text-xs text-slate-400">Saldo akhir</div>
        <div class="text-lg font-bold text-slate-800">{{ $rp($closing) }}</div>
    </div>
</div>

{{-- Filter --}}
<form method="GET" class="bg-white rounded-2xl border border-slate-100 p-3 mb-4 flex flex-wrap items-center gap-2">
    <input type="date" name="start_date" value="{{ request('start_date') }}" class="{{ $sel }}" title="Dari tanggal">
    <span class="text-slate-400 text-sm">–</span>
    <input type="date" name="end_date" value="{{ request('end_date') }}" class="{{ $sel }}" title="Sampai tanggal">
    <select name="type" class="{{ $sel }}">
        <option value="">Semua arus</option>
        <option value="income" @selected(request('type') === 'income')>Pemasukan</option>
        <option value="expense" @selected(request('type') === 'expense')>Pengeluaran</option>
    </select>
    <select name="account_id" class="{{ $sel }}">
        <option value="">Semua akun</option>
        @foreach($accounts as $a)<option value="{{ $a->id }}" @selected(request('account_id') == $a->id)>{{ $a->name }}</option>@endforeach
    </select>
    <select name="user_id" class="{{ $sel }}">
        <option value="">Semua petugas</option>
        @foreach($staff as $s)<option value="{{ $s->id }}" @selected(request('user_id') == $s->id)>{{ $s->name }}</option>@endforeach
    </select>
    <select name="payment_method" class="{{ $sel }}">
        <option value="">Semua metode</option>
        @foreach($methods as $k => $v)<option value="{{ $k }}" @selected(request('payment_method') === $k)>{{ $v }}</option>@endforeach
    </select>
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari keterangan / no. ref…" class="flex-1 min-w-[180px] py-2 px-3 rounded-xl border-slate-200 text-sm">
    <button class="px-4 py-2 rounded-xl bg-slate-800 text-white text-sm font-semibold">Terapkan</button>
    @if(request()->hasAny(['start_date', 'end_date', 'type', 'account_id', 'user_id', 'payment_method', 'search']))
        <a href="{{ route('reports.history') }}" class="text-sm text-slate-500 hover:text-slate-800">Reset</a>
    @endif
</form>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3 font-semibold">Tanggal</th>
                    <th class="px-4 py-3 font-semibold">Keterangan</th>
                    <th class="px-4 py-3 font-semibold">Akun</th>
                    <th class="px-4 py-3 font-semibold">Petugas</th>
                    <th class="px-4 py-3 font-semibold">Ref / Metode</th>
                    <th class="px-4 py-3 font-semibold text-right">Masuk</th>
                    <th class="px-4 py-3 font-semibold text-right">Keluar</th>
                    <th class="px-4 py-3 font-semibold text-right">Saldo</th>
                    <th class="px-2 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
            @forelse($transactions as $trx)
                <tr class="align-top hover:bg-slate-50">
                    <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                        {{ \Carbon\Carbon::parse($trx->date)->locale('id')->translatedFormat('d M Y') }}
                        <div class="text-xs text-slate-400">{{ $trx->created_at?->format('H:i') }}</div>
                    </td>
                    <td class="px-4 py-3 min-w-[220px]">
                        <div class="text-slate-800">{{ $trx->description }}</div>
                        @if($trx->customer)
                            <a href="{{ route('customers.show', $trx->customer) }}" class="text-xs text-sky-600 hover:underline">{{ $trx->customer->name }}{{ $trx->customer->displayCode() ? ' · ' . $trx->customer->displayCode() : '' }}</a>
                        @endif
                        @if($trx->notes)<div class="text-xs text-slate-400">{{ $trx->notes }}</div>@endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        {{ $trx->account->name ?? '—' }}
                        @if($trx->sourceAccount)<div class="text-xs text-slate-400">{{ $trx->type === 'expense' ? 'dari' : 'ke' }} {{ $trx->sourceAccount->name }}</div>@endif
                    </td>
                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $trx->user->name ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($trx->invoice_id)
                            <a href="{{ route('invoices.print', $trx->invoice_id) }}" target="_blank" class="font-mono text-xs text-sky-600 hover:underline">{{ $trx->reference }}</a>
                        @else
                            <span class="font-mono text-xs text-slate-600">{{ $trx->reference ?? '—' }}</span>
                        @endif
                        <div class="text-xs text-slate-400">{{ $methods[$trx->payment_method] ?? '' }}</div>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap font-semibold text-emerald-600">{{ $trx->type === 'income' ? $rp($trx->amount) : '' }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap font-semibold text-rose-600">{{ $trx->type === 'expense' ? $rp($trx->amount) : '' }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap text-slate-700">{{ $rp($balances[$trx->id] ?? 0) }}</td>
                    <td class="px-2 py-3 whitespace-nowrap text-right">
                        @if($trx->proof_image)
                            <a href="{{ asset('storage/' . $trx->proof_image) }}" target="_blank" class="text-xs font-semibold text-slate-500 hover:text-slate-800">Bukti</a>
                        @endif
                        @if(($userRole ?? null) === 'owner')
                            <form action="{{ route('transactions.destroy', $trx->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus transaksi ini dari buku kas secara permanen?')">
                                @csrf @method('DELETE')
                                <button class="ml-2 text-xs text-slate-300 hover:text-rose-600" title="Hapus">✕</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-4 py-14 text-center text-slate-400">Belum ada transaksi pada filter ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())<div class="p-4 border-t border-slate-50">{{ $transactions->links() }}</div>@endif
</div>
@endsection
