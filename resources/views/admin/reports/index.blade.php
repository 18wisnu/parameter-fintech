@extends('layouts.admin')

@section('content')

    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">Laporan Bagi Hasil</h2>
                
                <div class="bg-white p-2 rounded-lg shadow-sm border border-slate-200">
                    <form action="{{ route('reports.index') }}" method="GET" class="flex items-center space-x-2">
                        <label class="font-bold text-slate-600 text-sm ml-2">Periode:</label>
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
                        <button type="submit" class="bg-sky-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-sky-700 font-bold">Lihat</button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                
                <div class="space-y-4">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-emerald-500">
                        <p class="text-slate-500 text-xs font-bold uppercase">Total Pendapatan</p>
                        <h3 class="text-2xl font-bold text-emerald-600">+ Rp {{ number_format($revenue, 0, ',', '.') }}</h3>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-rose-500">
                        <p class="text-slate-500 text-xs font-bold uppercase">Total Pengeluaran</p>
                        <h3 class="text-2xl font-bold text-rose-600">- Rp {{ number_format($expense, 0, ',', '.') }}</h3>
                    </div>
                    <div class="bg-slate-800 p-6 rounded-2xl shadow-lg text-white">
                        <p class="opacity-75 text-xs font-bold uppercase">Laba Bersih (Net Profit)</p>
                        <h3 class="text-3xl font-bold mt-1">Rp {{ number_format($netProfit, 0, ',', '.') }}</h3>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold mb-4 border-b pb-4 text-slate-800">Simulasi Pembagian</h3>
                    
                    @if($netProfit > 0)
                        <div class="space-y-4">
                            <div class="flex justify-between items-center text-sm p-3 bg-orange-50 rounded-xl border border-orange-100">
                                <span class="font-bold text-orange-800">Dana Cadangan (10%)</span>
                                <span class="font-bold text-orange-700">Rp {{ number_format($reserveFund, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex justify-between items-center text-xs text-slate-400 px-2 font-bold uppercase">
                                <span>Sisa Dibagi Dividen</span>
                                <span>Rp {{ number_format($distributable, 0, ',', '.') }}</span>
                            </div>

                            <div class="pt-2 space-y-2">
                                <p class="text-xs text-slate-500 font-bold mb-1 ml-1">JATAH PEMILIK</p>
                                
                                <div class="flex justify-between items-center bg-blue-50 p-4 rounded-xl border border-blue-100">
                                    <span class="font-bold text-blue-800">Junaidi & Eka (60%)</span>
                                    <span class="font-bold text-blue-700 text-lg">Rp {{ number_format($shareA, 0, ',', '.') }}</span>
                                </div>
                                
                                <div class="flex justify-between items-center bg-purple-50 p-4 rounded-xl border border-purple-100">
                                    <span class="font-bold text-purple-800">Bagus (40%)</span>
                                    <span class="font-bold text-purple-700 text-lg">Rp {{ number_format($shareB, 0, ',', '.') }}</span>
                                </div>
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
                                <th class="px-6 py-4">Jatah Junaidi-Eka</th>
                                <th class="px-6 py-4">Jatah Bagus</th>
                                <th class="px-6 py-4">Disimpan Pada</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($history as $h)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-700">{{ $h->period->format('F Y') }}</td>
                                <td class="px-6 py-4 font-bold text-emerald-600">Rp {{ number_format($h->net_profit, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 font-medium text-blue-600">Rp {{ number_format($h->share_group_a, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 font-medium text-purple-600">Rp {{ number_format($h->share_group_b, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-xs text-slate-400">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

@endsection