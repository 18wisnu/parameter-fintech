@extends('layouts.admin')

@section('content')
@include('partials.flash')
@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $input = 'w-full py-2 px-3 rounded-xl border-slate-200 text-sm';
    $tone = ['unlimited' => 'emerald', 'trial' => 'sky', 'active' => 'emerald', 'expired' => 'rose', 'suspended' => 'rose'][$status];
    $end = $business->accessEndsAt();
    $limit = $business->customerLimit();
    $pending = $payments->firstWhere('status', 'pending');
@endphp
{{-- kelas dinamis: bg-emerald-50 border-emerald-200 text-emerald-700 bg-sky-50 border-sky-200 text-sky-700 bg-rose-50 border-rose-200 text-rose-700 --}}

<div class="mb-5">
    <h2 class="text-2xl font-bold text-slate-800">Langganan NetBill</h2>
    <p class="text-sm text-slate-500">Status langganan usaha <b>{{ $business->name }}</b> dan perpanjangannya.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl border p-5 bg-{{ $tone }}-50 border-{{ $tone }}-200">
        <div class="text-xs font-semibold uppercase tracking-wider text-{{ $tone }}-700">Status</div>
        <div class="text-2xl font-bold text-slate-800 mt-1">{{ \App\Models\Business::SUBSCRIPTION_LABELS[$status] }}</div>
        <div class="text-sm text-slate-600 mt-1">
            @if($status === 'suspended')
                {{ $business->suspend_reason }}
            @elseif($end)
                {{ $status === 'expired' ? 'Berakhir' : 'Berlaku sampai' }} {{ $end->locale('id')->translatedFormat('d F Y') }}
                @if($business->daysLeft() >= 0) ({{ $business->daysLeft() }} hari lagi) @endif
            @else
                Tidak ada tanggal berakhir.
            @endif
        </div>
    </div>
    <div class="rounded-2xl border border-slate-100 bg-white p-5">
        <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Paket</div>
        <div class="text-2xl font-bold text-slate-800 mt-1">{{ $business->plan->name ?? '-' }}</div>
        <div class="text-sm text-slate-500 mt-1">{{ $business->plan ? $rp($business->plan->price) . ' / bulan' : 'Belum memilih paket' }}</div>
    </div>
    <div class="rounded-2xl border border-slate-100 bg-white p-5">
        <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pelanggan</div>
        <div class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($customerCount, 0, ',', '.') }}{{ $limit ? ' / ' . number_format($limit, 0, ',', '.') : '' }}</div>
        @if($limit)
            <div class="h-2 rounded-full bg-slate-100 mt-2 overflow-hidden"><div class="h-2 {{ $customerCount >= $limit ? 'bg-rose-500' : 'bg-sky-500' }}" style="width: {{ min(100, round($customerCount / $limit * 100)) }}%"></div></div>
        @else
            <div class="text-sm text-slate-500 mt-1">Tanpa batas</div>
        @endif
    </div>
</div>

@if($pending)
    <div class="mb-6 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3">
        Pembayaran {{ $rp($pending->amount) }} ({{ $pending->plan->name ?? '-' }}, {{ $pending->months }} bulan) sedang menunggu konfirmasi pengelola NetBill.
    </div>
@endif

@if($isOwner && !$pending)
<div class="bg-white rounded-2xl border border-slate-100 p-5 mb-6" x-data="{ plan: '{{ old('plan_id', $business->plan_id ?? $plans->first()?->id) }}', months: '{{ old('months', 1) }}', prices: @js($plans->pluck('price', 'id')) }">
    <h3 class="font-bold text-slate-800 mb-1">Perpanjang / ganti paket</h3>
    <p class="text-sm text-slate-500 mb-4">Pilih paket, transfer sesuai total, lalu unggah bukti transfer.</p>

    <form method="POST" action="{{ route('subscription.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @foreach($plans as $p)
                <label class="block rounded-2xl border-2 p-4 cursor-pointer" :class="plan == '{{ $p->id }}' ? 'border-sky-500 bg-sky-50' : 'border-slate-100'">
                    <input type="radio" name="plan_id" value="{{ $p->id }}" x-model="plan" class="sr-only">
                    <div class="font-bold text-slate-800">{{ $p->name }}</div>
                    <div class="text-lg font-bold text-sky-700">{{ $rp($p->price) }}<span class="text-xs font-normal text-slate-500"> / bulan</span></div>
                    <div class="text-xs text-slate-500 mt-1">{{ $p->limitLabel() }}</div>
                    @if($p->description)<div class="text-xs text-slate-400 mt-1">{{ $p->description }}</div>@endif
                </label>
            @endforeach
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Lama langganan</label>
                <select name="months" x-model="months" class="{{ $input }}">
                    @foreach($months as $m)<option value="{{ $m }}">{{ $m }} bulan</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Bukti transfer</label>
                <input type="file" name="proof_image" accept="image/*" required class="w-full text-sm">
            </div>
            <div class="text-right">
                <div class="text-xs text-slate-500">Total bayar</div>
                <div class="text-2xl font-bold text-slate-800" x-text="'Rp ' + Math.round((prices[plan] || 0) * months).toLocaleString('id-ID')"></div>
            </div>
        </div>
        <input type="text" name="notes" value="{{ old('notes') }}" maxlength="255" placeholder="Catatan (opsional), mis. transfer dari rekening a.n. ..." class="{{ $input }}">

        @if($instructions)
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-4 text-sm text-slate-700 whitespace-pre-line">{{ $instructions }}</div>
        @endif

        <button class="px-5 py-2.5 rounded-xl bg-sky-600 text-white text-sm font-bold hover:bg-sky-700">Kirim bukti pembayaran</button>
    </form>
</div>
@elseif(!$isOwner)
    <p class="mb-6 text-sm text-slate-500">Perpanjangan langganan dilakukan oleh pemilik usaha.</p>
@endif

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="px-5 py-3 font-bold text-slate-800 border-b border-slate-100">Riwayat pembayaran</div>
    <div class="divide-y divide-slate-100">
        @forelse($payments as $pay)
            <div class="px-5 py-3 flex flex-wrap justify-between gap-2 text-sm">
                <div>
                    <div class="font-semibold text-slate-700">{{ $pay->plan->name ?? '-' }} · {{ $pay->months }} bulan · {{ $rp($pay->amount) }}</div>
                    <div class="text-xs text-slate-400">{{ $pay->created_at->format('d/m/Y H:i') }}
                        @if($pay->period_end) · berlaku {{ $pay->period_start->format('d/m/Y') }} – {{ $pay->period_end->format('d/m/Y') }} @endif
                        @if($pay->admin_notes) · {{ $pay->admin_notes }} @endif
                    </div>
                </div>
                <span class="text-xs font-bold {{ ['pending' => 'text-amber-600', 'confirmed' => 'text-emerald-600', 'rejected' => 'text-rose-600'][$pay->status] }}">{{ \App\Models\SubscriptionPayment::STATUSES[$pay->status] }}</span>
            </div>
        @empty
            <div class="px-5 py-8 text-center text-sm text-slate-400">Belum ada pembayaran.</div>
        @endforelse
    </div>
</div>
@endsection
