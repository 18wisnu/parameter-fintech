@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('customers.show', $customer) }}" class="text-sm text-slate-500 hover:text-slate-800">← {{ $customer->name }}</a>
    <h2 class="text-2xl font-bold text-slate-800 mt-2 mb-5">Edit Pelanggan</h2>

    <form action="{{ route('customers.update', $customer) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.customers._form')

        <div class="flex justify-end gap-2 mt-5">
            <a href="{{ route('customers.show', $customer) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600">Batal</a>
            <button class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
