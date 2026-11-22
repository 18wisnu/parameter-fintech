@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('customers.index') }}" class="text-sm text-slate-500 hover:text-slate-800">← Pelanggan</a>
    <h2 class="text-2xl font-bold text-slate-800 mt-2 mb-5">Pelanggan Baru</h2>

    <form action="{{ route('customers.store') }}" method="POST">
        @csrf
        @include('admin.customers._form')

        <div class="flex justify-end gap-2 mt-5">
            <a href="{{ route('customers.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600">Batal</a>
            <button class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">Simpan Pelanggan</button>
        </div>
    </form>
</div>
@endsection
