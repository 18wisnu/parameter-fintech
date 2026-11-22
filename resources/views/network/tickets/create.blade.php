@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => 'Buat Tiket',
    'subtitle' => 'Catat keluhan pelanggan (mis. dari telepon/WhatsApp).',
    'actions' => [['← Daftar Tiket', route('network.tickets.index')]],
])

@php $input = 'w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100'; $label = 'block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5'; @endphp

<form action="{{ route('network.tickets.store') }}" method="POST" class="max-w-2xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
    @csrf
    <div>
        <label class="{{ $label }}">Pelanggan</label>
        <select name="customer_id" required class="{{ $input }}">
            <option value="">— Pilih pelanggan —</option>
            @foreach($customers as $c)
            <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }}{{ $c->code ? ' (' . $c->code . ')' : '' }}</option>
            @endforeach
        </select>
        @if($customers->isEmpty())<p class="mt-1 text-xs text-amber-600">Belum ada pelanggan yang tersinkron dengan jaringan. Jalankan sinkronisasi pelanggan dulu.</p>@endif
    </div>
    <div>
        <label class="{{ $label }}">Judul</label>
        <input type="text" name="subject" required maxlength="200" value="{{ old('subject') }}" placeholder="mis. Internet lambat sejak pagi" class="{{ $input }}">
    </div>
    <div>
        <label class="{{ $label }}">Keterangan</label>
        <textarea name="description" rows="4" maxlength="5000" class="{{ $input }}">{{ old('description') }}</textarea>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="{{ $label }}">Kategori</label>
            <select name="category" class="{{ $input }}">
                @foreach($categories as $k => $v)<option value="{{ $k }}" @selected(old('category', 'technical') === $k)>{{ $v }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="{{ $label }}">Prioritas</label>
            <select name="priority" class="{{ $input }}">
                @foreach($priorities as $k => $v)<option value="{{ $k }}" @selected(old('priority', 'medium') === $k)>{{ $v }}</option>@endforeach
            </select>
        </div>
    </div>
    <button class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest">Simpan Tiket</button>
</form>
@endsection
