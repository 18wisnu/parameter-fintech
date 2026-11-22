@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => 'Reseller & Outlet',
    'subtitle' => 'Mitra penjual voucher beserta saldo dan komisinya.',
])

<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / kode" class="px-3 py-2 rounded-xl border-slate-200 text-sm">
    <select name="type" class="px-3 py-2 rounded-xl border-slate-200 text-sm">
        <option value="">Semua jenis</option>
        @foreach($types as $k => $v)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $v }}</option>@endforeach
    </select>
    <button class="bg-slate-900 hover:bg-slate-700 text-white rounded-xl text-xs font-black uppercase tracking-widest py-2">Filter</button>
</form>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                <tr>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Jenis</th>
                    <th class="px-4 py-3 text-left">Sistem Saldo</th>
                    <th class="px-4 py-3 text-right">Saldo</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($page['items'] as $e)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-bold text-slate-800">{{ $e['name'] ?? '-' }}<div class="text-[10px] font-mono font-normal text-slate-400">{{ $e['code'] ?? '' }}</div></td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ $types[$e['type'] ?? ''] ?? ($e['type'] ?? '-') }}</td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ ['prepaid' => 'Deposit', 'commission' => 'Komisi', 'none' => '-'][$e['balance_type'] ?? 'none'] ?? $e['balance_type'] }}
                        @if(!empty($e['commission_type'])) · {{ $e['commission_type'] }} {{ $e['commission_percentage'] ? $e['commission_percentage'] . '%' : '' }}@endif
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-slate-700 whitespace-nowrap">Rp {{ number_format($e['balance'] ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-center">@include('network.partials.badge', ['value' => $e['status'] ?? null, 'labels' => ['active' => 'Aktif', 'inactive' => 'Nonaktif']])</td>
                    <td class="px-4 py-3 text-right">
                        @if(($e['type'] ?? '') !== 'isp')<a href="{{ route('network.entities.show', $e['id']) }}" class="text-sky-600 font-bold text-xs hover:underline">Statistik</a>@endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400 text-sm">Belum ada reseller/outlet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('network.partials.pagination')
</div>
@endsection
