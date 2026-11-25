@php
    $cls = [
        'awaiting_payment' => 'bg-amber-50 text-amber-700',
        'pending' => 'bg-sky-50 text-sky-700',
        'confirmed' => 'bg-emerald-50 text-emerald-700',
        'rejected' => 'bg-rose-50 text-rose-700',
        'cancelled' => 'bg-slate-100 text-slate-500',
    ][$s] ?? 'bg-slate-100 text-slate-500';
@endphp
<span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-bold {{ $cls }}">{{ \App\Models\SubscriptionPayment::STATUSES[$s] ?? $s }}</span>
