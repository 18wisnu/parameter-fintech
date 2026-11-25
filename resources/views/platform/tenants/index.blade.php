@extends('layouts.admin')

@section('content')
@include('partials.flash')
@include('platform._nav', ['title' => 'Usaha Terdaftar'])
@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $badge = ['unlimited' => 'bg-slate-100 text-slate-600', 'trial' => 'bg-sky-100 text-sky-700', 'active' => 'bg-emerald-100 text-emerald-700', 'expired' => 'bg-rose-100 text-rose-700', 'suspended' => 'bg-rose-100 text-rose-700'];
@endphp

<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
    @foreach([
        ['Usaha', $stats['tenants']],
        ['Berbayar aktif', $stats['paying']],
        ['Masa coba', $stats['trial']],
        ['Terkunci', $stats['locked']],
        ['Pendapatan rutin / bln', $rp($stats['mrr'])],
    ] as [$l, $v])
        <div class="bg-white rounded-2xl border border-slate-100 p-4">
            <div class="text-xs text-slate-400">{{ $l }}</div>
            <div class="text-xl font-bold text-slate-800">{{ $v }}</div>
        </div>
    @endforeach
</div>

@if($stats['pending'])
    <a href="{{ route('platform.payments.index') }}" class="block mb-5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 font-semibold">
        {{ $stats['pending'] }} pembayaran menunggu konfirmasi →
    </a>
@endif

<form method="GET" class="mb-3 flex gap-2 text-sm">
    <select name="status" onchange="this.form.submit()" class="py-2 rounded-xl border-slate-200 text-sm">
        <option value="">Semua status</option>
        @foreach($labels as $k => $l)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach
    </select>
    <span class="self-center text-slate-400">Diterima bulan ini: <b class="text-slate-700">{{ $rp($stats['revenue_month']) }}</b></span>
</form>

<div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-xs text-slate-500 text-left">
            <tr><th class="px-4 py-3">Usaha</th><th class="px-4 py-3">Pemilik</th><th class="px-4 py-3">Paket</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Berakhir</th><th class="px-4 py-3 text-right">Pelanggan</th><th class="px-4 py-3 text-right">Staf</th><th class="px-4 py-3">Terakhir aktif</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($rows as $r)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3"><a href="{{ route('platform.tenants.show', $r->business) }}" class="font-semibold text-sky-700 hover:underline">{{ $r->business->name }}</a></td>
                    <td class="px-4 py-3 text-slate-600">{{ $r->business->owner->name ?? '-' }}<div class="text-xs text-slate-400">{{ $r->business->owner->email ?? '' }}</div></td>
                    <td class="px-4 py-3">{{ $r->business->plan->name ?? '-' }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $badge[$r->status] }}">{{ $labels[$r->status] }}</span></td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        {{ $r->business->accessEndsAt()?->format('d/m/Y') ?? '—' }}
                        @if($r->days_left !== null && $r->days_left >= 0 && $r->days_left <= 7)<span class="text-xs text-amber-600">({{ $r->days_left }} hr)</span>@endif
                    </td>
                    <td class="px-4 py-3 text-right">{{ number_format($r->customers, 0, ',', '.') }}{{ $r->business->customerLimit() ? ' / ' . $r->business->customerLimit() : '' }}</td>
                    <td class="px-4 py-3 text-right">{{ $r->staff }}</td>
                    <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $r->last_active?->diffForHumans() ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">Tidak ada usaha.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
