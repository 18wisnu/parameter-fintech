@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => 'Batch ' . $batch->batch_code,
    'subtitle' => $batch->quantity . ' voucher · ' . $batch->product_name . ' · Rp ' . number_format($batch->price, 0, ',', '.') . ' · Area: ' . ($batch->restriction_name ?? 'Global'),
    'actions' => [
        ['Arsip Batch', route('network.vouchers.batches')],
        ['Lihat Status di Jaringan', route('network.vouchers.index', ['batch_code' => $batch->batch_code])],
        ['Cetak Voucher', route('network.vouchers.batches.print', $batch), true],
    ],
])

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @foreach($batch->vouchers as $v)
        <div class="border border-dashed border-slate-300 rounded-xl p-3 text-center">
            <div class="font-mono font-black text-slate-800 tracking-wider">{{ $v['code'] }}</div>
            @if($batch->login_method === 'user_pass' && !empty($v['password']))
                <div class="font-mono text-xs text-slate-500 mt-1">pass: {{ $v['password'] }}</div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection
