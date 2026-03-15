@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-3 mb-2">
        <div class="bg-sky-100 p-2 rounded-xl text-sky-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
            </svg>
        </div>
        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Analisa Voucher</h2>
    </div>
    <p class="text-slate-500 font-medium">Melihat trend penjualan voucher dan reseller paling aktif.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- TREND PENJUALAN -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-slate-700">Trend Penjualan (6 Bulan Terakhir)</h3>
                <span class="text-[10px] font-black text-sky-600 uppercase tracking-widest bg-sky-50 px-2 py-1 rounded-md">Voucher Revenue</span>
            </div>
            <div class="p-8">
                <div class="flex items-end gap-2 h-64">
                    @php
                        $maxAmount = $voucherTrend->max('total_amount') ?: 1;
                    @endphp
                    @forelse($voucherTrend as $trend)
                        <div class="flex-1 flex flex-col items-center group">
                            <div class="relative w-full flex flex-col items-center">
                                <!-- Tooltip -->
                                <div class="absolute -top-12 opacity-0 group-hover:opacity-100 transition-all bg-slate-800 text-white text-[10px] py-1 px-2 rounded-lg whitespace-nowrap z-10 font-bold mb-2">
                                    Rp {{ number_format($trend->total_amount, 0, ',', '.') }}
                                </div>
                                <!-- Bar -->
                                <div class="w-full bg-sky-500 rounded-t-xl transition-all group-hover:bg-sky-600 group-hover:shadow-lg group-hover:shadow-sky-200" style="height: {{ ($trend->total_amount / $maxAmount) * 200 }}px"></div>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 mt-3 uppercase tracking-tight">{{ date('M Y', strtotime($trend->month . '-01')) }}</span>
                            <span class="text-[9px] font-black text-slate-500">{{ $trend->transaction_count }} trx</span>
                        </div>
                    @empty
                        <div class="w-full text-center py-20 text-slate-400 italic font-medium">Belum ada data transaksi voucher.</div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <div class="mt-8 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50 bg-slate-50/50">
                <h3 class="font-bold text-slate-700">Detail Bulanan</h3>
            </div>
            <table class="w-full text-left">
                <thead class="bg-slate-50/30 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <tr>
                        <th class="px-8 py-4">Bulan</th>
                        <th class="px-8 py-4">Total Transaksi</th>
                        <th class="px-8 py-4 text-right">Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($voucherTrend->reverse() as $trend)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-8 py-4 font-bold text-slate-700">{{ date('F Y', strtotime($trend->month . '-01')) }}</td>
                        <td class="px-8 py-4">
                            <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded-md text-xs font-bold">{{ $trend->transaction_count }} Transaksi</span>
                        </td>
                        <td class="px-8 py-4 text-right font-black text-sky-600">Rp {{ number_format($trend->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </tbody>
            </table>
        </div>
    </div>

    <!-- TOP RESELLERS -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50 bg-gradient-to-r from-sky-600 to-sky-700">
                <h3 class="font-bold text-white">Top 10 Reseller Voucher</h3>
                <p class="text-sky-100 text-xs mt-1">Berdasarkan total nominal pembelian.</p>
            </div>
            <div class="p-4">
                <div class="space-y-3">
                    @forelse($topResellers as $index => $reseller)
                        <div class="flex items-center p-3 rounded-2xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100 group">
                            <div class="w-10 h-10 rounded-xl {{ $index < 3 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center font-black mr-4 shadow-sm">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-slate-800 group-hover:text-sky-700 transition">{{ $reseller->customer->name ?? 'Unknown' }}</h4>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">{{ $reseller->total_vouchers }} Transaksi</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black text-slate-700 group-hover:text-slate-900">Rp{{ number_format($reseller->total_spent, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-slate-400 italic">Data belum tersedia.</div>
                    @endforelse
                </div>
            </div>
            <div class="p-6 bg-slate-50 border-t border-slate-100">
                <div class="flex items-center gap-2 text-slate-500 text-[10px] font-bold italic">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Data dihitung dari akun {{ $voucherAccount->name }} ({{ $voucherAccount->code }})
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
