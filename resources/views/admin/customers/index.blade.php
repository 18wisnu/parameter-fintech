@extends('layouts.admin')

@section('content')
@include('partials.flash')

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Pelanggan</h2>
        <p class="text-sm text-slate-500">{{ number_format($counts['all']) }} pelanggan terdaftar</p>
    </div>

    <div class="flex items-center gap-2">
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600 hover:bg-slate-50">Lainnya ▾</button>
            <div x-show="open" x-cloak x-transition class="absolute right-0 mt-2 w-52 bg-white border border-slate-100 rounded-xl shadow-lg py-1 z-20 text-sm">
                <a href="{{ route('customers.export') }}" class="block px-4 py-2 hover:bg-slate-50">Export ke Excel</a>
                <button type="button" @click="open = false; $refs.importModal.showModal()" class="w-full text-left px-4 py-2 hover:bg-slate-50">Import dari Excel</button>
                @if($network && ($userRole ?? null) === 'owner')
                <form method="POST" action="{{ route('admin.mixradius.sync') }}">
                    @csrf
                    <button class="w-full text-left px-4 py-2 hover:bg-slate-50">Sinkronkan dari server</button>
                </form>
                @endif
            </div>

            <dialog x-ref="importModal" class="rounded-2xl p-0 w-full max-w-md backdrop:bg-slate-900/50">
                <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <h3 class="text-lg font-bold text-slate-800 mb-1">Import Pelanggan</h3>
                    <p class="text-sm text-slate-500 mb-4">File Excel (.xlsx) dengan kolom: <code class="text-slate-700">nama, tipe, hp, alamat, id_pelanggan</code></p>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv" class="block w-full text-sm border border-slate-200 rounded-xl file:mr-3 file:py-2 file:px-4 file:border-0 file:bg-slate-100 file:font-semibold">
                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" onclick="this.closest('dialog').close()" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600">Batal</button>
                        <button class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">Upload</button>
                    </div>
                </form>
            </dialog>
        </div>

        <a href="{{ route('customers.create') }}" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">+ Pelanggan</a>
    </div>
</div>

{{-- Filter --}}
<form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
    @php $statusTabs = ['' => ['Semua', $counts['all']], 'active' => ['Aktif', $counts['active']], 'isolated' => ['Isolir', $counts['isolated']], 'inactive' => ['Nonaktif', $counts['inactive']]]; @endphp
    <div class="flex bg-white border border-slate-200 rounded-xl p-1 overflow-x-auto">
        @foreach($statusTabs as $key => [$label, $n])
            <a href="{{ route('customers.index', array_filter(['status' => $key, 'search' => request('search'), 'type' => request('type')])) }}"
               class="px-3 py-1.5 rounded-lg text-sm whitespace-nowrap {{ (string) request('status') === (string) $key ? 'bg-slate-800 text-white font-semibold' : 'text-slate-500 hover:text-slate-800' }}">
                {{ $label }} <span class="opacity-60">{{ $n }}</span>
            </a>
        @endforeach
    </div>
    <input type="hidden" name="status" value="{{ request('status') }}">
    <select name="type" onchange="this.form.submit()" class="py-2 rounded-xl border-slate-200 text-sm text-slate-600">
        <option value="">Semua layanan</option>
        @foreach(\App\Models\Customer::TYPES as $k => $v)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $v }}</option>@endforeach
    </select>
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode, HP, username…" class="flex-1 min-w-[200px] py-2 px-3 rounded-xl border-slate-200 text-sm">
</form>

@php
    $statusTone = ['active' => 'bg-emerald-50 text-emerald-700', 'isolated' => 'bg-amber-50 text-amber-700', 'inactive' => 'bg-slate-100 text-slate-500'];
@endphp

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3 font-semibold">Pelanggan</th>
                    <th class="px-4 py-3 font-semibold">Kontak</th>
                    <th class="px-4 py-3 font-semibold">Layanan</th>
                    <th class="px-4 py-3 font-semibold">Paket</th>
                    <th class="px-4 py-3 font-semibold">Jatuh Tempo</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($customers as $c)
                @php $st = $c->displayStatus(); $due = $c->due_date ? \Carbon\Carbon::parse($c->due_date) : null; @endphp
                <tr class="hover:bg-slate-50 cursor-pointer" onclick="window.location='{{ route('customers.show', $c) }}'">
                    <td class="px-4 py-3">
                        <a href="{{ route('customers.show', $c) }}" class="font-semibold text-slate-800 hover:text-sky-700">{{ $c->name }}</a>
                        <div class="text-xs text-slate-400 font-mono">{{ $c->displayCode() ?? '—' }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        {{ $c->phone ?: '—' }}
                        @if($c->email)<div class="text-xs text-slate-400">{{ $c->email }}</div>@endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-slate-700">{{ \App\Models\Customer::TYPES[$c->type] ?? strtoupper((string) $c->type) }}</span>
                        @if($c->mixradius_username)<div class="text-xs text-slate-400 font-mono">{{ $c->mixradius_username }}</div>@endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-slate-700">{{ $c->product_name ?: '—' }}</span>
                        <div class="text-xs text-slate-400">{{ $c->bandwidth ? $c->bandwidth . ' · ' : '' }}Rp {{ number_format($c->package_fee, 0, ',', '.') }}</div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap {{ $due && $due->isPast() && $st !== 'inactive' ? 'text-rose-600 font-semibold' : 'text-slate-600' }}">
                        {{ $due ? $due->locale('id')->translatedFormat('d M Y') : '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-md text-xs font-semibold {{ $statusTone[$st] }}">{{ \App\Models\Customer::STATUSES[$st] }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-14 text-center text-slate-400">
                    {{ request()->hasAny(['search', 'status', 'type']) ? 'Tidak ada pelanggan yang cocok dengan filter.' : 'Belum ada pelanggan.' }}
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())<div class="p-4 border-t border-slate-50">{{ $customers->links() }}</div>@endif
</div>
@endsection
