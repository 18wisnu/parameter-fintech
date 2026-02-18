@extends('layouts.admin')

@section('content')

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Tagihan Pelanggan</h2>
            <p class="text-slate-500 text-sm">Kelola tagihan bulanan (PPPoE/Hotspot).</p>
        </div>
        
        <div class="flex gap-2">
            <form action="{{ route('invoices.checkIsolir') }}" method="POST">
                @csrf
                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 rounded-xl shadow-md font-bold text-sm transition-all" onclick="return confirm('Jalankan Cek Isolir Otomatis?')">
                    ⚡ Cek Isolir
                </button>
            </form>

            <form action="{{ route('invoices.generate') }}" method="POST">
                @csrf
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl shadow-md font-bold text-sm transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Buat Tagihan Bulan Ini
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs uppercase">
                <tr>
                    <th class="px-6 py-4">No. Invoice</th>
                    <th class="px-6 py-4">Pelanggan</th>
                    <th class="px-6 py-4">Periode</th>
                    <th class="px-6 py-4 text-right">Nominal</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($invoices as $inv)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $inv->invoice_number }}</td>
                    <td class="px-6 py-4 font-bold text-slate-700">
                        {{ $inv->customer->name }}
                        @if($inv->customer->is_isolated)
                            <span class="text-[10px] bg-rose-100 text-rose-600 px-1 rounded ml-1">ISOLIR</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-slate-500 text-sm">{{ \Carbon\Carbon::parse($inv->period_date)->format('F Y') }}</td>
                    <td class="px-6 py-4 text-right font-bold text-slate-800">Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($inv->status == 'paid')
                            <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full text-xs font-bold">LUNAS</span>
                        @else
                            <span class="bg-rose-100 text-rose-700 px-2 py-1 rounded-full text-xs font-bold">BELUM BAYAR</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($inv->status == 'unpaid')
                            <form action="{{ route('invoices.paid', $inv->id) }}" method="POST">
                                @csrf
                                <button class="text-xs bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded font-bold" onclick="return confirm('Tandai Lunas?')">Bayar</button>
                            </form>
                        @else
                            <span class="text-slate-300 text-xs font-bold">Selesai</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $invoices->links() }}</div>
    </div>

@endsection