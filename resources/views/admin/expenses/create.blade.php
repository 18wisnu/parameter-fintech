@extends('layouts.admin')

@section('content')
@php
    $input = 'w-full px-3 py-2.5 rounded-xl border-slate-200 text-sm focus:border-sky-500 focus:ring-sky-500';
    $label = 'block text-sm font-medium text-slate-700 mb-1';
@endphp
<div class="max-w-2xl mx-auto">
    <a href="{{ route('reports.history') }}" class="text-sm text-slate-500 hover:text-slate-800">← Buku Kas</a>
    <h2 class="text-2xl font-bold text-slate-800 mt-2 mb-5">Catat Pengeluaran</h2>

    @include('partials.flash')
    @if($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-700 text-sm">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-100 p-5 space-y-4">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="{{ $label }}">Tanggal</label>
                <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Nominal</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-sm text-slate-400">Rp</span>
                    <input type="number" name="amount" min="1" value="{{ old('amount') }}" required class="{{ $input }} pl-10" placeholder="500000">
                </div>
            </div>
            <div>
                <label class="{{ $label }}">Jenis biaya</label>
                <select name="account_id" required class="{{ $input }}">
                    <option value="">Pilih jenis…</option>
                    @foreach($expenseAccounts as $a)<option value="{{ $a->id }}" @selected(old('account_id') == $a->id)>{{ $a->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Dibayar dari</label>
                <select name="source_account_id" required class="{{ $input }}">
                    @foreach($sourceAccounts as $a)<option value="{{ $a->id }}" @selected(old('source_account_id') == $a->id)>{{ $a->name }}</option>@endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="{{ $label }}">Keterangan</label>
                <input type="text" name="description" value="{{ old('description') }}" required maxlength="255" class="{{ $input }}" placeholder="Contoh: Bayar bandwidth bulan November">
            </div>
            <div class="sm:col-span-2">
                <label class="{{ $label }}">Catatan tambahan <span class="text-slate-400 font-normal">(opsional)</span></label>
                <textarea name="notes" rows="2" maxlength="500" class="{{ $input }}" placeholder="Toko, nomor nota, jumlah barang, dll.">{{ old('notes') }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="{{ $label }}">Foto nota / bukti <span class="text-slate-400 font-normal">(opsional)</span></label>
                <input type="file" name="proof_image" accept="image/*" class="block w-full text-sm border border-slate-200 rounded-xl file:mr-3 file:py-2 file:px-4 file:border-0 file:bg-slate-100 file:font-semibold">
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <a href="{{ route('reports.history') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600">Batal</a>
            <button class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold">Simpan Pengeluaran</button>
        </div>
    </form>
</div>
@endsection
