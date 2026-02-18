@extends('layouts.admin')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-slate-800">Tambah Saldo / Modal</h2>
        <p class="text-slate-500 text-sm">Input pemasukan manual ke dalam kas.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-emerald-500"></div>

        <form action="{{ route('incomes.store') }}" method="POST">
            @csrf
            
            <div class="mb-5">
                <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Transaksi</label>
                <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="w-full px-4 py-3 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-100 font-medium">
            </div>

            <div class="mb-5">
                <label class="block text-sm font-bold text-slate-700 mb-2">Sumber Dana</label>
                <select name="account_id" class="w-full px-4 py-3 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-100 bg-white">
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-bold text-slate-700 mb-2">Nominal (Rp)</label>
                <div class="relative">
                    <span class="absolute left-4 top-3.5 text-slate-400 font-bold">Rp</span>
                    <input type="number" name="amount" required class="w-full pl-12 pr-4 py-3 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-100 font-bold text-lg text-emerald-700" placeholder="0">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">Keterangan</label>
                <textarea name="description" rows="2" class="w-full px-4 py-3 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-100" placeholder="Contoh: Modal Awal Bulan..."></textarea>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('dashboard') }}" class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition">Batal</a>
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-emerald-200 transition">
                    Simpan Pemasukan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection