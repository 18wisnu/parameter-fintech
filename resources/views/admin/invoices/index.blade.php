@extends('layouts.admin')

@section('content')

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Tagihan Pelanggan</h2>
            <p class="text-slate-500 text-sm">Kelola tagihan bulanan (PPPoE/Hotspot) dan status isolir.</p>
        </div>
        
        <div class="flex flex-wrap gap-2 w-full md:w-auto">
            <form action="{{ route('invoices.checkIsolir') }}" method="POST">
                @csrf
                <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-600 px-4 py-2.5 rounded-xl border border-rose-200 font-bold text-sm transition-all" onclick="return confirm('Jalankan Cek Isolir Otomatis?')">
                    ⚡ Cek Isolir
                </button>
            </form>

            <form action="{{ route('invoices.generate') }}" method="POST">
                @csrf
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl shadow-lg shadow-indigo-100 font-bold text-sm transition-all flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Tagihan Bulan Ini
                </button>
            </form>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="mb-6 bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
        <form action="{{ route('invoices.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                <!-- Filter Status -->
                <div class="flex bg-slate-100 p-1 rounded-xl">
                    <a href="{{ route('invoices.index', array_merge(request()->all(), ['status' => ''])) }}" 
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ !request('status') ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Semua</a>
                    <a href="{{ route('invoices.index', array_merge(request()->all(), ['status' => 'unpaid'])) }}" 
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ request('status') == 'unpaid' ? 'bg-white text-rose-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Belum Bayar</a>
                    <a href="{{ route('invoices.index', array_merge(request()->all(), ['status' => 'paid'])) }}" 
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ request('status') == 'paid' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Lunas</a>
                </div>

                <input type="hidden" name="status" value="{{ request('status') }}">

                <!-- Filter Bulan -->
                <select name="month" class="bg-slate-50 border-slate-100 rounded-xl text-xs font-bold text-slate-600 focus:ring-sky-500 focus:border-sky-500 py-2" onchange="this.form.submit()">
                    <option value="">-- Pilih Bulan --</option>
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endforeach
                </select>

                <!-- Filter Tahun -->
                <select name="year" class="bg-slate-50 border-slate-100 rounded-xl text-xs font-bold text-slate-600 focus:ring-sky-500 focus:border-sky-500 py-2" onchange="this.form.submit()">
                    <option value="">-- Pilih Tahun --</option>
                    @foreach(range(date('Y')-1, date('Y')+1) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div class="relative w-full md:w-72">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Cari nama atau no. invoice..." 
                    class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-100 bg-slate-50 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all text-sm outline-none">
                <div class="absolute left-3 top-2.5 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px] md:min-w-full">
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
                    <td class="px-6 py-4 font-bold text-slate-700 whitespace-nowrap">
                        {{ $inv->customer->name }}
                        @if($inv->customer->is_isolated)
                            <span class="text-[9px] bg-rose-100 text-rose-600 px-1.5 py-0.5 rounded ml-1 font-black">ISOLIR</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-slate-500 text-sm whitespace-nowrap">{{ \Carbon\Carbon::parse($inv->period_date)->format('F Y') }}</td>
                    <td class="px-6 py-4 text-right font-bold text-slate-800 whitespace-nowrap">Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        @if($inv->status == 'paid')
                            <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">Lunas</span>
                        @else
                            <span class="bg-rose-100 text-rose-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">Belum Bayar</span>
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
        </div>
        <div class="p-4 border-t border-slate-50">{{ $invoices->links() }}</div>
    </div>

@endsection