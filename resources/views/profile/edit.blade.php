<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Saya - Parameter Fintech</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f1f5f9; }
    </style>
</head>
<body>

{{-- Top Navigation --}}
<nav class="bg-sky-600 shadow-lg shadow-sky-200/50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white/20 rounded-lg flex items-center justify-center border border-white/25">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-lg tracking-wide">PARAMETER</span>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ url('/dashboard') }}" class="text-sky-100 hover:text-white text-sm font-medium transition-colors">
                    ← Dashboard
                </a>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-white/15 hover:bg-white/25 text-white text-sm font-semibold px-4 py-1.5 rounded-lg border border-white/25 transition-all">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Profil Saya</h1>
        <p class="text-slate-500 mt-1">Kelola informasi akun dan keamanan Anda.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- User Card --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="h-24 bg-gradient-to-br from-sky-500 to-blue-600"></div>
                <div class="px-6 pb-6 -mt-10">
                    <div class="w-20 h-20 bg-white rounded-2xl shadow-lg border-4 border-white flex items-center justify-center mb-4">
                        <div class="w-full h-full bg-gradient-to-br from-sky-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <span class="text-white text-2xl font-bold">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                        </div>
                    </div>
                    <h3 class="font-bold text-slate-800 text-lg leading-tight">{{ Auth::user()->name }}</h3>
                    <p class="text-slate-500 text-sm mt-0.5">{{ Auth::user()->email }}</p>

                    @if(Auth::user()->role ?? null)
                        <span class="inline-block mt-3 px-3 py-1 bg-sky-100 text-sky-700 text-xs font-bold rounded-full uppercase tracking-wide">
                            {{ Auth::user()->role }}
                        </span>
                    @endif

                    <div class="mt-5 pt-5 border-t border-slate-100 space-y-2.5">
                        <div class="flex items-center gap-2.5 text-sm text-slate-600">
                            <svg class="w-4 h-4 text-sky-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span class="truncate">{{ Auth::user()->email }}</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-sm text-slate-600">
                            <svg class="w-4 h-4 text-sky-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Bergabung {{ Auth::user()->created_at->locale('id')->isoFormat('MMMM YYYY') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Forms Column --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Update Profile Information --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 lg:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-sky-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-800">Informasi Profil</h2>
                        <p class="text-slate-500 text-sm">Perbarui nama dan email akun Anda.</p>
                    </div>
                </div>
                @include('profile.partials.update-profile-information-form')
            </div>

            {{-- Update Password --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 lg:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-800">Keamanan & Kata Sandi</h2>
                        <p class="text-slate-500 text-sm">Pastikan Anda menggunakan kata sandi yang kuat.</p>
                    </div>
                </div>
                @include('profile.partials.update-password-form')
            </div>

            {{-- Delete Account --}}
            <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-6 lg:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-red-700">Hapus Akun</h2>
                        <p class="text-slate-500 text-sm">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                @include('profile.partials.delete-user-form')
            </div>

        </div>
    </div>
</div>

</body>
</html>
