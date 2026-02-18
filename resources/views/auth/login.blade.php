<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Parameter Fintech</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="bg-slate-50">

    <div class="min-h-screen flex flex-col lg:flex-row">
        
        <div class="hidden lg:flex w-1/2 bg-sky-600 items-center justify-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-sky-500 to-blue-700 opacity-90"></div>
            
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full blur-2xl"></div>

            <div class="relative z-10 text-center px-10 text-white">
                <div class="w-24 h-24 bg-white/20 rounded-2xl mx-auto flex items-center justify-center mb-6 backdrop-blur-sm shadow-xl border border-white/20">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h1 class="text-4xl font-bold mb-2 tracking-wide">PARAMETER</h1>
                <p class="text-sky-100 text-lg font-light">Sistem Manajemen Keuangan Pintar</p>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 md:p-12 bg-white lg:bg-slate-50">
            
            <div class="w-full max-w-md">
                
                <div class="lg:hidden text-center mb-8 mt-4">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-sky-600 text-white mb-4 shadow-lg shadow-sky-200">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800">Selamat Datang</h2>
                    <p class="text-slate-500">Masuk ke aplikasi Parameter Fintech</p>
                </div>

                <div class="hidden lg:block mb-8">
                    <h2 class="text-3xl font-bold text-slate-800">Login Akun</h2>
                    <p class="text-slate-500 mt-2">Silakan masukkan kredensial Anda.</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <div class="bg-white rounded-2xl lg:shadow-xl lg:border lg:border-slate-100 lg:p-8">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-5">
                            <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Email</label>
                            <input type="email" name="email" required autofocus 
                                class="w-full px-5 py-3 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all font-medium" 
                                placeholder="admin@example.com">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Password</label>
                            <input type="password" name="password" required
                                class="w-full px-5 py-3 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all font-medium"
                                placeholder="••••••••">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                       
                        <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-sky-200 transition-transform transform active:scale-95 text-lg">
                            Masuk Sekarang
                        </button>

                         <br>

                        <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-slate-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-slate-400 font-medium">Atau masuk dengan</span>
                        </div>
                    </div>
                         <br>
                    <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center px-4 py-3 border border-slate-200 rounded-xl shadow-sm bg-white text-slate-700 hover:bg-slate-50 font-bold transition-all transform active:scale-95">
                        <svg class="h-5 w-5 mr-3" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Akun Google
                    </a>

                        <div class="mt-6 flex justify-center">
                            <label class="inline-flex items-center cursor-pointer">
                                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500" name="remember">
                                <span class="ml-2 text-sm text-slate-500">Ingat saya di perangkat ini</span>
                            </label>
                        </div>
                    </form>
                </div>

                <p class="mt-8 text-center text-xs text-slate-400">
                    &copy; 2026 Parameter Fintech System v1.0
                </p>
            </div>
        </div>
    </div>
</body>
</html>