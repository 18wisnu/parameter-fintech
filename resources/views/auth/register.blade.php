<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun - Parameter Fintech</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="bg-slate-50">

    <div class="min-h-screen flex flex-col lg:flex-row">

        {{-- Left Panel: Branding --}}
        <div class="hidden lg:flex w-1/2 bg-sky-600 items-center justify-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-sky-500 to-blue-700 opacity-90"></div>

            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full blur-2xl"></div>

            <div class="relative z-10 text-center px-10 text-white">
                <div class="w-24 h-24 bg-white/20 rounded-2xl mx-auto flex items-center justify-center mb-6 backdrop-blur-sm shadow-xl border border-white/20">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold mb-2 tracking-wide">PARAMETER</h1>
                <p class="text-sky-100 text-lg font-light mb-8">Sistem Manajemen Keuangan Pintar</p>

                <div class="grid grid-cols-1 gap-4 text-left max-w-xs mx-auto">
                    <div class="flex items-center gap-3 bg-white/10 rounded-xl px-4 py-3 border border-white/15">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium">Laporan keuangan otomatis</span>
                    </div>
                    <div class="flex items-center gap-3 bg-white/10 rounded-xl px-4 py-3 border border-white/15">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium">Manajemen data pelanggan</span>
                    </div>
                    <div class="flex items-center gap-3 bg-white/10 rounded-xl px-4 py-3 border border-white/15">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium">Dashboard lapangan mobile</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Panel: Form --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 md:p-12 bg-white lg:bg-slate-50">

            <div class="w-full max-w-md">

                {{-- Mobile Header --}}
                <div class="lg:hidden text-center mb-8 mt-4">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-sky-600 text-white mb-4 shadow-lg shadow-sky-200">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800">Buat Akun Baru</h2>
                    <p class="text-slate-500">Daftar ke Parameter Fintech</p>
                </div>

                <div class="hidden lg:block mb-8">
                    <h2 class="text-3xl font-bold text-slate-800">Buat Akun Baru</h2>
                    <p class="text-slate-500 mt-2">Isi formulir di bawah untuk mendaftar.</p>
                </div>

                <div class="bg-white rounded-2xl lg:shadow-xl lg:border lg:border-slate-100 lg:p-8">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        {{-- Name --}}
                        <div class="mb-5">
                            <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                                class="w-full px-5 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all font-medium outline-none"
                                placeholder="Nama lengkap Anda">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        {{-- Email --}}
                        <div class="mb-5">
                            <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Alamat Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                                class="w-full px-5 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all font-medium outline-none"
                                placeholder="email@contoh.com">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        {{-- Password --}}
                        <div class="mb-5">
                            <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Kata Sandi</label>
                            <input type="password" name="password" required autocomplete="new-password"
                                class="w-full px-5 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all font-medium outline-none"
                                placeholder="Minimal 8 karakter">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        {{-- Confirm Password --}}
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Konfirmasi Kata Sandi</label>
                            <input type="password" name="password_confirmation" required autocomplete="new-password"
                                class="w-full px-5 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all font-medium outline-none"
                                placeholder="Ulangi kata sandi">
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <button type="submit"
                            class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-sky-200 transition-transform transform active:scale-95 text-lg">
                            Daftar Sekarang
                        </button>

                        <div class="mt-6 text-center">
                            <span class="text-slate-500 text-sm">Sudah punya akun? </span>
                            <a href="{{ route('login') }}" class="text-sky-600 font-bold text-sm hover:text-sky-700 hover:underline">
                                Masuk di sini
                            </a>
                        </div>
                    </form>
                </div>

                <p class="mt-8 text-center text-xs text-slate-400">
                    &copy; {{ date('Y') }} Parameter Fintech System v1.0
                </p>
            </div>
        </div>
    </div>

</body>
</html>
