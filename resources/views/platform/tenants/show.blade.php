@extends('layouts.admin')

@section('content')
@include('partials.flash')
@include('platform._nav', ['title' => $business->name])
@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $input = 'w-full py-2 px-3 rounded-xl border-slate-200 text-sm';
    $btn = 'px-4 py-2 rounded-xl text-sm font-bold';
@endphp

<a href="{{ route('platform.tenants.index') }}" class="text-sm text-sky-600 hover:underline">← Semua usaha</a>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-3 mb-6">
    <div class="bg-white rounded-2xl border border-slate-100 p-5 text-sm space-y-1">
        <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Ringkasan</div>
        <div>Status: <b>{{ $labels[$status] }}</b> @if($business->suspend_reason)<span class="text-rose-600">— {{ $business->suspend_reason }}</span>@endif</div>
        <div>Paket: <b>{{ $business->plan->name ?? '-' }}</b></div>
        <div>Masa coba sampai: {{ $business->trial_ends_at?->format('d/m/Y') ?? '—' }}</div>
        <div>Langganan sampai: {{ $business->subscription_ends_at?->format('d/m/Y') ?? '—' }}</div>
        <div>Pelanggan: {{ number_format($customerCount, 0, ',', '.') }}{{ $business->customerLimit() ? ' / ' . $business->customerLimit() : '' }}</div>
        <div>Terdaftar: {{ $business->created_at?->format('d/m/Y') }}</div>
        <div>Server jaringan: {{ $business->hasMixradius() ? 'terhubung' : 'tidak' }}</div>
    </div>

    <form method="POST" action="{{ route('platform.tenants.extend', $business) }}" class="bg-white rounded-2xl border border-slate-100 p-5 space-y-3">
        @csrf
        <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Perpanjang (pembayaran di luar aplikasi)</div>
        <select name="plan_id" class="{{ $input }}">
            @foreach($plans as $p)<option value="{{ $p->id }}" @selected($business->plan_id === $p->id)>{{ $p->name }} — {{ $rp($p->price) }}/bln</option>@endforeach
        </select>
        <div class="grid grid-cols-2 gap-2">
            <input type="number" name="months" value="1" min="1" max="36" class="{{ $input }}" placeholder="Bulan">
            <input type="number" name="amount" min="0" class="{{ $input }}" placeholder="Nominal (kosong = harga paket)">
        </div>
        <input type="text" name="admin_notes" maxlength="255" class="{{ $input }}" placeholder="Catatan, mis. bayar tunai">
        <button class="{{ $btn }} bg-emerald-600 text-white hover:bg-emerald-700">Perpanjang</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 p-5 space-y-4">
        <form method="POST" action="{{ route('platform.tenants.update', $business) }}" class="space-y-2">
            @csrf @method('PUT')
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Atur manual</div>
            <select name="plan_id" class="{{ $input }}">
                <option value="">— tanpa paket —</option>
                @foreach($plans as $p)<option value="{{ $p->id }}" @selected($business->plan_id === $p->id)>{{ $p->name }}</option>@endforeach
            </select>
            <label class="block text-xs text-slate-500">Masa coba sampai <input type="date" name="trial_ends_at" value="{{ $business->trial_ends_at?->toDateString() }}" class="{{ $input }}"></label>
            <label class="block text-xs text-slate-500">Langganan sampai <input type="date" name="subscription_ends_at" value="{{ $business->subscription_ends_at?->toDateString() }}" class="{{ $input }}"></label>
            <p class="text-[11px] text-slate-400">Kosongkan kedua tanggal untuk akses tanpa batas waktu.</p>
            <button class="{{ $btn }} bg-slate-800 text-white">Simpan</button>
        </form>

        @if($business->suspended_at)
            <form method="POST" action="{{ route('platform.tenants.resume', $business) }}">
                @csrf
                <button class="{{ $btn }} bg-sky-600 text-white w-full">Buka penangguhan</button>
            </form>
        @else
            <form method="POST" action="{{ route('platform.tenants.suspend', $business) }}" class="space-y-2" onsubmit="return confirm('Tangguhkan usaha ini? Semua menu mereka akan terkunci.')">
                @csrf
                <input type="text" name="suspend_reason" required maxlength="255" class="{{ $input }}" placeholder="Alasan penangguhan">
                <button class="{{ $btn }} bg-rose-600 text-white w-full">Tangguhkan</button>
            </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <div class="px-5 py-3 font-bold text-slate-800 border-b border-slate-100">Anggota ({{ $members->count() }})</div>
        <div class="divide-y divide-slate-100 text-sm">
            @foreach($members as $m)
                <div class="px-5 py-2 flex justify-between"><span>{{ $m->name }} <span class="text-xs text-slate-400">{{ $m->email }}</span></span><span class="text-xs capitalize text-slate-500">{{ $m->pivot->role }}</span></div>
            @endforeach
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <div class="px-5 py-3 font-bold text-slate-800 border-b border-slate-100">Pembayaran langganan</div>
        <div class="divide-y divide-slate-100 text-sm">
            @forelse($payments as $pay)
                <div class="px-5 py-2">
                    <div class="flex justify-between"><span><span class="font-mono text-xs text-slate-400">{{ $pay->reference }}</span> {{ $pay->plan->name ?? '-' }} · {{ $pay->months }} bln · {{ $rp($pay->amount) }}</span><span class="text-xs font-bold">{{ \App\Models\SubscriptionPayment::STATUSES[$pay->status] }}</span></div>
                    <div class="text-xs text-slate-400">{{ $pay->created_at->format('d/m/Y') }}
                        @if($pay->period_end) · {{ $pay->period_start->format('d/m/Y') }}–{{ $pay->period_end->format('d/m/Y') }} @endif
                        @if($pay->confirmer) · oleh {{ $pay->confirmer->name }} @endif
                        @if($pay->admin_notes) · {{ $pay->admin_notes }} @endif
                    </div>
                </div>
            @empty
                <div class="px-5 py-6 text-center text-slate-400">Belum ada.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
