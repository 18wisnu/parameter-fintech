@extends('layouts.admin')

@section('content')
@include('partials.flash')

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Outlet</h2>
        <p class="text-sm text-slate-500">{{ number_format($counts['all']) }} outlet voucher · tidak dihitung dalam batas paket pelanggan</p>
    </div>
    <div class="flex items-center gap-2">
        @if($network)
            <a href="{{ route('reports.vouchers') }}" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600 hover:bg-slate-50">Analisa Voucher</a>
        @else
            <a href="{{ route('customers.create', ['type' => \App\Models\Customer::OUTLET_TYPE]) }}" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">+ Outlet</a>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
    <div class="bg-white rounded-2xl border border-slate-100 p-4">
        <p class="text-xs text-slate-500">Setoran voucher bulan ini (buku kas)</p>
        <p class="text-xl font-bold text-slate-800 mt-1">Rp {{ number_format($monthTotal, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-4 text-sm text-slate-500">
        @if($network)
            Outlet diambil otomatis dari server jaringan saat sinkron. Penjualan voucher per outlet ada di <a href="{{ route('reports.vouchers') }}" class="text-sky-700 font-semibold">Analisa Voucher</a>.
        @else
            Setoran outlet dicatat dari menu Setoran (pilih jenis <b>Voucher (Outlet)</b>) dan masuk ke akun Pendapatan Voucher.
        @endif
    </div>
</div>

<form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
    @php $statusTabs = ['' => ['Semua', $counts['all']], 'active' => ['Aktif', $counts['active']], 'inactive' => ['Nonaktif', $counts['inactive']]]; @endphp
    <div class="flex bg-white border border-slate-200 rounded-xl p-1 overflow-x-auto">
        @foreach($statusTabs as $key => [$label, $n])
            <a href="{{ route('outlets.index', array_filter(['status' => $key, 'search' => request('search')])) }}"
               class="px-3 py-1.5 rounded-lg text-sm whitespace-nowrap {{ (string) request('status') === (string) $key ? 'bg-slate-800 text-white font-semibold' : 'text-slate-500 hover:text-slate-800' }}">
                {{ $label }} <span class="opacity-60">{{ $n }}</span>
            </a>
        @endforeach
    </div>
    <input type="hidden" name="status" value="{{ request('status') }}">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode, HP…" class="flex-1 min-w-[200px] py-2 px-3 rounded-xl border-slate-200 text-sm">
</form>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3 font-semibold">Outlet</th>
                    <th class="px-4 py-3 font-semibold">Kontak</th>
                    <th class="px-4 py-3 font-semibold text-right">Setoran bulan ini</th>
                    <th class="px-4 py-3 font-semibold text-right">Total setoran</th>
                    <th class="px-4 py-3 font-semibold">Setoran terakhir</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($outlets as $o)
                @php $d = $deposits[$o->id] ?? null; $inactive = $o->status === 'inactive'; @endphp
                <tr class="hover:bg-slate-50 cursor-pointer" onclick="window.location='{{ route('customers.show', $o) }}'">
                    <td class="px-4 py-3">
                        <a href="{{ route('customers.show', $o) }}" class="font-semibold text-slate-800 hover:text-sky-700">{{ $o->name }}</a>
                        <div class="text-xs text-slate-400 font-mono">{{ $o->displayCode() ?? '—' }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $o->phone ?: '—' }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-800">Rp {{ number_format($d->month ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-slate-600">Rp {{ number_format($d->total ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $d?->last_date ? \Carbon\Carbon::parse($d->last_date)->locale('id')->translatedFormat('d M Y') : '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-md text-xs font-semibold {{ $inactive ? 'bg-slate-100 text-slate-500' : 'bg-emerald-50 text-emerald-700' }}">{{ $inactive ? 'Nonaktif' : 'Aktif' }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-14 text-center text-slate-400">
                    {{ request()->hasAny(['search', 'status']) ? 'Tidak ada outlet yang cocok dengan filter.' : 'Belum ada outlet.' }}
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($outlets->hasPages())<div class="p-4 border-t border-slate-50">{{ $outlets->links() }}</div>@endif
</div>
@endsection
