@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Dana Cadangan</h2>
        <p class="text-slate-500 text-sm">Log tabungan perusahaan.</p>
    </div>
    <a href="{{ route('reports.reserve.pdf') }}" class="bg-rose-600 text-white px-4 py-2 rounded-xl font-bold hover:bg-rose-700 shadow-lg">Download PDF</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-indigo-600 text-white p-6 rounded-2xl shadow-lg">
        <p class="text-indigo-200 text-sm font-bold mb-1">Total Dana Tersimpan</p>
        <h3 class="text-3xl font-bold">Rp {{ number_format($saldoCadangan, 0, ',', '.') }}</h3>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 col-span-2">
        <h4 class="font-bold text-slate-800 mb-4">Gunakan Dana Cadangan</h4>
        <form action="{{ route('reports.reserve.store') }}" method="POST" class="flex gap-3">
            @csrf
            <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="rounded-lg border-slate-200">
            <input type="number" name="amount" placeholder="Rp Nominal" class="rounded-lg border-slate-200 font-bold">
            <input type="text" name="description" placeholder="Keperluan..." class="flex-1 rounded-lg border-slate-200">
            <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-lg font-bold">Simpan</button>
        </form>
    </div>
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
            @foreach($logs as $log)
            <tr class="border-b border-slate-50 hover:bg-slate-50">
                <td class="px-6 py-4 text-sm">{{ $log->transaction_date->format('d/m/Y') }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 rounded text-xs font-bold {{ $log->type == 'in' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        {{ $log->type == 'in' ? 'MASUK' : 'KELUAR' }}
                    </span>
                </td>
                <td class="px-6 py-4">{{ $log->description }}</td>
                <td class="px-6 py-4 text-right font-bold {{ $log->type == 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $log->type == 'in' ? '+' : '-' }} Rp {{ number_format($log->amount, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4">{{ $logs->links() }}</div>
</div>
@endsection