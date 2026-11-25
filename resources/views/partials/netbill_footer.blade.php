{{-- Footer legal NetBill: tampil di semua halaman (tidak ikut dicetak) --}}
<footer class="print:hidden text-center text-[11px] leading-relaxed {{ $class ?? 'text-slate-400' }} py-5">
    <img src="{{ asset('images/' . (($dark ?? false) ? 'netbill-logo.png' : 'netbill-logo-light.png')) }}" alt="{{ config('netbill.name') }}" class="h-7 w-auto mx-auto mb-1.5 opacity-90" loading="lazy">
    &copy; {{ date('Y') }} <span class="font-semibold">{{ config('netbill.name') }}</span> &middot; {{ config('netbill.tagline') }}<br>
    Made with dedication by <span class="font-semibold">{{ config('netbill.company') }}</span>
</footer>
