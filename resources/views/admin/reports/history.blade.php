@extends('layouts.admin')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Riwayat Transaksi</h2>
    <p class="text-slate-500 text-sm">Semua pemasukan dan pengeluaran.</p>
</div>

<div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 flex justify-between items-end">
    <form action="{{ route('reports.history') }}" method="GET" class="flex gap-3">
        <div>
            <label class="text-xs font-bold text-slate-500">Dari</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="block rounded-lg border-slate-200 text-sm">
        </div>
        <div>
            <label class="text-xs font-bold text-slate-500">Sampai</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="block rounded-lg border-slate-200 text-sm">
        </div>
        <button type="submit" class="mt-4 bg-sky-600 text-white px-4 py-2 rounded-lg text-sm font-bold">Filter</button>
    </form>
    <a href="{{ route('reports.history.pdf', request()->all()) }}" class="bg-slate-700 text-white px-4 py-2 rounded-lg font-bold text-sm">Cetak PDF</a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
            <tr>
                <th class="px-6 py-4">Tanggal</th>
                <th class="px-6 py-4">Tipe</th>
                <th class="px-6 py-4">Keterangan</th>
                <th class="px-6 py-4 text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $trx)
            <tr class="border-b border-slate-50 hover:bg-slate-50">
                <td class="px-6 py-4 text-sm">{{ \Carbon\Carbon::parse($trx->transaction_date)->format('d/m/Y') }}</td>
                <td class="px-6 py-4">
                    <span class="font-bold text-xs {{ $trx->type == 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $trx->type == 'in' ? 'PEMASUKAN' : 'PENGELUARAN' }}
                    </span>
                </td>
                <td class="px-6 py-4">{{ $trx->description }}</td>
                <td class="px-6 py-4 text-right font-bold {{ $trx->type == 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $trx->type == 'in' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}

                    @if(auth()->user()->role === 'owner')
                        <form action="{{ route('transactions.destroy', $trx->id) }}" method="POST" onsubmit="return confirm('Yakin hapus permanen?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-100 text-red-600 px-2 py-1 rounded">
                                🗑️ Hapus
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4">{{ $transactions->links() }}</div>
</div>
@endsection