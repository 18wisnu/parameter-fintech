@extends('layouts.admin')

@section('content')
@include('partials.flash')
@include('platform._nav', ['title' => 'Paket & Pengaturan'])
@php $input = 'w-full py-2 px-3 rounded-xl border-slate-200 text-sm'; @endphp

<div class="space-y-3 mb-6">
    @foreach($plans as $plan)
        <form method="POST" action="{{ route('platform.plans.update', $plan) }}" class="bg-white rounded-2xl border border-slate-100 p-4 grid grid-cols-2 md:grid-cols-[1fr_140px_140px_2fr_70px_auto_auto] gap-2 items-center">
            @csrf @method('PUT')
            <input name="name" value="{{ $plan->name }}" required class="{{ $input }} font-semibold" title="Nama">
            <input name="price" type="number" min="0" value="{{ (int) $plan->price }}" required class="{{ $input }}" title="Harga per bulan">
            <input name="max_customers" type="number" min="1" value="{{ $plan->max_customers }}" placeholder="Tanpa batas" class="{{ $input }}" title="Maks. pelanggan">
            <input name="description" value="{{ $plan->description }}" placeholder="Keterangan" class="{{ $input }}">
            <input name="sort" type="number" min="0" value="{{ $plan->sort }}" class="{{ $input }}" title="Urutan">
            <label class="flex items-center gap-1 text-xs text-slate-600"><input type="checkbox" name="is_active" value="1" @checked($plan->is_active)> Aktif</label>
            <div class="flex items-center gap-2">
                <button class="px-3 py-2 rounded-xl bg-slate-800 text-white text-xs font-bold">Simpan</button>
                <span class="text-xs text-slate-400 whitespace-nowrap">{{ $plan->businesses_count }} usaha</span>
            </div>
        </form>
    @endforeach
    <p class="text-xs text-slate-400 px-1">Kolom: nama · harga/bulan · maks. pelanggan (kosong = tanpa batas) · keterangan · urutan. Paket nonaktif tidak bisa dipilih usaha, tetapi usaha yang sudah memakainya tetap berjalan.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <form method="POST" action="{{ route('platform.plans.store') }}" class="bg-white rounded-2xl border border-slate-100 p-5 space-y-2">
        @csrf
        <h3 class="font-bold text-slate-800">Tambah paket</h3>
        <input name="name" required placeholder="Nama paket" class="{{ $input }}">
        <div class="grid grid-cols-2 gap-2">
            <input name="price" type="number" min="0" required placeholder="Harga / bulan" class="{{ $input }}">
            <input name="max_customers" type="number" min="1" placeholder="Maks. pelanggan (kosong = tanpa batas)" class="{{ $input }}">
        </div>
        <input name="description" placeholder="Keterangan" class="{{ $input }}">
        <input type="hidden" name="is_active" value="1">
        <button class="px-4 py-2 rounded-xl bg-sky-600 text-white text-sm font-bold">Tambah</button>
    </form>

    <form method="POST" action="{{ route('platform.settings.update') }}" class="bg-white rounded-2xl border border-slate-100 p-5 space-y-2">
        @csrf @method('PUT')
        <h3 class="font-bold text-slate-800">Instruksi pembayaran</h3>
        <p class="text-xs text-slate-500">Tampil di halaman Langganan setiap usaha, mis. nomor rekening CV.</p>
        <textarea name="payment_instructions" rows="5" class="{{ $input }}" placeholder="Transfer ke BCA 123xxxx a.n. CV. Parameter Digital Inovation">{{ $instructions }}</textarea>
        <button class="px-4 py-2 rounded-xl bg-slate-800 text-white text-sm font-bold">Simpan</button>
    </form>
</div>
@endsection
