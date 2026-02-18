<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Mobile</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased pb-20"> <div class="bg-blue-600 p-4 shadow-md sticky top-0 z-50">
        <div class="flex justify-between items-center text-white">
            <div>
                <h1 class="font-bold text-lg">Halo, {{ Auth::user()->name }}</h1>
                <p class="text-xs opacity-80 uppercase tracking-wider">{{ Auth::user()->role }}</p>
            </div>
            @if(Auth::user()->avatar)
                <img src="{{ Auth::user()->avatar }}" class="h-10 w-10 rounded-full border-2 border-white">
            @else
                <div class="h-10 w-10 rounded-full bg-blue-800 flex items-center justify-center border-2 border-white">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
            @endif
        </div>
    </div>

    <main class="p-4">
        @yield('content')
    </main>

    <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 flex justify-around py-3 shadow-lg z-50">
        <a href="{{ route('mobile.home') }}" class="flex flex-col items-center text-blue-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-xs mt-1">Home</span>
        </a>
        <a href="#" class="flex flex-col items-center text-gray-400 hover:text-blue-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            <span class="text-xs mt-1">Riwayat</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="flex flex-col items-center text-red-400 hover:text-red-600 cursor-pointer">
            @csrf
            <button type="submit" class="flex flex-col items-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span class="text-xs mt-1">Keluar</span>
            </button>
        </form>
    </div>

</body>
</html>