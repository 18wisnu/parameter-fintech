@extends('layouts.admin')

@section('content')

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Dashboard Overview</h2>
            <p class="text-slate-500 mt-1">Pantau kesehatan keuangan perusahaan Anda hari ini.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('incomes.create') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-lg shadow flex items-center font-bold text-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Tambah Modal
            </a>
            <a href="{{ route('expenses.create') }}" class="bg-rose-500 hover:bg-rose-600 text-white px-5 py-2.5 rounded-lg shadow flex items-center font-bold text-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Pengeluaran
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-100">
            <p class="text-slate-500 font-medium mb-2">Saldo Kas Besar</p>
            <h3 class="text-3xl font-bold text-slate-800">Rp {{ number_format($saldoReal, 0, ',', '.') }}</h3>
            <p class="text-emerald-500 text-sm mt-2 font-bold flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Terhubung ke Akuntansi
            </p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-100">
            <p class="text-slate-500 font-medium mb-2">Total Dana Cadangan</p>
            <h3 class="text-3xl font-bold text-indigo-700">Rp {{ number_format($totalCadangan, 0, ',', '.') }}</h3>
            <p class="text-indigo-500 text-sm mt-2 font-bold">
                Tabungan Perusahaan
            </p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-100">
            <p class="text-slate-500 font-medium mb-2">Setoran Menunggu</p>
            <h3 class="text-3xl font-bold text-orange-600">Rp {{ number_format($totalPending, 0, ',', '.') }}</h3>
            @if($totalPending > 0)
                <p class="text-orange-500 text-sm mt-2 font-bold animate-pulse">Segera verifikasi</p>
            @else
                <p class="text-slate-400 text-sm mt-2">Aman terkendali</p>
            @endif
        </div>

    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="font-bold text-lg text-slate-800">Setoran Masuk (Butuh Approval)</h3>
        </div>

        @if($pendingDeposits->isEmpty())
            <div class="text-center py-10 text-slate-400">Tidak ada setoran baru.</div>
        @else
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-500 text-sm">
                        <tr>
                            <th class="px-6 py-4 font-semibold">TANGGAL</th>
                            <th class="px-6 py-4 font-semibold">TEKNISI</th>
                            <th class="px-6 py-4 font-semibold">KETERANGAN</th>
                            <th class="px-6 py-4 font-semibold">NOMINAL</th>
                            <th class="px-6 py-4 font-semibold text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($pendingDeposits as $deposit)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-slate-500 text-sm">{{ $deposit->created_at->format('d M H:i') }}</td>
                            <td class="px-6 py-4 font-bold">{{ $deposit->user->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $deposit->description }}</td>
                            <td class="px-6 py-4 font-bold text-emerald-600">Rp {{ number_format($deposit->amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('admin.deposit.approve', $deposit->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-emerald-500 text-white px-4 py-2 rounded text-sm font-bold hover:bg-emerald-600" onclick="return confirm('Terima?')">
                                        TERIMA UANG
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="md:hidden p-4 space-y-4">
                @foreach($pendingDeposits as $deposit)
                <div class="border border-slate-200 rounded-lg p-4 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <span class="text-xs text-slate-400 font-bold">{{ $deposit->created_at->format('d M H:i') }}</span>
                            <h4 class="font-bold text-slate-800">{{ $deposit->user->name }}</h4>
                        </div>
                        <span class="bg-orange-100 text-orange-600 text-xs px-2 py-1 rounded font-bold">Pending</span>
                    </div>
                    <p class="text-sm text-slate-600 mb-3">{{ $deposit->description }}</p>
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-emerald-600 text-lg">Rp {{ number_format($deposit->amount, 0, ',', '.') }}</span>
                        <form action="{{ route('admin.deposit.approve', $deposit->id) }}" method="POST">
                            @csrf
                            <button class="bg-emerald-500 text-white px-3 py-1.5 rounded text-sm font-bold shadow-sm">
                                Terima
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

@endsection