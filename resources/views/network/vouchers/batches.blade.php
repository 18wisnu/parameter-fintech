@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => 'Arsip Batch Voucher',
    'subtitle' => 'Batch yang dibuat dari aplikasi ini — bisa dicetak ulang kapan saja.',
    'actions' => [['Daftar Voucher', route('network.vouchers.index')], ['+ Buat Voucher', route('network.vouchers.create'), true]],
])

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs font-semibold text-slate-400">
                <tr>
                    <th class="px-4 py-3 text-left">Batch</th>
                    <th class="px-4 py-3 text-left">Paket</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3 text-right">Nilai</th>
                    <th class="px-4 py-3 text-left">Dibuat</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($batches as $b)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-mono text-xs font-bold text-slate-700">{{ $b->batch_code }}
                        @if($b->notes)<div class="font-sans font-normal text-slate-400">{{ $b->notes }}</div>@endif</td>
                    <td class="px-4 py-3 text-slate-600">{{ $b->product_name }}</td>
                    <td class="px-4 py-3 text-right font-bold">{{ $b->quantity }}</td>
                    <td class="px-4 py-3 text-right font-bold text-slate-700 whitespace-nowrap">Rp {{ number_format($b->price * $b->quantity, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $b->created_at->format('d M Y H:i') }}<br>{{ $b->creator->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('network.vouchers.batches.show', $b) }}" class="text-sky-600 font-bold text-xs hover:underline">Lihat</a>
                        <a href="{{ route('network.vouchers.batches.print', $b) }}" target="_blank" class="ml-3 text-slate-600 font-bold text-xs hover:underline">Cetak</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400 text-sm">Belum ada batch.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-50">{{ $batches->links() }}</div>
</div>
@endsection
