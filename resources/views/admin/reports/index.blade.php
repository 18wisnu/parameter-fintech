@extends('layouts.admin')

@section('content')

    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Laporan Bagi Hasil & Keuangan</h2>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Rincian pendapatan, pengeluaran, dana cadangan dan dividen.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <div class="bg-white p-2 rounded-2xl shadow-sm border border-slate-200">
                        <form action="{{ route('reports.index') }}" method="GET" class="flex items-center space-x-2">
                            <label class="font-bold text-slate-600 text-xs ml-2">Periode:</label>
                            <select name="month" class="text-sm border-none focus:ring-0 font-bold text-slate-700 cursor-pointer">
                                @for($i=1; $i<=12; $i++)
                                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                        {{ \DateTime::createFromFormat('!m', $i)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                            <select name="year" class="text-sm border-none focus:ring-0 font-bold text-slate-700 cursor-pointer">
                                <option value="2025" {{ $year == 2025 ? 'selected' : '' }}>2025</option>
                                <option value="2026" {{ $year == 2026 ? 'selected' : '' }}>2026</option>
                            </select>
                            <button type="submit" class="bg-sky-600 text-white px-4 py-1.5 rounded-xl text-sm hover:bg-sky-700 font-bold">Lihat</button>
                        </form>
                    </div>

                    <a href="{{ route('reports.export-excel', ['month' => $month, 'year' => $year]) }}" 
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 rounded-2xl text-sm font-bold shadow-md shadow-emerald-100 transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Cetak ke XLS (Excel)
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                
                <div class="space-y-4">
                    <!-- Card Pendapatan -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-slate-400 text-xs font-black uppercase tracking-wider">Total Pendapatan</p>
                            <span class="text-xs px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg font-bold">Revenue</span>
                        </div>
                        <h3 class="text-3xl font-black text-emerald-600 mb-4">+ Rp {{ number_format($revenue, 0, ',', '.') }}</h3>
                        
                        <div class="pt-3 border-t border-slate-100 space-y-2 text-xs">
                            <div class="flex justify-between items-center text-slate-600">
                                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-sky-500"></span> Pendapatan Voucher Hotspot:</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($voucherIncome, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600">
                                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Pendapatan PPPoE (Langganan):</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($pppoeIncome, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600">
                                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Pemasangan Baru (Registrasi):</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($pasangBaruIncome, 0, ',', '.') }}</span>
                            </div>
                            @if($otherIncome > 0)
                            <div class="flex justify-between items-center text-slate-600">
                                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-400"></span> Pendapatan Lain-lain:</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($otherIncome, 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card Pengeluaran -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-slate-400 text-xs font-black uppercase tracking-wider">Total Pengeluaran</p>
                            <span class="text-xs px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg font-bold">Expense</span>
                        </div>
                        <h3 class="text-3xl font-black text-rose-600 mb-4">- Rp {{ number_format($expense, 0, ',', '.') }}</h3>
                        
                        <div class="pt-3 border-t border-slate-100 space-y-2 text-xs">
                            <div class="flex justify-between items-center text-slate-600">
                                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Tagihan (Bandwidth / Listrik / Ops):</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($tagihanExpense, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600">
                                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-purple-500"></span> Gaji Pegawai & Teknisi:</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($gajiExpense, 0, ',', '.') }}</span>
                            </div>
                            @if($otherExpense > 0)
                            <div class="flex justify-between items-center text-slate-600">
                                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-400"></span> Pengeluaran Lain-lain:</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($otherExpense, 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card Laba Bersih -->
                    <div class="bg-slate-900 p-6 rounded-2xl shadow-xl text-white border border-slate-800">
                        <p class="text-slate-400 text-xs font-black uppercase tracking-wider">Laba Bersih Operasional (Net Profit)</p>
                        <h3 class="text-3xl font-black text-sky-400 mt-2">Rp {{ number_format($netProfit, 0, ',', '.') }}</h3>
                        <p class="text-[11px] text-slate-400 mt-2">Dihitung dari Total Pendapatan dikurangi Total Pengeluaran riil.</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold mb-4 border-b pb-4 text-slate-800">Simulasi Pembagian</h3>
                    
                    @if($netProfit > 0)
                        <div class="space-y-4">
                            <div class="flex justify-between items-center text-sm p-3 bg-orange-50 rounded-xl border border-orange-100">
                                <span class="font-bold text-orange-800">Dana Cadangan ({{ $reservePercentage }}%)</span>
                                <span class="font-bold text-orange-700">Rp {{ number_format($reserveFund, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs text-slate-400 px-2 font-bold uppercase">
                                <span>Sisa Dibagi Dividen</span>
                                <span>Rp {{ number_format($distributable, 0, ',', '.') }}</span>
                            </div>

                            <div class="pt-2 space-y-2">
                                <p class="text-xs text-slate-500 font-bold mb-1 ml-1">JATAH PEMILIK</p>
                                
                                @foreach($shares as $index => $share)
                                    @php
                                        $colors = ['blue', 'purple', 'emerald', 'amber', 'rose', 'sky'];
                                        $color = $colors[$index % count($colors)];
                                    @endphp
                                    <div class="flex justify-between items-center bg-{{ $color }}-50 p-4 rounded-xl border border-{{ $color }}-100">
                                        <span class="font-bold text-{{ $color }}-800">{{ $share['name'] }} ({{ $share['percentage'] }}%)</span>
                                        <span class="font-bold text-{{ $color }}-700 text-lg">Rp {{ number_format($share['amount'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <form action="{{ route('reports.store') }}" method="POST" class="mt-6">
                                @csrf
                                <input type="hidden" name="month" value="{{ $month }}">
                                <input type="hidden" name="year" value="{{ $year }}">
                                <input type="hidden" name="revenue" value="{{ $revenue }}">
                                <input type="hidden" name="expense" value="{{ $expense }}">
                                <input type="hidden" name="net_profit" value="{{ $netProfit }}">
                                <input type="hidden" name="reserve_fund" value="{{ $reserveFund }}">
                                <input type="hidden" name="distributable" value="{{ $distributable }}">
                                <input type="hidden" name="share_a" value="{{ $shareA }}">
                                <input type="hidden" name="share_b" value="{{ $shareB }}">
                                <input type="hidden" name="shares_details" value="{{ json_encode($shares) }}">
                                
                                <button type="submit" class="w-full bg-slate-900 text-white py-3 rounded-xl hover:bg-black font-bold shadow-lg transition-transform active:scale-95" onclick="return confirm('Simpan laporan ini? Data akan terkunci di riwayat.')">
                                    💾 Simpan / Kunci Laporan Ini
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="text-center py-10 text-rose-500 font-bold">
                            Tidak ada laba untuk dibagi bulan ini.
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-slate-100 p-6">
                <h3 class="font-bold text-lg text-slate-800 mb-4">Arsip Laporan Tersimpan</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-500">
                        <thead class="bg-slate-50 uppercase text-xs font-bold text-slate-400">
                            <tr>
                                <th class="px-6 py-4">Periode</th>
                                <th class="px-6 py-4">Laba Bersih</th>
                                <th class="px-6 py-4">Detail Distribusi</th>
                                <th class="px-6 py-4 text-center">Disimpan Pada</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($history as $h)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-700">{{ $h->period->format('F Y') }}</td>
                                <td class="px-6 py-4 font-bold text-emerald-600">Rp {{ number_format($h->net_profit, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @if($h->shares_details)
                                            @foreach($h->shares_details as $s)
                                                <span class="text-[10px] bg-slate-100 px-2 py-1 rounded-md font-bold text-slate-600">
                                                    {{ $s['name'] }}: Rp{{ number_format($s['amount'], 0, ',', '.') }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-[10px] bg-blue-50 px-2 py-1 rounded-md font-bold text-blue-600">
                                                Junaidi-Eka: Rp{{ number_format($h->share_group_a, 0, ',', '.') }}
                                            </span>
                                            <span class="text-[10px] bg-purple-50 px-2 py-1 rounded-md font-bold text-purple-600">
                                                Bagus: Rp{{ number_format($h->share_group_b, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-400 text-center">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

@endsection