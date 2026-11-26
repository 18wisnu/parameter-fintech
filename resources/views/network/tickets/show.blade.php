@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => $ticket['subject'] ?? 'Tiket',
    'subtitle' => ($ticket['ticket_code'] ?? '') . ' · ' . ($ticket['customer_name'] ?? '') . (!empty($ticket['customer_email']) ? ' (' . $ticket['customer_email'] . ')' : ''),
    'actions' => [['← Daftar Tiket', route('network.tickets.index')]],
])

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <p class="text-xs font-semibold text-slate-400 mb-2">Keluhan · {{ $ticket['created_at'] ?? '' }}</p>
            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $ticket['description'] ?? '(tanpa keterangan)' }}</p>
        </div>

        @foreach($ticket['replies'] ?? [] as $r)
        @php $staff = ($r['reply_type'] ?? '') === 'staff'; @endphp
        <div class="rounded-2xl border p-5 {{ $staff ? 'bg-sky-50 border-sky-100 ml-6' : 'bg-white border-slate-100 mr-6' }}">
            <p class="text-xs font-semibold {{ $staff ? 'text-sky-600' : 'text-slate-400' }} mb-1">{{ $r['reply_by'] ?? ($staff ? 'Petugas' : 'Pelanggan') }} · {{ $r['created_at'] ?? '' }}</p>
            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $r['message'] ?? '' }}</p>
        </div>
        @endforeach

        <form action="{{ route('network.tickets.reply', $ticket['id']) }}" method="POST" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-3">
            @csrf
            <label class="block text-[10px] font-bold text-slate-400">Balas sebagai petugas</label>
            <textarea name="message" rows="4" required maxlength="5000" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm" placeholder="Tulis balasan…">{{ old('message') }}</textarea>
            <button class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold">Kirim Balasan</button>
            @if(in_array($ticket['status'] ?? '', ['closed', 'resolved']))<p class="text-xs text-slate-400">Membalas tiket yang sudah ditutup akan membukanya kembali.</p>@endif
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 h-fit">
        <dl class="space-y-4 text-sm">
            <div><dt class="text-xs font-semibold text-slate-400">Status</dt><dd class="mt-1">@include('network.partials.badge', ['value' => $ticket['status'] ?? null, 'labels' => $statuses])</dd></div>
            <div><dt class="text-xs font-semibold text-slate-400">Prioritas</dt><dd class="mt-1">@include('network.partials.badge', ['value' => $ticket['priority'] ?? null, 'labels' => $priorities])</dd></div>
            <div><dt class="text-xs font-semibold text-slate-400">Kategori</dt><dd class="mt-1 font-bold text-slate-700">{{ $categories[$ticket['category'] ?? ''] ?? '-' }}</dd></div>
            <div><dt class="text-xs font-semibold text-slate-400">Petugas</dt><dd class="mt-1 font-bold text-slate-700">{{ $ticket['assigned_name'] ?? '-' }}</dd></div>
            <div><dt class="text-xs font-semibold text-slate-400">Diperbarui</dt><dd class="mt-1 text-slate-600">{{ $ticket['updated_at'] ?? '-' }}</dd></div>
            @if(!empty($ticket['closed_at']))<div><dt class="text-xs font-semibold text-slate-400">Ditutup</dt><dd class="mt-1 text-slate-600">{{ $ticket['closed_at'] }}</dd></div>@endif
        </dl>
    </div>
</div>
@endsection
