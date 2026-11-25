{{-- Footer legal NetBill: tampil di semua halaman (tidak ikut dicetak) --}}
<footer class="print:hidden text-center text-[11px] leading-relaxed {{ $class ?? 'text-slate-400' }} py-4">
    &copy; {{ date('Y') }} <span class="font-semibold">{{ config('netbill.name') }}</span> &middot; {{ config('netbill.tagline') }}<br>
    Made with dedication by <span class="font-semibold">{{ config('netbill.company') }}</span>
</footer>
