@extends('layouts.admin')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">📋 Log Sistem (500 Terakhir)</h1>
        <div class="flex space-x-2">
            <button onclick="switchTab('activity')" id="btn-activity" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">Log Aktivitas</button>
            <button onclick="switchTab('error')" id="btn-error" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition">Log Error</button>
        </div>
    </div>

    <!-- Activity Log Tab -->
    <div id="tab-activity" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50 flex justify-between items-center">
            <h2 class="font-bold text-slate-800">Aktivitas Real-time</h2>
            <span class="text-[10px] bg-sky-50 text-sky-600 px-3 py-1 rounded-full font-black uppercase tracking-widest">Logging On</span>
        </div>
        
        <!-- Desktop Table view (hidden on mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-widest border-b">
                    <tr>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Petugas</th>
                        <th class="px-6 py-4">Aksi / Aktivitas</th>
                        <th class="px-6 py-4">Detail Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($activityLogs as $log)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-xs font-bold text-slate-400">{{ $log->created_at->format('d M H:i:s') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center font-bold text-xs uppercase">{{ substr($log->user->name ?? '?', 0, 1) }}</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700 leading-none">{{ $log->user->name ?? 'System' }}</p>
                                    <p class="text-[9px] text-slate-400 uppercase font-black mt-1">{{ $log->user->role ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold text-slate-600">{{ $log->activity }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->payload && count($log->payload) > 0)
                                <div x-data="{ open: false }">
                                    <button @click="open = !open" class="text-[10px] text-sky-600 font-bold hover:underline">Lihat Data &raquo;</button>
                                    <pre x-show="open" class="text-[9px] bg-slate-900 text-slate-400 p-2 rounded-lg mt-2 overflow-x-auto max-w-[200px]">{{ json_encode($log->payload, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            @else
                                <span class="text-[10px] text-slate-300 italic">No Data</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-4xl mb-4">🔍</span>
                                <p class="text-slate-400 text-sm font-bold">Belum ada riwayat aktivitas dicatat.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile View (Vertical Cards) -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($activityLogs as $log)
                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] text-slate-400 font-bold border rounded-lg px-2 py-0.5">{{ $log->created_at->format('d M, H:i') }}</span>
                        <span class="text-[9px] bg-sky-100 text-sky-600 font-black uppercase tracking-widest px-2 py-0.5 rounded-lg">{{ $log->user->role ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center font-bold">{{ substr($log->user->name ?? '?', 0, 1) }}</div>
                        <p class="font-black text-slate-700 text-sm">{{ $log->user->name ?? 'System' }}</p>
                    </div>
                    <p class="text-sm font-bold text-slate-500 bg-slate-50 p-3 rounded-2xl mb-2">{{ $log->activity }}</p>
                    @if($log->payload && count($log->payload) > 0)
                        <details class="bg-slate-900 rounded-xl p-3">
                            <summary class="text-[9px] text-sky-400 font-black uppercase cursor-pointer">Lihat Payload</summary>
                            <pre class="text-[8px] text-slate-400 mt-2 overflow-x-auto">{{ json_encode($log->payload, JSON_PRETTY_PRINT) }}</pre>
                        </details>
                    @endif
                </div>
            @empty
                <div class="p-10 text-center text-slate-400 italic text-xs">Belum ada riwayat.</div>
            @endforelse
        </div>
    </div>

    <!-- Error Log Tab (Hidden Initially) -->
    <div id="tab-error" class="bg-gray-900 rounded-xl shadow-lg border border-gray-800 hidden overflow-hidden">
        <div class="p-4 bg-gray-800 flex justify-between items-center border-b border-gray-700">
            <h2 class="font-semibold text-red-400 font-mono">System Error Log (laravel.log)</h2>
            <span class="text-xs text-gray-400">Total lines: {{ count($errorLogs) }}</span>
        </div>
        <div class="p-6 overflow-y-auto max-h-[600px] font-mono text-xs leading-relaxed text-gray-300">
            @forelse($errorLogs as $log)
            <div class="mb-4 pb-4 border-b border-gray-800 last:border-0">
                @if(strpos($log, '.ERROR:') !== false)
                    <pre class="text-red-400 whitespace-pre-wrap">{{ $log }}</pre>
                @else
                    <pre class="whitespace-pre-wrap">{{ $log }}</pre>
                @endif
            </div>
            @empty
            <div class="text-center py-10 text-gray-600">Tidak ada log error yang terekam.</div>
            @endforelse
        </div>
    </div>
</div>

<script>
    function switchTab(tab) {
        if (tab === 'activity') {
            document.getElementById('tab-activity').classList.remove('hidden');
            document.getElementById('tab-error').classList.add('hidden');
            document.getElementById('btn-activity').classList.add('bg-blue-600', 'text-white');
            document.getElementById('btn-activity').classList.remove('bg-gray-200', 'text-gray-800');
            document.getElementById('btn-error').classList.add('bg-gray-200', 'text-gray-800');
            document.getElementById('btn-error').classList.remove('bg-blue-600', 'text-white');
        } else {
            document.getElementById('tab-error').classList.remove('hidden');
            document.getElementById('tab-activity').classList.add('hidden');
            document.getElementById('btn-error').classList.add('bg-blue-600', 'text-white');
            document.getElementById('btn-error').classList.remove('bg-gray-200', 'text-gray-800');
            document.getElementById('btn-activity').classList.add('bg-gray-200', 'text-gray-800');
            document.getElementById('btn-activity').classList.remove('bg-blue-600', 'text-white');
        }
    }
</script>
@endsection
