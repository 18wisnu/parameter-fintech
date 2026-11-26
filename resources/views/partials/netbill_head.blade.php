{{-- Ikon NetBill: favicon, ikon layar utama (HP) & manifest aplikasi --}}
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
<link rel="icon" type="image/png" sizes="64x64" href="{{ asset('images/favicon-64.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
<meta name="application-name" content="{{ config('netbill.name') }}">
<meta name="apple-mobile-web-app-title" content="{{ config('netbill.name') }}">
{{-- Huruf NetBill (sama di semua halaman) --}}
@once
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }</style>
@endonce
