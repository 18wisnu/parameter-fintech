@extends('layouts.admin')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Dana Cadangan</h2>
        <p class="text-slate-500 text-sm mt-1">Kelola dan pantau log tabungan perusahaan Anda.</p>
    </div>
    <a href="{{ route('reports.reserve.pdf') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold hover:bg-slate-50 hover:text-rose-600 transition-all shadow-sm text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        Download PDF
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 items-stretch">
    
    <div class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-3xl p-6 text-white shadow-lg shadow-indigo-200 flex flex-col justify-between h-full relative overflow-hidden group">
        <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-white opacity-10 blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
        <div class="relative z-10">
            <p class="text-indigo-100 text-xs font-bold mb-2 uppercase tracking-widest">Total Dana Tersimpan</p>
            <h3 class="text-3xl lg:text-4xl font-extrabold tracking-tight">Rp {{ number_format($saldoCadangan, 0, ',', '.') }}</h3>
        </div>
        <div class="relative z-10 mt-8">
            <span class="inline-flex items-center gap-1 bg-white/20 px-3 py-1.5 rounded-full text-xs font-semibold backdrop-blur-sm">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span> Sinkron Akuntansi
            </span>
        </div>
    </div>

    @if(auth()->user()->role === 'owner')
    <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 h-full">
        
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-shadow flex flex-col">
            <div class="flex items-center gap-3 mb-5 border-b border-slate-50 pb-4">
                <div class="bg-emerald-100 text-emerald-600 p-2.5 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <div>
                    <h4 class="font-extrabold text-slate-800 text-sm uppercase">Suntik Dana</h4>
                    <p class="text-xs text-slate-400 mt-0.5">Tambah modal tabungan</p>
                </div>
            </div>
            <form action="{{ route('reports.reserve.inject') }}" method="POST" class="space-y-4 flex-1 flex flex-col justify-between">
                @csrf
                <div class="space-y-4">
                    <div>
                        <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring focus:ring-emerald-200 transition-all text-sm" required>
                    </div>
                    <div>
                        <input type="number" name="amount" placeholder="Nominal (Rp)" class="w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring focus:ring-emerald-200 transition-all font-bold text-slate-700 text-sm" required>
                    </div>
                    <div>
                        <input type="text" name="description" placeholder="Keterangan..." class="w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring focus:ring-emerald-200 transition-all text-sm" required>
                    </div>
                </div>
                <button type="submit" class="w-full mt-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl transition-all text-sm shadow-sm shadow-emerald-200">
                    Simpan Pemasukan
                </button>
            </form>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-shadow flex flex-col">
            <div class="flex items-center gap-3 mb-5 border-b border-slate-50 pb-4">
                <div class="bg-slate-100 text-slate-600 p-2.5 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </div>
                <div>
                    <h4 class="font-extrabold text-slate-800 text-sm uppercase">Gunakan Dana</h4>
                    <p class="text-xs text-slate-400 mt-0.5">Tarik dana keperluan</p>
                </div>
            </div>
            <form action="{{ route('reports.reserve.store') }}" method="POST" class="space-y-4 flex-1 flex flex-col justify-between">
                @csrf
                <div class="space-y-4">
                    <div>
                        <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-slate-200 focus:border-slate-500 focus:ring focus:ring-slate-200 transition-all text-sm" required>
                    </div>
                    <div>
                        <input type="number" name="amount" placeholder="Nominal (Rp)" class="w-full rounded-xl border-slate-200 focus:border-slate-500 focus:ring focus:ring-slate-200 transition-all font-bold text-slate-700 text-sm" required>
                    </div>
                    <div>
                        <input type="text" name="description" placeholder="Keperluan..." class="w-full rounded-xl border-slate-200 focus:border-slate-500 focus:ring focus:ring-slate-200 transition-all text-sm" required>
                    </div>
                </div>
                <button type="submit" class="w-full mt-4 bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-xl transition-all text-sm shadow-sm">
                    Simpan Pengeluaran
                </button>
            </form>
        </div>

    </div>
    @else
    <div class="lg:col-span-2 bg-slate-50/50 rounded-3xl border border-slate-200 flex flex-col items-center justify-center p-8 shadow-inner text-center">
        <div class="bg-rose-100 text-rose-600 p-4 rounded-full mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
        </div>
        <h4 class="font-extrabold text-slate-800 mb-2">Akses Dibatasi</h4>
        <p class="text-sm text-slate-500 max-w-sm">Hanya akun dengan akses <span class="font-bold text-slate-700">Owner</span> yang diizinkan untuk menambah atau menarik Dana Cadangan perusahaan.</p>
    </div>
    @endif
</div>

<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-6 border-b border-slate-50">
        <h3 class="font-extrabold text-slate-800">Riwayat Mutasi Cadangan</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50/50 text-slate-400 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 font-bold">Tanggal</th>
                    <th class="px-6 py-4 font-bold">Tipe Mutasi</th>
                    <th class="px-6 py-4 font-bold">Keterangan</th>
                    <th class="px-6 py-4 font-bold text-right">Nominal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($logs as $log)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4 text-sm text-slate-600 font-medium">{{ \Carbon\Carbon::parse($log->transaction_date)->format('d M Y') }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold {{ $log->type == 'in' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                            @if($log->type == 'in')
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg> MASUK
                            @else
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg> KELUAR
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $log->description }}</td>
                    <td class="px-6 py-4 text-right font-bold text-sm {{ $log->type == 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $log->type == 'in' ? '+' : '-' }} Rp {{ number_format($log->amount, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="p-4 border-t border-slate-50">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection