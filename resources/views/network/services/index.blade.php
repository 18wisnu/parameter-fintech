@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => 'Layanan Internet',
    'subtitle' => 'Status layanan PPPoE, hotspot, DHCP & static per pelanggan, langsung dari jaringan.',
    'actions' => [['Pengguna Online', route('network.online.index'), true]],
])

<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari username / kode / nama" class="px-3 py-2 rounded-xl border-slate-200 text-sm sm:col-span-1">
    <select name="status" class="px-3 py-2 rounded-xl border-slate-200 text-sm">
        <option value="">Semua status</option>
        @foreach($statuses as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach
    </select>
    <select name="service_type" class="px-3 py-2 rounded-xl border-slate-200 text-sm">
        <option value="">Semua jenis</option>
        @foreach($types as $k => $v)<option value="{{ $k }}" @selected(request('service_type') === $k)>{{ $v }}</option>@endforeach
    </select>
    <button class="bg-slate-900 hover:bg-slate-700 text-white rounded-xl text-xs font-black uppercase tracking-widest py-2">Filter</button>
</form>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                <tr>
                    <th class="px-4 py-3 text-left">Pelanggan</th>
                    <th class="px-4 py-3 text-left">Username</th>
                    <th class="px-4 py-3 text-left">Jenis</th>
                    <th class="px-4 py-3 text-left">Paket</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-left">Aktif s/d</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($page['items'] as $s)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        @if(!empty($s['customer_code']) && isset($localCustomers[$s['customer_code']]))
                            <a href="{{ route('customers.show', $localCustomers[$s['customer_code']]) }}" class="font-bold text-sky-700 hover:underline">{{ $s['customer_name'] ?? '-' }}</a>
                        @else
                            <span class="font-bold text-slate-700">{{ $s['customer_name'] ?? '-' }}</span>
                        @endif
                        <div class="text-[10px] text-slate-400">{{ $s['customer_code'] ?? '' }}</div>
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-600">
                        @if(!empty($s['is_online']))<span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1" title="Online"></span>@endif
                        {{ $s['username'] ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">@include('partials.type_badge', ['type' => $s['service_type'] ?? null]) <span class="ml-1">{{ ($s['billing_type'] ?? '') === 'postpaid' ? 'Pascabayar' : 'Prabayar' }}</span></td>
                    <td class="px-4 py-3 text-slate-600">{{ $s['product']['name'] ?? '-' }}
                        <div class="text-[10px] text-slate-400">{{ $s['product']['bandwidth'] ?? '' }}{{ isset($s['product']['price']) ? ' · Rp ' . number_format($s['product']['price'], 0, ',', '.') : '' }}</div></td>
                    <td class="px-4 py-3 text-center">@include('network.partials.badge', ['value' => $s['status'] ?? null, 'labels' => $statuses])</td>
                    <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $s['expiry_date'] ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400 text-sm">Tidak ada layanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('network.partials.pagination')
</div>
@endsection
