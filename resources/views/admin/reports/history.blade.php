@extends('layouts.admin')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Riwayat Transaksi</h2>
        <p class="text-slate-500 mt-1 text-sm font-medium">Laporan arus kas masuk dan keluar secara real-time.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('reports.history.pdf', request()->all()) }}" class="flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Cetak Laporan (PDF)
        </a>
    </div>
</div>

<!-- Filter Section -->
<div class="bg-white p-2 rounded-[2rem] shadow-sm border border-slate-100 mb-8 overflow-hidden">
    <form action="{{ route('reports.history') }}" method="GET" class="flex flex-col md:flex-row items-center gap-2">
        <div class="grid grid-cols-2 gap-2 w-full md:flex-1">
            <div class="relative group">
                <label class="absolute left-4 top-2 text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] group-focus-within:text-sky-500 transition-colors">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full pl-4 pr-4 pt-6 pb-2 rounded-2xl border-none bg-slate-50 focus:ring-2 focus:ring-sky-500/20 text-sm font-bold text-slate-700 transition-all">
            </div>
            <div class="relative group">
                <label class="absolute left-4 top-2 text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] group-focus-within:text-sky-500 transition-colors">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full pl-4 pr-4 pt-6 pb-2 rounded-2xl border-none bg-slate-50 focus:ring-2 focus:ring-sky-500/20 text-sm font-bold text-slate-700 transition-all">
            </div>
        </div>
        <button type="submit" class="w-full md:w-auto bg-sky-600 hover:bg-sky-500 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-md active:scale-95">
            Terapkan Filter
        </button>
        @if(request('start_date') || request('end_date'))
            <a href="{{ route('reports.history') }}" class="w-full md:w-auto bg-slate-100 hover:bg-slate-200 text-slate-500 px-6 py-4 rounded-2xl font-black text-xs uppercase tracking-widest text-center transition-all">Reset</a>
        @endif
    </form>
</div>

<!-- content Section -->
<div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden mb-12">
    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50/50 border-b border-slate-100 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                <tr>
                    <th class="px-8 py-5">Info Transaksi</th>
                    <th class="px-8 py-5">Tipe Arus</th>
                    <th class="px-8 py-5 text-right">Nominal (IDR)</th>
                    @if(auth()->user()->role === 'owner')
                    <th class="px-8 py-5 text-center">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($transactions as $trx)
                <tr class="hover:bg-slate-50/50 transition-colors group">
                    <td class="px-8 py-5">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl {{ $trx->type == 'income' ? 'bg-emerald-50 text-emerald-500' : 'bg-rose-50 text-rose-500' }} flex items-center justify-center font-bold shadow-inner">
                                @if($trx->type == 'income')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800 leading-tight">{{ $trx->description }}</p>
                                <p class="text-[10px] text-slate-400 mt-1 font-bold uppercase tracking-wider">{{ \Carbon\Carbon::parse($trx->date)->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-5">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border
                            {{ $trx->type == 'income' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100' }}">
                            {{ $trx->type == 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                        </span>
                    </td>
                    <td class="px-8 py-5 text-right font-black text-lg {{ $trx->type == 'income' ? 'text-emerald-500' : 'text-rose-500' }}">
                        {{ $trx->type == 'income' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                    </td>
                    @if(auth()->user()->role === 'owner')
                    <td class="px-8 py-5 text-center">
                        <form action="{{ route('transactions.destroy', $trx->id) }}" method="POST" onsubmit="return confirm('Yakin hapus permanen?')" class="opacity-0 group-hover:opacity-100 transition-opacity">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-500 p-2.5 rounded-xl transition-all" title="Hapus Permanen">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards View -->
    <div class="md:hidden divide-y divide-slate-100">
        @foreach($transactions as $trx)
        <div class="p-6 active:bg-slate-50 transition-colors">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $trx->type == 'income' ? 'bg-emerald-50 text-emerald-500' : 'bg-rose-50 text-rose-500' }} flex items-center justify-center">
                        @if($trx->type == 'income')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">{{ \Carbon\Carbon::parse($trx->date)->format('d M, H:i') }}</p>
                        <p class="text-xs font-black text-slate-500 uppercase tracking-tighter">{{ $trx->type == 'income' ? 'Pemasukan' : 'Pengeluaran' }}</p>
                    </div>
                </div>
                
                @if(auth()->user()->role === 'owner')
                <form action="{{ route('transactions.destroy', $trx->id) }}" method="POST" onsubmit="return confirm('Hapus Permanen?')" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-slate-300 hover:text-rose-500 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>
                @endif
            </div>

            <div class="flex justify-between items-end">
                <p class="text-sm font-bold text-slate-800 leading-tight flex-1 pr-4">{{ $trx->description }}</p>
                <p class="text-base font-black {{ $trx->type == 'income' ? 'text-emerald-500' : 'text-rose-500' }} whitespace-nowrap">
                    {{ $trx->type == 'income' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                </p>
            </div>
        </div>
        @endforeach
    </div>
    
    @if($transactions->hasPages())
    <div class="p-6 bg-slate-50/50 border-t border-slate-100">
        {{ $transactions->links() }}
    </div>
    @endif
</div>
@endsection