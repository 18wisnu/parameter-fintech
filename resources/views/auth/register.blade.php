<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar - NetBill</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.netbill_head')
</head>
<body class="bg-slate-50">

    <div class="min-h-screen flex flex-col lg:flex-row">

        {{-- Left Panel: Branding --}}
        <div class="hidden lg:flex w-1/2 bg-navy-950 items-center justify-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-navy-950 via-navy-900 to-brand-900 opacity-95"></div>

            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-gold-400 opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-brand-400 opacity-20 rounded-full blur-2xl"></div>

            <div class="relative z-10 text-center px-10 text-white">
                <img src="{{ asset('images/netbill-logo.png') }}" alt="NetBill – Internet Billing" class="w-80 max-w-full mx-auto mb-5">
                <p class="text-sky-100/90 text-lg font-light mb-8">{{ config('netbill.tagline') }}</p>

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
                    <img src="{{ asset('images/netbill-logo-light.png') }}" alt="NetBill – Internet Billing" class="h-16 w-auto mx-auto mb-5">
                    <h2 class="text-2xl font-bold text-slate-800">Daftarkan Unit Bisnis</h2>
                    <p class="text-slate-500">Yang mendaftarkan menjadi pemilik (owner)</p>
                </div>

                <div class="hidden lg:block mb-8">
                    <h2 class="text-3xl font-bold text-slate-800">Daftarkan Unit Bisnis</h2>
                    <p class="text-slate-500 mt-2">Anda akan menjadi pemilik (owner) unit bisnis yang didaftarkan.</p>
                </div>

                @php $isTenantMode = request('mode') === 'tenant'; @endphp

                {{-- Pilihan: Daftar Baru / Tambah Tenant --}}
                <div class="grid grid-cols-2 gap-2 p-1 mb-6 bg-slate-100 rounded-xl">
                    <a href="{{ route('register') }}"
                        class="text-center py-2.5 rounded-lg text-sm font-bold transition {{ $isTenantMode ? 'text-slate-500 hover:text-slate-700' : 'bg-white text-sky-700 shadow-sm' }}">
                        Daftar Baru
                    </a>
                    <a href="{{ route('register', ['mode' => 'tenant']) }}"
                        class="text-center py-2.5 rounded-lg text-sm font-bold transition {{ $isTenantMode ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                        Tambah Tenant
                    </a>
                </div>

                @if($isTenantMode)
                <div class="bg-white rounded-2xl lg:shadow-xl lg:border lg:border-slate-100 lg:p-8">
                    <p class="text-sm text-slate-500 mb-6">Sudah punya akun? Masukkan email &amp; password akun Anda, lalu nama unit bisnis baru. Unit bisnis ini akan terhubung ke akun yang sama.</p>

                    <form method="POST" action="{{ route('register.tenant') }}">
                        @csrf

                        <div class="mb-5">
                            <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Nama Unit Bisnis Baru</label>
                            <input type="text" name="business_name" value="{{ old('business_name') }}" required autofocus
                                class="w-full px-5 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all font-medium outline-none"
                                placeholder="Contoh: RTRW Net Cabang Timur">
                            <x-input-error :messages="$errors->get('business_name')" class="mt-2" />
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Email Akun</label>
                            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                                class="w-full px-5 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all font-medium outline-none"
                                placeholder="email@contoh.com">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Kata Sandi Akun</label>
                            <input type="password" name="password" required autocomplete="current-password"
                                class="w-full px-5 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all font-medium outline-none"
                                placeholder="Kata sandi akun Anda">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <button type="submit"
                            class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-sky-200 transition-transform transform active:scale-95 text-lg">
                            Tambah Tenant
                        </button>

                        <div class="mt-6 text-center">
                            <span class="text-slate-500 text-sm">Belum punya akun? </span>
                            <a href="{{ route('register') }}" class="text-sky-600 font-bold text-sm hover:text-sky-700 hover:underline">
                                Daftar baru
                            </a>
                        </div>
                    </form>
                </div>
                @else
                <div class="bg-white rounded-2xl lg:shadow-xl lg:border lg:border-slate-100 lg:p-8">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        {{-- Business Name --}}
                        <div class="mb-5">
                            <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Nama Unit Bisnis</label>
                            <input type="text" name="business_name" value="{{ old('business_name') }}" required
                                class="w-full px-5 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all font-medium outline-none"
                                placeholder="Contoh: RTRW Net Parameter">
                            <x-input-error :messages="$errors->get('business_name')" class="mt-2" />
                        </div>

                        {{-- Name --}}
                        <div class="mb-5">
                            <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
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
                @endif

                @include('partials.netbill_footer')
            </div>
        </div>
    </div>

</body>
</html>
