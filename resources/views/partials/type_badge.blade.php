{{--
    Lencana jenis layanan, warnanya sama di semua halaman.
    @include('partials.type_badge', ['type' => 'pppoe' | 'ppp' | 'hotspot' | 'voucher' | 'reseller' | 'general' | 'dhcp' | 'static'])
--}}
@php
    $k = ['ppp' => 'pppoe', 'dhcp' => 'general', 'static' => 'general', 'reseller' => 'voucher'][$type ?? ''] ?? ($type ?? '');
    [$label, $cls] = [
        'pppoe' => ['PPPoE', 'bg-indigo-50 text-indigo-700 ring-indigo-200'],
        'hotspot' => ['Hotspot langganan', 'bg-teal-50 text-teal-700 ring-teal-200'],
        'voucher' => [($type ?? '') === 'reseller' ? 'Reseller voucher' : 'Voucher hotspot', 'bg-amber-50 text-amber-700 ring-amber-200'],
        'general' => [['dhcp' => 'DHCP', 'static' => 'Static'][$type] ?? 'DHCP / Static', 'bg-slate-100 text-slate-600 ring-slate-200'],
    ][$k] ?? [strtoupper((string) ($type ?? '-')), 'bg-slate-100 text-slate-600 ring-slate-200'];
@endphp
<span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold ring-1 whitespace-nowrap {{ $cls }}">{{ $label }}</span>
