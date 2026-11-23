@extends('layouts.mobile')

@section('content')
@php $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.'); @endphp

<div class="flex items-center justify-between mb-5 px-1">
    <div>
        <h3 class="font-bold text-slate-800 text-xl tracking-tight">Daftar Tagih</h3>
        <p class="text-xs text-slate-400 mt-0.5">{{ $customers->count() }} pelanggan · {{ $rp($customers->sum('total')) }}</p>
    </div>
    <a href="{{ route('mobile.home') }}" class="text-xs font-bold text-sky-600">Kembali</a>
</div>

@if($lastClosing)
    <div class="mb-5 px-4 py-3 rounded-2xl text-xs font-semibold {{ $lastClosing->difference == 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-800' }}">
        Tutup kas terakhir {{ $lastClosing->date->format('d/m/Y') }}:
        {{ $lastClosing->difference == 0 ? 'cocok' : 'selisih ' . $rp($lastClosing->difference) }}
    </div>
@endif

<div class="space-y-3">
    @forelse($customers as $row)
        @php $c = $row->customer; @endphp
        <div class="bg-white p-4 rounded-[1.5rem] shadow-sm border border-slate-100">
            <div class="flex justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-bold text-slate-800 text-[15px] truncate">{{ $c->name }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $c->displayCode() }}</p>
                    @if($c->address)<p class="text-xs text-slate-500 mt-1">{{ $c->address }}</p>@endif
                    <p class="text-[11px] text-slate-400 mt-1">{{ $row->invoices->map(fn ($i) => \Carbon\Carbon::parse($i->period_date)->locale('id')->translatedFormat('M y'))->implode(', ') }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="font-bold text-slate-900">{{ $rp($row->total) }}</p>
                    <p class="text-[10px] font-bold uppercase tracking-wider {{ $row->invoices->count() > 1 ? 'text-rose-500' : 'text-orange-500' }}">{{ $row->invoices->count() }} bulan</p>
                </div>
            </div>
            <div class="flex gap-2 mt-3">
                <a href="{{ route('mobile.deposits.create', ['customer_id' => $c->id, 'amount' => (int) $row->invoices->first()->amount]) }}" class="flex-1 text-center py-2 rounded-xl bg-emerald-600 text-white text-sm font-bold">Setor</a>
                @if($c->phone)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', preg_replace('/^0/', '62', $c->phone)) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold">WA</a>
                @endif
                @if($c->mapUrl())
                    <a href="{{ $c->mapUrl() }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold">Peta</a>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-16 bg-white rounded-[2.5rem] border border-dashed border-slate-200">
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em]">Tidak ada tagihan</p>
            <p class="text-xs text-slate-300 mt-2">Belum ada pelanggan yang ditugaskan, atau semua sudah lunas.</p>
        </div>
    @endforelse
</div>
@endsection
