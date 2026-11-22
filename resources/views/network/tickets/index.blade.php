@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => 'Tiket Gangguan',
    'subtitle' => 'Keluhan dan gangguan pelanggan.',
    'actions' => [['+ Buat Tiket', route('network.tickets.create'), true]],
])

<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
    @foreach(['status' => $statuses, 'priority' => $priorities, 'category' => $categories] as $field => $opts)
    <select name="{{ $field }}" class="px-3 py-2 rounded-xl border-slate-200 text-sm">
        <option value="">{{ ['status' => 'Semua status', 'priority' => 'Semua prioritas', 'category' => 'Semua kategori'][$field] }}</option>
        @foreach($opts as $k => $v)<option value="{{ $k }}" @selected(request($field) === $k)>{{ $v }}</option>@endforeach
    </select>
    @endforeach
    <button class="bg-slate-900 hover:bg-slate-700 text-white rounded-xl text-xs font-black uppercase tracking-widest py-2">Filter</button>
</form>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                <tr>
                    <th class="px-4 py-3 text-left">Tiket</th>
                    <th class="px-4 py-3 text-left">Pelanggan</th>
                    <th class="px-4 py-3 text-left">Kategori</th>
                    <th class="px-4 py-3 text-center">Prioritas</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-left">Petugas</th>
                    <th class="px-4 py-3 text-left">Dibuat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($page['items'] as $t)
                <tr class="hover:bg-slate-50 cursor-pointer" onclick="window.location='{{ route('network.tickets.show', $t['id']) }}'">
                    <td class="px-4 py-3">
                        <a href="{{ route('network.tickets.show', $t['id']) }}" class="font-bold text-slate-800 hover:text-sky-700">{{ $t['subject'] ?? '-' }}</a>
                        <div class="text-[10px] font-mono text-slate-400">{{ $t['ticket_code'] ?? '' }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $t['customer_name'] ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ $categories[$t['category'] ?? ''] ?? ($t['category'] ?? '-') }}</td>
                    <td class="px-4 py-3 text-center">@include('network.partials.badge', ['value' => $t['priority'] ?? null, 'labels' => $priorities])</td>
                    <td class="px-4 py-3 text-center">@include('network.partials.badge', ['value' => $t['status'] ?? null, 'labels' => $statuses])</td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ $t['assigned_name'] ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $t['created_at'] ?? '' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400 text-sm">Tidak ada tiket.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('network.partials.pagination')
</div>
@endsection
