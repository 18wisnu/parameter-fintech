@extends('layouts.admin')

@section('content')
    @include('partials.flash')

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
            @if($totalPending > 0)
                <p class="text-slate-500 font-medium mb-2">Setoran Menunggu</p>
                <h3 class="text-3xl font-bold text-orange-600">Rp {{ number_format($totalPending, 0, ',', '.') }}</h3>
                <p class="text-orange-500 text-sm mt-2 font-bold">Perlu dikonfirmasi</p>
            @else
                <p class="text-slate-500 font-medium mb-2">Setoran Kolektor Hari Ini</p>
                <h3 class="text-3xl font-bold text-emerald-700">Rp {{ number_format($depositsToday, 0, ',', '.') }}</h3>
                <p class="text-slate-400 text-sm mt-2">Langsung tercatat di buku kas</p>
            @endif
        </div>

    </div>

    <!-- Monthly Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-emerald-50 p-6 rounded-lg shadow-sm border border-emerald-100">
            <div class="flex items-center justify-between mb-2">
                <p class="text-emerald-700 font-bold uppercase tracking-wider text-xs">Pendapatan Bulan Ini (Kotor)</p>
                <div class="bg-emerald-500 text-white p-1 rounded">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <h3 class="text-3xl font-bold text-emerald-800">Rp {{ number_format($incomeThisMonth, 0, ',', '.') }}</h3>
            <p class="text-emerald-600 text-xs mt-2 italic font-medium">Tanpa potongan operasional & cadangan</p>
        </div>

        <div class="bg-rose-50 p-6 rounded-lg shadow-sm border border-rose-100">
            <div class="flex items-center justify-between mb-2">
                <p class="text-rose-700 font-bold uppercase tracking-wider text-xs">Pengeluaran Bulan Ini</p>
                <div class="bg-rose-500 text-white p-1 rounded">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <h3 class="text-3xl font-bold text-rose-800">Rp {{ number_format($expenseThisMonth, 0, ',', '.') }}</h3>
            <p class="text-rose-600 text-xs mt-2 italic font-medium">Biaya operasional & teknisi</p>
        </div>
    </div>

    @if($pendingDeposits->isNotEmpty())
    <div class="bg-white rounded-2xl border border-amber-200 overflow-hidden mb-6">
        <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-4 border-b border-amber-100 bg-amber-50">
            <div>
                <h3 class="font-semibold text-slate-800">Setoran menunggu konfirmasi ({{ $pendingDeposits->count() }})</h3>
                <p class="text-xs text-slate-500">Setoran baru sekarang langsung masuk buku kas. Ini sisa setoran lama.</p>
            </div>
            <form action="{{ route('admin.deposit.approveAll') }}" method="POST" onsubmit="return confirm('Terima semua setoran ini dan catat di buku kas?')">
                @csrf
                <button class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">Terima semua</button>
            </form>
        </div>
        <table class="w-full text-sm">
            <tbody class="divide-y divide-slate-50">
            @foreach($pendingDeposits as $deposit)
                <tr>
                    <td class="px-5 py-3 text-slate-500 whitespace-nowrap">{{ $deposit->created_at->format('d M H:i') }}</td>
                    <td class="px-5 py-3"><div class="font-semibold text-slate-700">{{ $deposit->user->name ?? '-' }}</div><div class="text-xs text-slate-400">{{ $deposit->description }}</div></td>
                    <td class="px-5 py-3 text-right font-semibold text-emerald-700 whitespace-nowrap">Rp {{ number_format($deposit->amount, 0, ',', '.') }}</td>
                    <td class="px-5 py-3 text-right">
                        <form action="{{ route('admin.deposit.approve', $deposit->id) }}" method="POST">
                            @csrf
                            <button class="text-sm font-semibold text-emerald-700 hover:underline">Terima</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
            <h3 class="font-semibold text-slate-800">Transaksi terakhir</h3>
            <a href="{{ route('reports.history') }}" class="text-sm font-semibold text-sky-600 hover:underline">Buka buku kas</a>
        </div>
        <table class="w-full text-sm">
            <tbody class="divide-y divide-slate-50">
            @forelse($recentTransactions as $trx)
                <tr>
                    <td class="px-5 py-3 text-slate-500 whitespace-nowrap">{{ $trx->created_at?->format('d M H:i') }}</td>
                    <td class="px-5 py-3">
                        <div class="text-slate-700">{{ $trx->description }}</div>
                        <div class="text-xs text-slate-400">{{ $trx->account->name ?? '' }}{{ $trx->user ? ' · ' . $trx->user->name : '' }}</div>
                    </td>
                    <td class="px-5 py-3 text-right font-semibold whitespace-nowrap {{ $trx->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $trx->type === 'income' ? '+' : '−' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr><td class="px-5 py-10 text-center text-slate-400">Belum ada transaksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
