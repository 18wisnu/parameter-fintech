@extends('layouts.admin')

@section('content')
@include('partials.flash')
@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $tone = ['unlimited' => 'emerald', 'trial' => 'sky', 'active' => 'emerald', 'expired' => 'rose', 'suspended' => 'rose'][$status];
    $end = $business->accessEndsAt();
    $limit = $business->customerLimit();
@endphp
{{-- kelas dinamis: bg-emerald-50 text-emerald-700 ring-emerald-200 bg-sky-50 text-sky-700 ring-sky-200 bg-rose-50 text-rose-700 ring-rose-200 --}}

<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Langganan NetBill</h1>
    <p class="text-sm text-slate-500 mt-0.5">Status, paket & pembayaran untuk <b class="text-slate-700">{{ $business->name }}</b>.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
    <div class="rounded-2xl p-5 bg-{{ $tone }}-50 ring-1 ring-{{ $tone }}-200">
        <div class="text-xs font-semibold text-{{ $tone }}-700">Status</div>
        <div class="text-xl font-bold text-slate-900 mt-1">{{ \App\Models\Business::SUBSCRIPTION_LABELS[$status] }}</div>
        <div class="text-sm text-slate-600 mt-1">
            @if($status === 'suspended')
                {{ $business->suspend_reason }}
            @elseif($end)
                {{ $status === 'expired' ? 'Berakhir' : 'Sampai' }} {{ $end->locale('id')->translatedFormat('d M Y') }}
                @if($business->daysLeft() >= 0)<span class="text-slate-400">· {{ $business->daysLeft() }} hari lagi</span>@endif
            @else
                Tanpa tanggal berakhir
            @endif
        </div>
    </div>
    <div class="rounded-2xl p-5 bg-white ring-1 ring-slate-200/70">
        <div class="text-xs font-semibold text-slate-500">Paket</div>
        <div class="text-xl font-bold text-slate-900 mt-1">{{ $business->plan->name ?? 'Belum ada' }}</div>
        <div class="text-sm text-slate-500 mt-1">{{ $business->plan ? $rp($business->plan->price) . ' / bulan' : 'Pilih paket di bawah' }}</div>
    </div>
    <div class="rounded-2xl p-5 bg-white ring-1 ring-slate-200/70">
        <div class="text-xs font-semibold text-slate-500">Pelanggan</div>
        <div class="text-xl font-bold text-slate-900 mt-1">{{ number_format($customerCount, 0, ',', '.') }}<span class="text-slate-400 font-medium">{{ $limit ? ' / ' . number_format($limit, 0, ',', '.') : '' }}</span></div>
        @if($limit)
            <div class="h-1.5 rounded-full bg-slate-100 mt-3 overflow-hidden"><div class="h-full rounded-full {{ $customerCount >= $limit ? 'bg-rose-500' : 'bg-theme' }}" style="width: {{ min(100, round($customerCount / $limit * 100)) }}%"></div></div>
        @else
            <div class="text-sm text-slate-500 mt-1">Tanpa batas</div>
        @endif
    </div>
</div>

@if($openOrder)
    <a href="{{ route('subscription.order', $openOrder) }}" class="flex flex-wrap items-center justify-between gap-2 mb-6 rounded-2xl bg-amber-50 ring-1 ring-amber-200 px-5 py-4">
        <div class="text-sm text-amber-900">
            <b>Pesanan {{ $openOrder->reference }}</b> · {{ $openOrder->plan->name ?? '-' }} {{ $openOrder->months }} bulan · {{ $rp($openOrder->amount) }}
            <div class="text-amber-700">{{ \App\Models\SubscriptionPayment::STATUSES[$openOrder->status] }}</div>
        </div>
        <span class="text-sm font-bold text-amber-900">Lihat pesanan →</span>
    </a>
@endif

<h2 class="font-bold text-slate-900 mb-3">Pilih paket</h2>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    @foreach($plans as $p)
        @php $current = $business->plan_id === $p->id; $tooSmall = $p->max_customers && $customerCount > $p->max_customers; @endphp
        <div class="relative rounded-3xl bg-white p-6 flex flex-col {{ $current ? 'ring-2 ring-[color:var(--theme)]' : 'ring-1 ring-slate-200/70' }}">
            @if($current)<span class="absolute -top-2.5 left-6 px-2.5 py-0.5 rounded-full bg-theme text-white text-[11px] font-bold">Paket Anda</span>@endif
            <div class="font-bold text-slate-900">{{ $p->name }}</div>
            @if($p->description)<div class="text-sm text-slate-500 mt-0.5">{{ $p->description }}</div>@endif
            <div class="mt-4"><span class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $rp($p->price) }}</span><span class="text-sm text-slate-500"> /bulan</span></div>
            <ul class="mt-4 space-y-2 text-sm text-slate-600 flex-1">
                <li class="flex gap-2"><svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>{{ $p->limitLabel() }}</li>
                <li class="flex gap-2"><svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Semua fitur billing, keuangan & jaringan</li>
                <li class="flex gap-2"><svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Staf & kolektor tanpa batas</li>
            </ul>
            @if($isOwner)
                @if($tooSmall)
                    <div class="mt-5 text-center text-xs text-slate-400 py-2.5">Pelanggan Anda melebihi batas paket ini</div>
                @else
                    <a href="{{ route('subscription.checkout', ['plan' => $p->id]) }}" class="mt-5 block text-center py-2.5 rounded-xl text-sm font-bold {{ $current ? 'bg-theme text-white hover:opacity-90' : 'bg-slate-900 text-white hover:bg-slate-800' }}">
                        {{ $current ? 'Perpanjang' : 'Pilih ' . $p->name }}
                    </a>
                @endif
            @endif
        </div>
    @endforeach
</div>
@unless($isOwner)
    <p class="mb-8 text-sm text-slate-500">Perpanjangan langganan dilakukan oleh pemilik atau admin usaha.</p>
@endunless

<h2 class="font-bold text-slate-900 mb-3">Riwayat pesanan</h2>
<div class="bg-white rounded-2xl ring-1 ring-slate-200/70 divide-y divide-slate-100">
    @forelse($payments as $pay)
        <a href="{{ route('subscription.order', $pay) }}" class="px-5 py-3.5 flex flex-wrap items-center justify-between gap-2 text-sm hover:bg-slate-50">
            <div>
                <div class="font-semibold text-slate-800">{{ $pay->reference ?? '#' . $pay->id }} · {{ $pay->plan->name ?? '-' }} {{ $pay->months }} bulan</div>
                <div class="text-xs text-slate-400">{{ $pay->created_at->format('d/m/Y H:i') }}
                    @if($pay->period_end) · berlaku {{ $pay->period_start->format('d/m/Y') }} – {{ $pay->period_end->format('d/m/Y') }} @endif
                </div>
            </div>
            <div class="text-right">
                <div class="font-bold text-slate-900">{{ $rp($pay->amount) }}</div>
                @include('admin.subscription._status', ['s' => $pay->status])
            </div>
        </a>
    @empty
        <div class="px-5 py-10 text-center text-sm text-slate-400">Belum ada pesanan.</div>
    @endforelse
</div>
@endsection
