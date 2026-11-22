@extends('layouts.admin')

@section('content')
<div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-rose-100 p-8">
    <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path></svg>
        </div>
        <div class="min-w-0">
            <h2 class="text-lg font-bold text-slate-800">Data jaringan tidak bisa dimuat</h2>
            <p class="text-sm text-slate-600 mt-2 break-words">{{ $message }}</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ url()->current() }}" class="bg-slate-900 hover:bg-slate-700 text-white px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest">Coba Lagi</a>
                <a href="{{ route('dashboard') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest">Dashboard</a>
            </div>
        </div>
    </div>
</div>
@endsection
