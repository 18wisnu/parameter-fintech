<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Tanjungpura Network | Parameter HelpDesk</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex flex-col md:flex-row selection:bg-indigo-500 selection:text-white">

    {{-- Kiri: Branding Product Parameter HelpDesk --}}
    <div class="relative flex-1 bg-slate-900 text-white flex flex-col justify-between p-8 md:p-12 lg:p-20 overflow-hidden bg-pattern">
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
            <div class="absolute -top-[20%] -left-[10%] w-[70%] h-[70%] rounded-full bg-indigo-600/20 blur-[100px]"></div>
            <div class="absolute top-[60%] -right-[10%] w-[50%] h-[50%] rounded-full bg-emerald-600/20 blur-[100px]"></div>
        </div>

        <div class="relative z-10 mt-4 md:mt-6">
            {{-- Logo Product --}}
            <div class="flex items-center gap-3 mb-10">
                <div class="bg-indigo-500 p-2 rounded-lg shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="2" y="2" width="20" height="8" rx="2" ry="2" stroke-width="2"></rect>
                        <rect x="2" y="14" width="20" height="8" rx="2" ry="2" stroke-width="2"></rect>
                        <line x1="6" y1="6" x2="6.01" y2="6" stroke-width="2"></line>
                        <line x1="6" y1="18" x2="6.01" y2="18" stroke-width="2"></line>
                    </svg>
                </div>
                <span class="text-xl sm:text-2xl font-black tracking-widest uppercase">Parameter <span class="text-indigo-400 font-bold">HelpDesk</span></span>
            </div>

            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-6">
                Sistem Manajemen <br class="hidden sm:block">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-emerald-400">Jaringan & Operasional</span>
            </h1>
            
            <p class="text-slate-300 text-base sm:text-lg md:text-xl max-w-2xl leading-relaxed mb-12 opacity-80 font-medium">
                Platform B2B terpadu untuk ISP dan RT/RW Net. Pantau dan kelola seluruh perangkat klien secara real-time dari satu pusat kontrol.
            </p>

            {{-- Grid Deskripsi Fitur Lanjutan --}}
            <div class="hidden lg:grid grid-cols-2 gap-8 max-w-2xl">
                <div>
                    <h4 class="text-indigo-400 text-sm font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        TR-069 AUTOMATION
                    </h4>
                    <p class="text-slate-400 text-sm leading-relaxed">Sinkronisasi parameter perangkat, SSID, dan profil PPPoE secara aman dan instan.</p>
                </div>
                <div>
                    <h4 class="text-emerald-400 text-sm font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        PORTAL PELANGGAN
                    </h4>
                    <p class="text-slate-400 text-sm leading-relaxed">Login khusus mandiri bagi pelanggan untuk ganti password WiFi & cek perangkat terhubung.</p>
                </div>
            </div>
        </div>

        <div class="relative z-10 mt-12 text-[10px] sm:text-xs text-slate-500 hidden md:block uppercase tracking-widest font-black opacity-50">
            Powered by <strong>Parameter Technology</strong> &copy; {{ date('Y') }}
        </div>
    </div>

    {{-- Kanan: Panel Login Klien (Tanjungpura Network) --}}
    <div class="w-full md:w-[450px] lg:w-[500px] bg-white flex flex-col justify-center px-6 py-10 sm:px-12 lg:px-16 shadow-2xl z-10 relative overflow-y-auto">
        <div class="max-w-sm mx-auto w-full">
            
            {{-- Header Tenant (Klien) --}}
            <div class="text-center md:text-left mb-10">
                <div class="inline-flex sm:hidden p-3 bg-slate-50 rounded-2xl mb-4">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight italic">Tanjungpura <span class="text-indigo-600">Net</span></h2>
                <p class="text-sm text-slate-500 mt-2 font-medium">Selamat datang! Silakan pilih jenis akses Anda untuk melanjutkan ke layanan kami.</p>
            </div>

            @if (Route::has('login'))
                <div class="flex flex-col gap-6">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-black uppercase tracking-widest rounded-2xl text-white bg-indigo-600 hover:bg-slate-900 focus:outline-none transition-all shadow-xl shadow-indigo-100">
                            Masuk Dashboard Saya
                        </a>
                    @else
                        {{-- Opsi 1: Portal Pelanggan --}}
                        <div class="group relative">
                            <a href="{{ route('login') }}" class="flex items-center gap-4 p-5 rounded-[2rem] bg-indigo-50 border-2 border-transparent hover:border-indigo-600 transition-all duration-300">
                                <div class="bg-white p-3 rounded-2xl shadow-sm text-indigo-600 shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <div class="flex-grow">
                                    <h4 class="text-base font-black text-slate-900 uppercase italic leading-none">Portal Pelanggan</h4>
                                    <p class="text-[11px] text-slate-500 mt-1 font-medium">Ganti Wi-Fi, Cek Perangkat & Kelola Akun</p>
                                </div>
                            </a>
                        </div>

                        <div class="relative flex justify-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">
                             &mdash; ATAU &mdash;
                        </div>

                        {{-- Opsi 2: Login Admin/Staff --}}
                        <div class="group relative">
                            <a href="{{ route('login') }}" class="flex items-center gap-4 p-5 rounded-[2rem] bg-slate-50 border-2 border-transparent hover:border-slate-800 transition-all duration-300">
                                <div class="bg-white p-3 rounded-2xl shadow-sm text-slate-700 shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 011-1h1a2 2 0 100-4H7a1 1 0 01-1-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>
                                </div>
                                <div class="flex-grow">
                                    <h4 class="text-base font-black text-slate-900 uppercase italic leading-none">Akses Admin / Staff</h4>
                                    <p class="text-[11px] text-slate-500 mt-1 font-medium">Monitoring Jaringan & Manajemen User</p>
                                </div>
                            </a>
                        </div>
                        
                    @endauth
                </div>
            @endif

            {{-- Support Info --}}
            <div class="mt-12 pt-8 border-t border-slate-100 flex items-center justify-center gap-4 text-xs font-black uppercase tracking-widest text-slate-400">
                <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Secure SSL</span>
                <span class="opacity-30">|</span>
                <span class="flex items-center gap-1.5">24/7 Support</span>
            </div>

            <div class="mt-8 text-center text-[11px] text-slate-400 font-medium md:hidden italic">
                Powered by Parameter Technology &copy; {{ date('Y') }}
            </div>
        </div>
    </div>
</body>
</html>