@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => 'Voucher Hotspot',
    'subtitle' => 'Semua voucher di jaringan Anda, termasuk yang dijual reseller/outlet.',
    'actions' => [['Arsip Batch', route('network.vouchers.batches')], ['+ Buat Voucher', route('network.vouchers.create'), true]],
])

<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode voucher" class="px-3 py-2 rounded-xl border-slate-200 text-sm">
    <input type="text" name="batch_code" value="{{ request('batch_code') }}" placeholder="Kode batch" class="px-3 py-2 rounded-xl border-slate-200 text-sm">
    <select name="status" class="px-3 py-2 rounded-xl border-slate-200 text-sm">
        <option value="">Semua status</option>
        @foreach($statuses as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach
    </select>
    <select name="product_id" class="px-3 py-2 rounded-xl border-slate-200 text-sm">
        <option value="">Semua paket</option>
        @foreach($products as $p)<option value="{{ $p['id'] }}" @selected((string) request('product_id') === (string) $p['id'])>{{ $p['name'] }}</option>@endforeach
    </select>
    <button class="bg-slate-900 hover:bg-slate-700 text-white rounded-xl text-xs font-black uppercase tracking-widest py-2">Filter</button>
</form>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                <tr>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Paket</th>
                    <th class="px-4 py-3 text-right">Harga</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-left">Pemilik</th>
                    <th class="px-4 py-3 text-left">Login Pertama / Habis</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($page['items'] as $v)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-mono font-bold text-slate-700">{{ $v['code'] }}
                        <div class="text-[10px] font-sans font-normal text-slate-400">{{ $v['batch_code'] ?? '' }}</div></td>
                    <td class="px-4 py-3 text-slate-600">{{ $v['product_name'] ?? '-' }}</td>
                    <td class="px-4 py-3 text-right font-bold text-slate-700 whitespace-nowrap">Rp {{ number_format($v['selling_price'] ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-center">@include('network.partials.badge', ['value' => $v['status'] ?? null, 'labels' => $statuses])</td>
                    <td class="px-4 py-3 text-slate-500 text-xs">{{ $v['entity']['name'] ?? '-' }}</td>
                    <td class="px-4 py-3 text-slate-500 text-xs whitespace-nowrap">{{ $v['first_login_at'] ?? '-' }}<br>{{ $v['expires_at'] ?? '' }}</td>
                    <td class="px-4 py-3 text-right"><a href="{{ route('network.vouchers.show', $v['code']) }}" class="text-sky-600 font-bold text-xs hover:underline">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400 text-sm">Tidak ada voucher.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('network.partials.pagination')
</div>
@endsection
