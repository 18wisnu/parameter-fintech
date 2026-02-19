<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Mobile</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Outfit', sans-serif; } [x-cloak] { display: none !important; } </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased pb-28"> 
    
    <div class="bg-sky-600 p-6 shadow-lg sticky top-0 z-50 rounded-b-[2rem]">
        <div class="flex justify-between items-center text-white">
            <div>
                <p class="text-[10px] opacity-70 uppercase tracking-[0.2em] font-bold mb-0.5">Parameter Fintech</p>
                <h1 class="font-bold text-xl tracking-tight">Halo, {{ Auth::user()->name }}</h1>
            </div>
            @if(Auth::user()->avatar)
                <img src="{{ Auth::user()->avatar }}" class="h-12 w-12 rounded-full border-2 border-white/20 shadow-lg">
            @else
                <div class="h-12 w-12 rounded-full bg-sky-700/50 flex items-center justify-center border-2 border-white/20 font-bold text-lg shadow-lg">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
            @endif
        </div>
    </div>

    <main class="p-5">
        @yield('content')
    </main>

    <div class="fixed bottom-0 left-0 w-full bg-white/90 backdrop-blur-md border-t border-slate-100 flex justify-around py-4 shadow-[0_-10px_30px_rgba(0,0,0,0.05)] z-50 rounded-t-[2rem]">
        <a href="{{ route('mobile.home') }}" class="flex flex-col items-center transition-all {{ request()->is('mobile/home') ? 'text-sky-600 scale-110' : 'text-slate-400 hover:text-sky-500' }}">
            <div class="p-1 rounded-lg {{ request()->is('mobile/home') ? 'bg-sky-50' : '' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            </div>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-widest">Home</span>
        </a>

        @if(in_array(auth()->user()->role, ['admin', 'owner']))
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center text-slate-400 hover:text-sky-500 transition-all">
            <div class="p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            </div>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-widest">Dashboard</span>
        </a>
        @endif
        <a href="#" class="flex flex-col items-center text-slate-400 hover:text-sky-500 transition-all">
            <div class="p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-widest">Riwayat</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="flex flex-col items-center text-slate-400 hover:text-rose-500 transition-all cursor-pointer">
            @csrf
            <button type="submit" class="flex flex-col items-center">
                <div class="p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </div>
                <span class="text-[9px] font-bold mt-1 uppercase tracking-widest">Keluar</span>
            </button>
        </form>
    </div>

</body>
</html>