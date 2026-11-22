{{-- @include('network.partials.badge', ['value' => 'active', 'labels' => [...]]) --}}
@php
    $tone = match ($value) {
        'active', 'resolved', 'completed', 'installed', 'paid' => 'bg-emerald-100 text-emerald-700',
        'unused', 'new', 'open', 'scheduled', 'pending' => 'bg-sky-100 text-sky-700',
        'in_progress' => 'bg-indigo-100 text-indigo-700',
        'suspended', 'expired', 'high', 'overdue' => 'bg-amber-100 text-amber-700',
        'disabled', 'inactive', 'terminated', 'rejected', 'cancelled', 'critical', 'closed' => 'bg-rose-100 text-rose-700',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp
<span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider whitespace-nowrap {{ $tone }}">{{ $labels[$value] ?? ($value ?? '-') }}</span>
