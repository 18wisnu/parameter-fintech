@extends('layouts.admin')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-3xl font-bold text-slate-800">Manajemen Gaji & Kerja</h2>
        <p class="text-slate-500">Kelola gaji pokok dan hitung bonus aktivasi staff lapangan.</p>
    </div>
    
    <div class="flex gap-2">
        <form action="{{ route('admin.salaries.generate') }}" method="POST" class="flex items-center gap-2 bg-white p-2 rounded-xl shadow-sm border border-slate-100">
            @csrf
            <input type="month" name="month" value="{{ date('Y-m') }}" class="border-none focus:ring-0 text-slate-700 font-bold">
            <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg font-bold transition">
                Generate Gaji
            </button>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="bg-emerald-500 text-white p-4 rounded-xl mb-6 shadow-lg animate-fade-in-down">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Pengaturan Gaji Pokok -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50 bg-slate-50/50">
                <h3 class="font-bold text-slate-800">Set Gaji Pokok Staff</h3>
            </div>
            <div class="p-6">
                <div class="space-y-6">
                    @foreach($users as $user)
                        <form action="{{ route('admin.salaries.update-base', $user->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-bold text-slate-600 flex justify-between">
                                    <span>{{ $user->name }}</span>
                                    <span class="text-xs px-2 py-0.5 bg-slate-100 rounded text-slate-500 uppercase">{{ $user->role }}</span>
                                </label>
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <span class="absolute left-3 top-2.5 text-slate-400 text-sm">Rp</span>
                                        <input type="number" name="base_salary" value="{{ (int)$user->base_salary }}" class="w-full pl-9 pr-3 py-2 rounded-lg border-slate-200 focus:border-sky-500 text-sm font-bold" placeholder="0">
                                    </div>
                                    <button type="submit" class="bg-slate-800 text-white px-3 py-2 rounded-lg hover:bg-slate-700 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Riwayat Gaji -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Daftar Payroll</h3>
                <span class="text-xs text-slate-400 font-medium">Berdasarkan bulan yang digenerate</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Staff / Bulan</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Gaji Pokok</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Bonus Aktivasi</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($salaries as $salary)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-slate-700">{{ $salary->user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($salary->month)->format('F Y') }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 font-medium">Rp {{ number_format($salary->base_salary, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-emerald-600 font-bold">+Rp {{ number_format($salary->activation_bonus, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-900 font-black">Rp {{ number_format($salary->total_amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($salary->status == 'paid')
                                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold border border-emerald-200">Terbayar</span>
                                    @else
                                        <span class="px-3 py-1 bg-rose-100 text-rose-700 rounded-full text-xs font-bold border border-rose-200">Belum Bayar</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($salary->status == 'unpaid')
                                        <form action="{{ route('admin.salaries.pay', $salary->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Tandai sebagai sudah dibayar?')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                                Bayar Sekarang
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400 italic">{{ $salary->paid_at->format('d/m/Y H:i') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 mb-2 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <p>Belum ada data gaji yang digenerate.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($salaries->hasPages())
                <div class="p-6 border-t border-slate-50">
                    {{ $salaries->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
