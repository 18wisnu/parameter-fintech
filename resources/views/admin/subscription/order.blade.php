@extends('layouts.admin')

@section('content')
@include('partials.flash')
@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $steps = [
        ['Pesanan dibuat', true],
        ['Pembayaran', in_array($order->status, ['pending', 'confirmed'], true)],
        ['Dikonfirmasi', $order->status === 'confirmed'],
    ];
    $canPay = $isOwner && $order->status === 'awaiting_payment' && !$order->isExpired();
@endphp

<a href="{{ route('subscription.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-800 mb-4">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Langganan
</a>

<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pesanan {{ $order->reference }}</h1>
        <p class="text-sm text-slate-500 mt-0.5">Dibuat {{ $order->created_at->locale('id')->translatedFormat('d M Y, H:i') }} oleh {{ $order->submitter->name ?? '-' }}</p>
    </div>
    @include('admin.subscription._status', ['s' => $order->isExpired() ? 'cancelled' : $order->status])
</div>

@unless(in_array($order->status, ['rejected', 'cancelled'], true))
<ol class="grid grid-cols-3 gap-2 mb-6">
    @foreach($steps as $i => [$label, $done])
        <li class="flex items-center gap-2 text-xs sm:text-sm {{ $done ? 'text-slate-900 font-semibold' : 'text-slate-400' }}">
            <span class="w-6 h-6 shrink-0 rounded-full grid place-items-center text-[11px] font-bold {{ $done ? 'bg-theme text-white' : 'bg-slate-200 text-slate-500' }}">{{ $done ? '✓' : $i + 1 }}</span>
            {{ $label }}
        </li>
    @endforeach
</ol>
@endunless

<div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6 items-start">
    <div class="space-y-6">
        @if($canPay)
            <section class="bg-white rounded-3xl ring-1 ring-slate-200/70 p-5 sm:p-6">
                <h2 class="font-bold text-slate-900">Lakukan pembayaran</h2>
                <p class="text-sm text-slate-500 mt-1">Transfer tepat <b class="text-slate-900">{{ $rp($order->amount) }}</b> sebelum {{ $order->expires_at->locale('id')->translatedFormat('d M Y, H:i') }}. Cantumkan <b>{{ $order->reference }}</b> di berita transfer.</p>

                @if($instructions)
                    <div class="mt-4 rounded-2xl bg-slate-50 ring-1 ring-slate-200/70 p-4 text-sm text-slate-700 whitespace-pre-line">{{ $instructions }}</div>
                @else
                    <div class="mt-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200 p-4 text-sm text-amber-800">Info rekening belum diisi pengelola NetBill. Hubungi pengelola untuk nomor rekening.</div>
                @endif

                <form method="POST" action="{{ route('subscription.proof', $order) }}" enctype="multipart/form-data" class="mt-5 space-y-3">
                    @csrf
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">Bukti transfer</span>
                        <input type="file" name="proof_image" accept="image/*" required class="mt-1.5 block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-slate-900 file:text-white file:font-semibold">
                    </label>
                    <input type="text" name="notes" maxlength="255" placeholder="Catatan (opsional), mis. transfer dari rekening a.n. ..." class="w-full py-2.5 px-3 rounded-xl border-slate-200 text-sm">
                    <button class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-theme text-white font-bold hover:opacity-90">Kirim bukti pembayaran</button>
                </form>
            </section>
            <form method="POST" action="{{ route('subscription.cancel', $order) }}" onsubmit="return confirm('Batalkan pesanan ini?')">
                @csrf
                <button class="text-sm font-semibold text-rose-600 hover:underline">Batalkan pesanan</button>
            </form>
        @elseif($order->status === 'pending')
            <section class="bg-sky-50 ring-1 ring-sky-200 rounded-3xl p-6">
                <h2 class="font-bold text-sky-900">Pembayaran sedang diperiksa</h2>
                <p class="text-sm text-sky-800 mt-1">Bukti transfer diterima {{ $order->paid_at?->locale('id')->translatedFormat('d M Y, H:i') }}. Langganan aktif otomatis setelah dikonfirmasi pengelola NetBill.</p>
            </section>
        @elseif($order->status === 'confirmed')
            <section class="bg-emerald-50 ring-1 ring-emerald-200 rounded-3xl p-6">
                <h2 class="font-bold text-emerald-900">Pembayaran lunas</h2>
                <p class="text-sm text-emerald-800 mt-1">Langganan aktif {{ $order->period_start?->locale('id')->translatedFormat('d M Y') }} – {{ $order->period_end?->locale('id')->translatedFormat('d M Y') }}. Terima kasih!</p>
            </section>
        @else
            <section class="bg-slate-100 rounded-3xl p-6">
                <h2 class="font-bold text-slate-800">{{ $order->status === 'rejected' ? 'Pembayaran ditolak' : 'Pesanan dibatalkan' }}</h2>
                @if($order->admin_notes)<p class="text-sm text-slate-600 mt-1">{{ $order->admin_notes }}</p>@endif
                @if($order->isExpired())<p class="text-sm text-slate-600 mt-1">Batas waktu pembayaran sudah lewat.</p>@endif
                @if($isOwner)<a href="{{ route('subscription.checkout', ['plan' => $order->plan_id, 'months' => $order->months]) }}" class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-bold">Buat pesanan baru</a>@endif
            </section>
        @endif

        @if($order->proof_image)
            <a href="{{ asset('storage/' . $order->proof_image) }}" target="_blank" class="inline-flex items-center gap-1 text-sm font-semibold text-theme hover:underline">Lihat bukti transfer</a>
        @endif
    </div>

    <aside class="bg-white rounded-3xl ring-1 ring-slate-200/70 p-5 sm:p-6">
        <h2 class="font-bold text-slate-900 mb-4">Rincian</h2>
        <dl class="space-y-2.5 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">No. pesanan</dt><dd class="font-mono text-slate-800">{{ $order->reference }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Usaha</dt><dd class="font-medium text-slate-800 text-right">{{ $business->name }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Paket</dt><dd class="text-slate-800">{{ $order->plan->name ?? '-' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Durasi</dt><dd class="text-slate-800">{{ $order->months }} bulan</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Metode</dt><dd class="text-slate-800 text-right">{{ \App\Models\SubscriptionPayment::METHODS[$order->method] ?? 'Manual' }}</dd></div>
        </dl>
        <div class="border-t border-dashed border-slate-200 my-4"></div>
        <div class="flex items-end justify-between">
            <span class="text-sm text-slate-500">Total</span>
            <span class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $rp($order->amount) }}</span>
        </div>
    </aside>
</div>
@endsection
