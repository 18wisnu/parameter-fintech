@extends('layouts.mobile')

@section('content')
    <div class="mb-6 flex justify-between items-center px-1">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Semua Riwayat</h2>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.1em] mt-1">Daftar setoran dana Anda</p>
        </div>
        <div class="w-12 h-12 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center text-xl">
            📜
        </div>
    </div>

    <div class="space-y-4 mb-10">
        @forelse($riwayat as $deposit)
            <div class="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 flex justify-between items-center active:scale-[0.98] transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl {{ $deposit->status == 'approved' ? 'bg-emerald-50 text-emerald-500' : 'bg-orange-50 text-orange-500' }} flex items-center justify-center shadow-inner">
                        @if($deposit->status == 'approved')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @endif
                    </div>
                    <div>
                        <p class="font-black text-slate-800 text-[14px] leading-tight">{{ $deposit->description }}</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="text-[9px] text-slate-400 font-bold uppercase">{{ $deposit->created_at->format('d M, H:i') }}</span>
                            <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                            @if($deposit->status == 'pending')
                                <span class="text-[9px] text-orange-500 font-bold uppercase">Pending</span>
                            @elseif($deposit->status == 'approved')
                                <span class="text-[9px] text-emerald-500 font-bold uppercase">Disetujui</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-black text-slate-900 text-sm">Rp {{ number_format($deposit->amount, 0, ',', '.') }}</p>
                </div>
            </div>
        @empty
            <div class="text-center py-20 bg-white rounded-[2.5rem] border border-dashed border-slate-200">
                <p class="text-slate-400 text-sm">Belum ada riwayat setoran.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $riwayat->links() }}
    </div>
@endsection
