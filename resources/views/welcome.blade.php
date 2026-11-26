<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NetBill – Billing untuk Usaha Internet Kecil-kecilan</title>
    <meta name="description" content="NetBill: aplikasi billing & keuangan untuk RT/RW Net, hotspot, dan ISP lokal. Tagihan otomatis, setoran kolektor dari HP, PPPoE, hotspot & voucher outlet, laporan laba rugi dan bagi hasil.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        .nb-gold { background: linear-gradient(135deg, #f6d27a, #e0a43a); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .nb-grid-bg { background-image: radial-gradient(rgba(255,255,255,.08) 1px, transparent 1px); background-size: 22px 22px; }
    </style>
    @include('partials.netbill_head')
</head>
<body class="bg-white text-slate-700 antialiased">
@php
    $dashboard = url('/dashboard');
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $trial = (int) config('netbill.trial_days', 14);

    $features = [
        ['Tagihan bulanan otomatis', 'Invoice terbit sendiri tiap bulan sesuai paket & jatuh tempo pelanggan. Cetak A4, struk thermal (POS), atau PDF.', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['Setoran kolektor dari HP', 'Kolektor lapangan mencatat setoran langsung dari ponsel, lengkap foto bukti. Uang langsung tercatat di buku kas, tagihan otomatis lunas.', 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'],
        ['PPPoE, hotspot & voucher terpisah', 'Pengguna online dibedakan jelas: PPPoE, hotspot langganan, dan voucher hotspot. Tidak ada lagi pendapatan yang tercampur.', 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0'],
        ['Voucher outlet tanpa hitung dobel', 'Penjualan voucher tiap outlet dibandingkan dengan setorannya, jadi kelihatan mana yang belum setor, tanpa angka terhitung dua kali.', 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
        ['Terhubung ke server jaringan', 'Data pelanggan, paket, status layanan & reseller/outlet ditarik otomatis dari server RADIUS Anda. Isolir & putus sesi dari satu tempat.', 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01'],
        ['Laporan keuangan siap pakai', 'Buku kas, piutang, laba rugi bulanan, dan tutup buku. Unduh ke Excel atau PDF untuk arsip & laporan ke mitra.', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ['Bagi hasil transparan', 'Atur penerima bagi hasil & persentasenya sendiri. Pembagian dihitung otomatis dari laba, tidak bisa melebihi 100%.', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ['Aman & tercatat', 'Hak akses per peran, log aktivitas tiap perubahan, transaksi salah bisa dibatalkan dengan jejak, dan backup data harian.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
    ];

    $roles = [
        ['Pemilik', 'Lihat laba, bagi hasil & semua laporan. Atur paket, server jaringan, dan tim.'],
        ['Admin', 'Kelola pelanggan, tagihan, konfirmasi setoran, dan cetak invoice.'],
        ['Kolektor', 'Tagih & setor dari HP: daftar pelanggan yang ditugaskan, riwayat setoran harian.'],
        ['Teknisi', 'Pantau pengguna online, status layanan, dan tiket gangguan di lapangan.'],
    ];

    $faqs = [
        ['Apakah cocok untuk RT/RW Net kecil?', 'Ya. NetBill dibuat untuk usaha internet kecil-kecilan: dari puluhan pelanggan PPPoE/hotspot sampai jaringan dengan banyak outlet voucher.'],
        ['Apakah harus punya server RADIUS?', 'Tidak wajib. Tanpa server, pelanggan & tagihan bisa dikelola manual. Bila server jaringan dihubungkan, data pelanggan, layanan & pengguna online tersinkron otomatis.'],
        ['Bagaimana dengan hotspot langganan dan voucher?', 'Keduanya dipisah. Hotspot langganan ditagih bulanan seperti PPPoE; voucher dijual lewat outlet dan masuk sebagai pendapatan voucher.'],
        ['Bisa dipakai dari HP?', 'Bisa. Tampilan menyesuaikan layar HP, dan kolektor punya menu khusus untuk tagih & setor.'],
        ['Data saya aman?', 'Setiap usaha terpisah datanya, akses dibatasi per peran, semua perubahan tercatat, dan database dicadangkan setiap hari.'],
    ];
@endphp

{{-- ================= HERO ================= --}}
<header class="relative overflow-hidden bg-[#0b1026] text-white">
    <div class="absolute inset-0 bg-gradient-to-br from-[#0b1026] via-[#13204a] to-[#0c4a6e]"></div>
    <div class="absolute inset-0 nb-grid-bg"></div>
    <div class="absolute -top-32 -right-24 w-[28rem] h-[28rem] rounded-full bg-teal-400/20 blur-3xl"></div>
    <div class="absolute -bottom-40 -left-20 w-[26rem] h-[26rem] rounded-full bg-amber-400/10 blur-3xl"></div>

    <nav class="relative max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-3">
        <a href="/" class="shrink-0"><img src="{{ asset('images/netbill-logo.png') }}" alt="NetBill" class="h-9 sm:h-11 w-auto"></a>
        <div class="hidden md:flex items-center gap-6 text-sm text-white/70">
            <a href="#keunggulan" class="hover:text-white">Keunggulan</a>
            <a href="#cara-kerja" class="hover:text-white">Cara kerja</a>
            @if($plans->isNotEmpty())<a href="#harga" class="hover:text-white">Harga</a>@endif
            <a href="#faq" class="hover:text-white">FAQ</a>
        </div>
        <div class="flex items-center gap-2">
            @auth
                <a href="{{ $dashboard }}" class="px-4 py-2 rounded-lg bg-white text-slate-900 text-sm font-bold hover:bg-slate-100">Ke Dashboard</a>
            @else
                @if (Route::has('login'))<a href="{{ route('login') }}" class="px-3 sm:px-4 py-2 rounded-lg text-sm font-semibold text-white/90 hover:bg-white/10">Masuk</a>@endif
                @if (Route::has('register'))<a href="{{ route('register') }}" class="px-3 sm:px-4 py-2 rounded-lg bg-amber-400 text-slate-900 text-sm font-bold hover:bg-amber-300">Coba Gratis</a>@endif
            @endauth
        </div>
    </nav>

    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 pt-10 pb-20 lg:pt-16 lg:pb-28 grid lg:grid-cols-2 gap-12 items-center">
        <div>
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 ring-1 ring-white/20 text-xs font-medium text-white/80">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Untuk RT/RW Net, hotspot & ISP lokal
            </span>
            <h1 class="mt-5 text-4xl sm:text-5xl lg:text-[3.4rem] font-extrabold leading-[1.08] tracking-tight">
                Tagih, setor & hitung untung<br><span class="nb-gold">tanpa ribet, tanpa dobel.</span>
            </h1>
            <p class="mt-5 text-base sm:text-lg text-white/75 max-w-xl leading-relaxed">
                NetBill menyatukan tagihan pelanggan, setoran kolektor, voucher outlet, dan laporan keuangan usaha internet Anda dalam satu aplikasi yang bisa dibuka dari HP.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                @auth
                    <a href="{{ $dashboard }}" class="px-6 py-3 rounded-xl bg-amber-400 text-slate-900 font-bold shadow-lg shadow-amber-500/20 hover:bg-amber-300">Buka Dashboard</a>
                @else
                    @if (Route::has('register'))<a href="{{ route('register') }}" class="px-6 py-3 rounded-xl bg-amber-400 text-slate-900 font-bold shadow-lg shadow-amber-500/20 hover:bg-amber-300">Coba gratis {{ $trial }} hari</a>@endif
                    <a href="#keunggulan" class="px-6 py-3 rounded-xl bg-white/10 ring-1 ring-white/25 font-semibold hover:bg-white/15">Lihat keunggulan</a>
                @endauth
            </div>
            <ul class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-sm text-white/60">
                <li class="flex items-center gap-1.5"><span class="text-teal-300">✓</span> Tanpa kartu kredit</li>
                <li class="flex items-center gap-1.5"><span class="text-teal-300">✓</span> Masuk dengan Google</li>
                <li class="flex items-center gap-1.5"><span class="text-teal-300">✓</span> Bisa di HP</li>
            </ul>
        </div>

        {{-- Pratinjau ringkas dashboard (ilustrasi, bukan data asli) --}}
        <div class="relative" aria-hidden="true">
            <div class="rounded-2xl bg-white text-slate-700 shadow-2xl shadow-black/40 ring-1 ring-white/10 overflow-hidden">
                <div class="flex items-center gap-1.5 px-4 py-2.5 bg-slate-50 border-b border-slate-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-300"></span><span class="w-2.5 h-2.5 rounded-full bg-amber-300"></span><span class="w-2.5 h-2.5 rounded-full bg-emerald-300"></span>
                    <span class="ml-3 text-[11px] text-slate-400">Ringkasan bulan ini · contoh</span>
                </div>
                <div class="p-4 grid grid-cols-3 gap-2.5">
                    <div class="rounded-xl bg-emerald-50 p-3"><div class="text-[10px] text-emerald-700/70">Pemasukan</div><div class="text-sm font-bold text-emerald-700">Rp 18,4 jt</div></div>
                    <div class="rounded-xl bg-amber-50 p-3"><div class="text-[10px] text-amber-700/70">Belum bayar</div><div class="text-sm font-bold text-amber-700">23 tagihan</div></div>
                    <div class="rounded-xl bg-sky-50 p-3"><div class="text-[10px] text-sky-700/70">Laba bersih</div><div class="text-sm font-bold text-sky-700">Rp 11,2 jt</div></div>
                </div>
                <div class="px-4 pb-4">
                    <div class="text-[11px] font-semibold text-slate-500 mb-2">Pengguna online</div>
                    <div class="flex h-2.5 rounded-full overflow-hidden">
                        <div class="bg-indigo-500" style="width:28%"></div><div class="bg-teal-500" style="width:8%"></div><div class="bg-amber-400" style="width:64%"></div>
                    </div>
                    <div class="mt-2 flex gap-4 text-[11px] text-slate-500">
                        <span class="flex items-center gap-1"><i class="inline-block w-2 h-2 rounded-full bg-indigo-500"></i>PPPoE</span>
                        <span class="flex items-center gap-1"><i class="inline-block w-2 h-2 rounded-full bg-teal-500"></i>Hotspot langganan</span>
                        <span class="flex items-center gap-1"><i class="inline-block w-2 h-2 rounded-full bg-amber-400"></i>Voucher</span>
                    </div>
                    <div class="mt-4 divide-y divide-slate-100 text-xs">
                        @foreach([['Warung Sari', 'Outlet voucher', 'Rp 450.000', 'amber'], ['Budi Santoso', 'PPPoE', 'Rp 150.000', 'indigo'], ['Rina', 'Hotspot langganan', 'Rp 100.000', 'teal']] as [$n, $t, $a, $c])
                            <div class="flex items-center justify-between py-2">
                                <div><div class="font-semibold text-slate-700">{{ $n }}</div><span class="inline-block mt-0.5 px-1.5 rounded-full text-[10px] font-semibold bg-{{ $c }}-50 text-{{ $c }}-700">{{ $t }}</span></div>
                                <div class="text-right"><div class="font-bold text-slate-800">{{ $a }}</div><div class="text-[10px] text-emerald-600">Disetor ✓</div></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="absolute -bottom-5 -left-3 sm:-left-6 rounded-xl bg-white text-slate-700 shadow-xl px-4 py-3 text-xs ring-1 ring-slate-100">
                <div class="font-bold text-slate-800">Setoran diterima</div>
                <div class="text-slate-500">Kolektor · Rp 150.000 · baru saja</div>
            </div>
        </div>
    </div>
</header>

{{-- ================= STRIP NILAI ================= --}}
<section class="border-b border-slate-100 bg-slate-50/60">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
        @foreach([['1 aplikasi', 'tagihan, kas & laporan'], ['3 jenis', 'PPPoE · hotspot · voucher'], ['4 peran', 'pemilik sampai kolektor'], ['Harian', 'backup data otomatis']] as [$a, $b])
            <div><div class="text-lg sm:text-xl font-extrabold text-slate-900">{{ $a }}</div><div class="text-xs text-slate-500">{{ $b }}</div></div>
        @endforeach
    </div>
</section>

{{-- ================= KEUNGGULAN ================= --}}
<section id="keunggulan" class="max-w-6xl mx-auto px-4 sm:px-6 py-16 lg:py-24">
    <div class="max-w-2xl">
        <p class="text-sm font-bold text-teal-600 uppercase tracking-wider">Keunggulan NetBill</p>
        <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Dibuat dari cara kerja usaha internet sungguhan</h2>
        <p class="mt-3 text-slate-500">Bukan aplikasi akuntansi umum. Setiap fitur menjawab masalah harian pengelola RT/RW Net: pelanggan telat bayar, setoran kolektor, voucher di banyak outlet, dan bagi hasil dengan mitra.</p>
    </div>
    <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($features as [$title, $desc, $icon])
            <div class="group rounded-2xl ring-1 ring-slate-200/80 p-5 bg-white hover:ring-teal-300 hover:shadow-lg hover:shadow-teal-500/5 transition">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#13204a] to-teal-600 text-white flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </div>
                <h3 class="mt-4 font-bold text-slate-900">{{ $title }}</h3>
                <p class="mt-1.5 text-sm text-slate-500 leading-relaxed">{{ $desc }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ================= CARA KERJA ================= --}}
<section id="cara-kerja" class="bg-slate-50 border-y border-slate-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 lg:py-20 grid lg:grid-cols-2 gap-12 items-start">
        <div>
            <p class="text-sm font-bold text-teal-600 uppercase tracking-wider">Cara kerja</p>
            <h2 class="mt-2 text-3xl font-extrabold text-slate-900 tracking-tight">Mulai dalam hitungan menit</h2>
            <ol class="mt-8 space-y-6">
                @foreach([
                    ['Daftar & buat usaha', 'Masuk dengan email atau Google. Usaha Anda langsung aktif dengan masa coba ' . $trial . ' hari.'],
                    ['Masukkan pelanggan', 'Impor dari Excel, input manual, atau hubungkan server jaringan agar pelanggan & outlet tersinkron otomatis.'],
                    ['Ajak tim', 'Undang admin, kolektor & teknisi. Masing-masing hanya melihat menu sesuai tugasnya.'],
                    ['Tagih & pantau', 'Tagihan terbit tiap bulan, kolektor setor dari HP, dan laporan laba rugi siap kapan saja.'],
                ] as $i => [$t, $d])
                    <li class="flex gap-4">
                        <span class="shrink-0 w-8 h-8 rounded-full bg-[#13204a] text-amber-300 text-sm font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                        <div><h3 class="font-bold text-slate-900">{{ $t }}</h3><p class="text-sm text-slate-500 mt-0.5">{{ $d }}</p></div>
                    </li>
                @endforeach
            </ol>
        </div>
        <div>
            <p class="text-sm font-bold text-teal-600 uppercase tracking-wider">Satu aplikasi, banyak peran</p>
            <h2 class="mt-2 text-3xl font-extrabold text-slate-900 tracking-tight">Setiap orang dapat menu yang pas</h2>
            <div class="mt-8 grid sm:grid-cols-2 gap-3">
                @foreach($roles as [$r, $d])
                    <div class="rounded-2xl bg-white ring-1 ring-slate-200/80 p-4">
                        <div class="font-bold text-slate-900">{{ $r }}</div>
                        <p class="text-sm text-slate-500 mt-1">{{ $d }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ================= HARGA ================= --}}
@if($plans->isNotEmpty())
<section id="harga" class="max-w-6xl mx-auto px-4 sm:px-6 py-16 lg:py-24">
    <div class="text-center max-w-2xl mx-auto">
        <p class="text-sm font-bold text-teal-600 uppercase tracking-wider">Harga</p>
        <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Bayar sesuai jumlah pelanggan</h2>
        <p class="mt-3 text-slate-500">Semua paket mendapat fitur lengkap. Coba gratis {{ $trial }} hari dulu, baru pilih paket.</p>
    </div>
    <div class="mt-10 grid gap-4 {{ $plans->count() >= 3 ? 'md:grid-cols-3' : 'md:grid-cols-2 max-w-3xl mx-auto' }}">
        @foreach($plans as $i => $p)
            @php $hl = $plans->count() >= 3 && $i === 1; @endphp
            <div class="relative rounded-2xl p-6 {{ $hl ? 'bg-[#0b1026] text-white ring-2 ring-amber-400 shadow-xl' : 'bg-white ring-1 ring-slate-200' }}">
                @if($hl)<span class="absolute -top-3 left-6 px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-900 text-[11px] font-bold">Paling dipilih</span>@endif
                <div class="font-bold {{ $hl ? 'text-white' : 'text-slate-900' }}">{{ $p->name }}</div>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-3xl font-extrabold">{{ $rp($p->price) }}</span><span class="text-sm {{ $hl ? 'text-white/60' : 'text-slate-400' }}">/bulan</span>
                </div>
                <div class="mt-1 text-sm {{ $hl ? 'text-teal-300' : 'text-teal-600' }} font-semibold">{{ $p->limitLabel() }}</div>
                @if($p->description)<p class="mt-3 text-sm {{ $hl ? 'text-white/70' : 'text-slate-500' }}">{{ $p->description }}</p>@endif
                <ul class="mt-4 space-y-1.5 text-sm {{ $hl ? 'text-white/80' : 'text-slate-600' }}">
                    @foreach(['Semua fitur billing & laporan', 'Kolektor & teknisi tanpa batas', 'Sinkron server jaringan'] as $f)
                        <li class="flex gap-2"><span class="{{ $hl ? 'text-amber-300' : 'text-teal-500' }}">✓</span>{{ $f }}</li>
                    @endforeach
                </ul>
                @guest
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="mt-6 block text-center px-4 py-2.5 rounded-xl font-bold text-sm {{ $hl ? 'bg-amber-400 text-slate-900 hover:bg-amber-300' : 'bg-slate-900 text-white hover:bg-slate-700' }}">Mulai coba gratis</a>
                    @endif
                @endguest
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- ================= FAQ ================= --}}
<section id="faq" class="bg-slate-50 border-t border-slate-100">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-16 lg:py-20">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight text-center">Pertanyaan yang sering muncul</h2>
        <div class="mt-8 space-y-3">
            @foreach($faqs as [$q, $a])
                <details class="group rounded-xl bg-white ring-1 ring-slate-200/80 px-5 py-4">
                    <summary class="flex items-center justify-between cursor-pointer list-none font-semibold text-slate-900">
                        {{ $q }}<span class="ml-4 text-slate-400 group-open:rotate-45 transition text-xl leading-none">+</span>
                    </summary>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed">{{ $a }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= CTA ================= --}}
<section class="relative overflow-hidden bg-[#0b1026] text-white">
    <div class="absolute inset-0 bg-gradient-to-r from-[#0b1026] via-[#13204a] to-[#0c4a6e]"></div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 py-14 text-center">
        <h2 class="text-2xl sm:text-3xl font-extrabold">Rapikan keuangan usaha internet Anda mulai hari ini</h2>
        <p class="mt-2 text-white/70">Gratis {{ $trial }} hari. Tidak perlu kartu kredit.</p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            @auth
                <a href="{{ $dashboard }}" class="px-6 py-3 rounded-xl bg-amber-400 text-slate-900 font-bold hover:bg-amber-300">Buka Dashboard</a>
            @else
                @if (Route::has('register'))<a href="{{ route('register') }}" class="px-6 py-3 rounded-xl bg-amber-400 text-slate-900 font-bold hover:bg-amber-300">Daftar sekarang</a>@endif
                @if (Route::has('login'))<a href="{{ route('login') }}" class="px-6 py-3 rounded-xl bg-white/10 ring-1 ring-white/25 font-semibold hover:bg-white/15">Sudah punya akun? Masuk</a>@endif
            @endauth
        </div>
    </div>
</section>

<div class="bg-white border-t border-slate-100">
    @include('partials.netbill_footer')
</div>
</body>
</html>
