@extends('layouts.admin')

@section('content')
@include('partials.flash')
@include('platform._nav', ['title' => 'Pelanggan NetBill'])
@php
    $badge = ['unlimited' => 'bg-slate-100 text-slate-600', 'trial' => 'bg-sky-50 text-sky-700', 'active' => 'bg-emerald-50 text-emerald-700', 'expired' => 'bg-rose-50 text-rose-700', 'suspended' => 'bg-rose-50 text-rose-700'];
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    @foreach([['Pemilik usaha', $summary['owners']], ['Usaha', $summary['businesses']], ['Berlangganan berbayar', $summary['paying']], ['Baru bulan ini', $summary['new_month']]] as [$l, $v])
        <div class="bg-white rounded-2xl ring-1 ring-slate-200/70 p-4">
            <div class="text-xs text-slate-500">{{ $l }}</div>
            <div class="text-2xl font-bold text-slate-900">{{ $v }}</div>
        </div>
    @endforeach
</div>

<form method="GET" class="flex flex-wrap gap-2 mb-4">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, atau nama usaha" class="flex-1 min-w-[200px] py-2 px-3 rounded-xl border-slate-200 text-sm">
    <select name="status" onchange="this.form.submit()" class="py-2 rounded-xl border-slate-200 text-sm">
        <option value="">Semua status</option>
        @foreach($labels as $k => $l)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach
    </select>
    <button class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold">Cari</button>
    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-4 py-2 rounded-xl ring-1 ring-slate-200 bg-white text-sm font-semibold text-slate-700">Unduh CSV</a>
</form>

<div class="bg-white rounded-2xl ring-1 ring-slate-200/70 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-xs text-slate-500 text-left">
            <tr>
                <th class="px-4 py-3 font-semibold">Pelanggan</th>
                <th class="px-4 py-3 font-semibold">Usaha & langganan</th>
                <th class="px-4 py-3 font-semibold text-right">Pelanggan internet</th>
                <th class="px-4 py-3 font-semibold">Terdaftar</th>
                <th class="px-4 py-3 font-semibold">Terakhir aktif</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 align-top">
            @forelse($rows as $r)
                <tr class="hover:bg-slate-50/60">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($r->user->avatar)
                                <img src="{{ $r->user->avatar }}" alt="" class="w-9 h-9 rounded-full object-cover">
                            @else
                                <div class="w-9 h-9 rounded-full bg-slate-200 text-slate-600 grid place-items-center text-xs font-bold">{{ mb_strtoupper(mb_substr($r->user->name, 0, 1)) }}</div>
                            @endif
                            <div class="min-w-0">
                                <div class="font-semibold text-slate-900">{{ $r->user->name }}
                                    @if($r->user->is_superadmin)<span class="ml-1 px-1.5 py-0.5 rounded bg-slate-900 text-white text-[10px] font-bold">Pengelola</span>@endif
                                </div>
                                <a href="mailto:{{ $r->user->email }}" class="text-xs text-slate-500 hover:underline">{{ $r->user->email }}</a>
                                <div class="text-[11px] text-slate-400">{{ $r->user->google_id ? 'Login Google' : 'Login email' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="space-y-1.5">
                            @foreach($r->businesses as $b)
                                @php $s = $b->subscriptionStatus(); @endphp
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('platform.tenants.show', $b) }}" class="font-medium text-sky-700 hover:underline">{{ $b->name }}</a>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $badge[$s] }}">{{ $labels[$s] }}</span>
                                    <span class="text-xs text-slate-400">{{ $b->plan->name ?? 'Tanpa paket' }}@if($b->accessEndsAt()) · s/d {{ $b->accessEndsAt()->format('d/m/Y') }}@endif</span>
                                </div>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-700">{{ number_format($r->customers, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $r->user->created_at?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $r->last_seen?->locale('id')->diffForHumans() ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">Belum ada pelanggan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<p class="text-xs text-slate-400 mt-3">Pelanggan NetBill = pemilik usaha. Data pelanggan internet milik tiap usaha bersifat rahasia usaha tersebut; di sini hanya jumlahnya.</p>
@endsection
