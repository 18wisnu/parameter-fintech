@extends('layouts.admin')

@section('content')
@include('partials.flash')
@php $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.'); @endphp

<a href="{{ route('subscription.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-800 mb-4">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Langganan
</a>
<h1 class="text-2xl font-bold tracking-tight text-slate-900 mb-6">Checkout</h1>

<form method="POST" action="{{ route('subscription.place') }}"
      x-data="{
          plan: '{{ old('plan_id', $selected?->id) }}',
          months: {{ (int) old('months', $months) }},
          method: 'manual_transfer',
          plans: @js($plans->mapWithKeys(fn ($p) => [$p->id => ['name' => $p->name, 'price' => (float) $p->price, 'limit' => $p->max_customers]])),
          start: new Date('{{ $startDate->toDateString() }}T00:00:00'),
          rp(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID') },
          get price() { return this.plans[this.plan]?.price ?? 0 },
          get total() { return this.price * this.months },
          get end() { const d = new Date(this.start); d.setMonth(d.getMonth() + Number(this.months)); d.setDate(d.getDate() - 1); return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) },
      }"
      class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6 items-start">
    @csrf

    <div class="space-y-6">
        {{-- 1. Paket --}}
        <section class="bg-white rounded-3xl ring-1 ring-slate-200/70 p-5 sm:p-6">
            <h2 class="font-bold text-slate-900 mb-4"><span class="text-slate-400 mr-1">1</span> Paket</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach($plans as $p)
                    @php $tooSmall = $p->max_customers && $customerCount > $p->max_customers; @endphp
                    <label class="relative rounded-2xl p-4 cursor-pointer ring-1 transition {{ $tooSmall ? 'opacity-50 cursor-not-allowed' : '' }}"
                           :class="plan == '{{ $p->id }}' ? 'ring-2 ring-[color:var(--theme)] bg-theme-soft' : 'ring-slate-200 hover:ring-slate-300'">
                        <input type="radio" name="plan_id" value="{{ $p->id }}" x-model="plan" class="sr-only" @disabled($tooSmall)>
                        <div class="font-semibold text-slate-900">{{ $p->name }}</div>
                        <div class="text-lg font-bold text-slate-900 mt-1">{{ $rp($p->price) }}<span class="text-xs font-medium text-slate-500">/bln</span></div>
                        <div class="text-xs text-slate-500 mt-1">{{ $p->limitLabel() }}</div>
                    </label>
                @endforeach
            </div>
            <p class="text-xs text-slate-400 mt-3">Pelanggan Anda saat ini: {{ number_format($customerCount, 0, ',', '.') }}.</p>
        </section>

        {{-- 2. Durasi --}}
        <section class="bg-white rounded-3xl ring-1 ring-slate-200/70 p-5 sm:p-6">
            <h2 class="font-bold text-slate-900 mb-4"><span class="text-slate-400 mr-1">2</span> Lama langganan</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                @foreach($monthOptions as $m)
                    <label class="rounded-xl py-3 text-center cursor-pointer ring-1 text-sm font-semibold transition"
                           :class="months == {{ $m }} ? 'ring-2 ring-[color:var(--theme)] bg-theme-soft text-slate-900' : 'ring-slate-200 text-slate-600 hover:ring-slate-300'">
                        <input type="radio" name="months" value="{{ $m }}" x-model.number="months" class="sr-only">
                        {{ $m }} bulan
                    </label>
                @endforeach
            </div>
        </section>

        {{-- 3. Metode bayar --}}
        <section class="bg-white rounded-3xl ring-1 ring-slate-200/70 p-5 sm:p-6">
            <h2 class="font-bold text-slate-900 mb-4"><span class="text-slate-400 mr-1">3</span> Metode pembayaran</h2>
            <div class="space-y-2">
                <label class="flex items-center gap-3 rounded-2xl p-4 cursor-pointer ring-1 transition" :class="method === 'manual_transfer' ? 'ring-2 ring-[color:var(--theme)] bg-theme-soft' : 'ring-slate-200'">
                    <input type="radio" name="method" value="manual_transfer" x-model="method" class="text-slate-900">
                    <div class="flex-1">
                        <div class="font-semibold text-sm text-slate-900">Transfer bank</div>
                        <div class="text-xs text-slate-500">Transfer ke rekening NetBill, lalu unggah bukti. Dikonfirmasi maks. 1×24 jam.</div>
                    </div>
                </label>
                <label class="flex items-center gap-3 rounded-2xl p-4 ring-1 transition {{ $gatewayReady ? 'cursor-pointer' : 'opacity-60 cursor-not-allowed' }}" :class="method === 'gateway' ? 'ring-2 ring-[color:var(--theme)] bg-theme-soft' : 'ring-slate-200'">
                    <input type="radio" name="method" value="gateway" x-model="method" @disabled(!$gatewayReady)>
                    <div class="flex-1">
                        <div class="font-semibold text-sm text-slate-900">QRIS · Virtual Account · E-wallet</div>
                        <div class="text-xs text-slate-500">{{ $gatewayReady ? 'Bayar otomatis, langganan langsung aktif.' : 'Segera hadir.' }}</div>
                    </div>
                    @unless($gatewayReady)<span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">Segera</span>@endunless
                </label>
            </div>
        </section>
    </div>

    {{-- Ringkasan --}}
    <aside class="lg:sticky lg:top-6 bg-white rounded-3xl ring-1 ring-slate-200/70 p-5 sm:p-6">
        <h2 class="font-bold text-slate-900 mb-4">Ringkasan pesanan</h2>
        <dl class="space-y-2.5 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Usaha</dt><dd class="font-medium text-slate-800 text-right">{{ $business->name }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Paket</dt><dd class="font-medium text-slate-800" x-text="plans[plan]?.name ?? '-'"></dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Harga</dt><dd class="text-slate-800"><span x-text="rp(price)"></span> × <span x-text="months"></span> bln</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Masa aktif</dt><dd class="text-slate-800 text-right">{{ $startDate->locale('id')->translatedFormat('d M Y') }} – <span x-text="end"></span></dd></div>
        </dl>
        <div class="border-t border-dashed border-slate-200 my-4"></div>
        <div class="flex items-end justify-between">
            <span class="text-sm text-slate-500">Total bayar</span>
            <span class="text-2xl font-extrabold tracking-tight text-slate-900" x-text="rp(total)"></span>
        </div>
        <button class="mt-5 w-full py-3 rounded-2xl bg-theme text-white font-bold hover:opacity-90 active:scale-[0.99] transition">Buat pesanan</button>
        <p class="text-[11px] text-slate-400 text-center mt-3">Pesanan berlaku {{ config('netbill.order_expiry_hours') }} jam. Dengan melanjutkan, Anda menyetujui ketentuan layanan {{ config('netbill.name') }}.</p>
    </aside>
</form>
@endsection
