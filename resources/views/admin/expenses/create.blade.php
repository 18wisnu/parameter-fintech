@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 mb-6">
        <div class="flex items-center gap-4 mb-2">
            <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Catat Pengeluaran</h2>
                <p class="text-slate-500 text-sm">Input biaya operasional atau pengeluaran kas.</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('expenses.store') }}" method="POST">
            @if ($errors->any())
                <div class="alert alert-danger" style="background: red; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Transaksi</label>
                <input type="date" name="date" value="{{ date('Y-m-d') }}" required 
                    class="w-full px-4 py-3 rounded-xl border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all font-medium text-slate-600">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Pengeluaran (Akun Biaya)</label>
                <select name="account_id" required 
                    class="w-full px-4 py-3 rounded-xl border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all font-bold text-slate-700">
                    <option value="">-- Pilih Kategori Biaya --</option>
                    @foreach($expenseAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">Sumber Dana (Bayar Pakai?)</label>
                <select name="source_account_id" required 
                    class="w-full px-4 py-3 rounded-xl border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all font-bold text-slate-700">
                    <option value="">-- Pilih Sumber Kas/Bank --</option>
                    @foreach($sourceAccounts as $source)
                        <option value="{{ $source->id }}">{{ $source->name }}</option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-400 mt-1 italic">*Pilih Kas Besar, Bank, atau Dana Cadangan.</p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">Nominal (Rp)</label>
                <div class="relative">
                    <span class="absolute left-4 top-3.5 text-slate-400 font-bold">Rp</span>
                    <input type="number" name="amount" placeholder="Contoh: 500000" required 
                        class="w-full pl-12 pr-4 py-3 rounded-xl border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all font-bold text-lg text-slate-800">
                </div>
            </div>

            <div class="mb-8">
                <label class="block text-sm font-bold text-slate-700 mb-2">Keterangan Detail</label>
                <textarea name="description" rows="3" placeholder="Contoh: Bayar tagihan Biznet bulan Februari" required 
                    class="w-full px-4 py-3 rounded-xl border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all font-medium text-slate-600"></textarea>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex-1 text-center py-4 rounded-2xl font-bold text-slate-500 bg-slate-50 hover:bg-slate-100 transition-all">
                    Batal
                </a>
                <button type="submit" class="flex-[2] py-4 rounded-2xl font-bold text-white bg-rose-500 hover:bg-rose-600 shadow-lg shadow-rose-200 transition-all transform active:scale-95">
                    Simpan Pengeluaran
                </button>
            </div>
        </form>
    </div>
</div>
@endsection