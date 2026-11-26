@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => 'Pemasangan Baru',
    'subtitle' => 'Antrian calon pelanggan yang menunggu dipasang teknisi.',
    'actions' => [['+ Ajukan Pemasangan', route('network.installations.create'), true]],
])

<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
    <select name="status" class="px-3 py-2 rounded-xl border-slate-200 text-sm">
        <option value="">Semua status</option>
        @foreach($statuses as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach
    </select>
    <select name="service_type" class="px-3 py-2 rounded-xl border-slate-200 text-sm">
        <option value="">Semua jenis</option>
        @foreach($types as $k => $v)<option value="{{ $k }}" @selected(request('service_type') === $k)>{{ $v }}</option>@endforeach
    </select>
    <button class="bg-slate-900 hover:bg-slate-700 text-white rounded-xl text-xs font-bold py-2">Filter</button>
</form>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs font-semibold text-slate-400">
                <tr>
                    <th class="px-4 py-3 text-left">Calon Pelanggan</th>
                    <th class="px-4 py-3 text-left">Paket</th>
                    <th class="px-4 py-3 text-left">Area</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-left">Jadwal</th>
                    <th class="px-4 py-3 text-left">Diajukan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($page['items'] as $i)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('network.installations.show', $i['id']) }}" class="font-bold text-slate-800 hover:text-sky-700">{{ $i['fullname'] ?? '-' }}</a>
                        <div class="text-[10px] text-slate-400">{{ $i['phone'] ?? '' }}{{ !empty($i['customer_code']) ? ' · ' . $i['customer_code'] : '' }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $i['product_name'] ?? '-' }}<div class="text-[10px] text-slate-400">{{ $types[$i['service_type'] ?? ''] ?? '' }}</div></td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ $i['area_name'] ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">@include('network.partials.badge', ['value' => $i['status'] ?? null, 'labels' => $statuses])</td>
                    <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $i['scheduled_install_at'] ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $i['created_at'] ?? '' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400 text-sm">Belum ada pengajuan pemasangan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('network.partials.pagination')
</div>
@endsection
