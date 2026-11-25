@extends('layouts.admin')

@section('content')
@include('partials.flash')
@include('platform._nav', ['title' => 'Pembayaran Langganan'])
@php $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.'); @endphp

<div class="flex gap-2 mb-4 text-sm">
    @foreach($statuses as $k => $l)
        <a href="{{ route('platform.payments.index', ['status' => $k]) }}" class="px-3 py-1.5 rounded-xl {{ $status === $k ? 'bg-slate-800 text-white font-semibold' : 'bg-white border border-slate-200 text-slate-600' }}">{{ $l }}</a>
    @endforeach
</div>

<div class="space-y-3">
    @forelse($payments as $pay)
        <div class="bg-white rounded-2xl border border-slate-100 p-4 flex flex-wrap gap-4 justify-between">
            <div class="text-sm">
                <a href="{{ route('platform.tenants.show', $pay->business) }}" class="font-bold text-sky-700 hover:underline">{{ $pay->business->name }}</a>
                <div class="text-slate-700">{{ $pay->plan->name ?? '-' }} · {{ $pay->months }} bulan · <b>{{ $rp($pay->amount) }}</b></div>
                <div class="text-xs text-slate-400">Dikirim {{ $pay->created_at->format('d/m/Y H:i') }} oleh {{ $pay->submitter->name ?? '-' }}
                    @if($pay->notes) · "{{ $pay->notes }}" @endif
                    @if($pay->admin_notes) · {{ $pay->admin_notes }} @endif
                    @if($pay->period_end) · berlaku s/d {{ $pay->period_end->format('d/m/Y') }} @endif
                </div>
                @if($pay->proof_image)
                    <a href="{{ asset('storage/' . $pay->proof_image) }}" target="_blank" class="inline-block mt-1 text-xs font-semibold text-sky-600 hover:underline">Lihat bukti transfer</a>
                @endif
            </div>
            @if($pay->status === 'pending')
                <div class="flex flex-wrap items-start gap-2">
                    <form method="POST" action="{{ route('platform.payments.confirm', $pay) }}" onsubmit="return confirm('Konfirmasi pembayaran ini? Langganan langsung diperpanjang.')">
                        @csrf
                        <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-bold">Konfirmasi</button>
                    </form>
                    <form method="POST" action="{{ route('platform.payments.reject', $pay) }}" class="flex gap-2">
                        @csrf
                        <input type="text" name="admin_notes" required maxlength="255" placeholder="Alasan ditolak" class="py-2 px-3 rounded-xl border-slate-200 text-sm">
                        <button class="px-4 py-2 rounded-xl bg-rose-50 text-rose-700 text-sm font-bold">Tolak</button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-10 text-center text-sm text-slate-400">Tidak ada pembayaran.</div>
    @endforelse
</div>
<div class="mt-4">{{ $payments->links() }}</div>
@endsection
