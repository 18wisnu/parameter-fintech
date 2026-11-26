<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Usaha | NetBill</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.netbill_head')
</head>
<body class="min-h-screen relative bg-navy-950 text-white antialiased">

    <div class="fixed inset-0 pointer-events-none -z-0">
        <div class="absolute inset-0 bg-gradient-to-br from-navy-950 via-navy-900 to-brand-900"></div>
        <div class="absolute -top-32 -right-24 w-[28rem] h-[28rem] rounded-full bg-brand-400/20 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-20 w-[26rem] h-[26rem] rounded-full bg-gold-400/10 blur-3xl"></div>
    </div>

    <div class="relative">
        <nav class="max-w-5xl mx-auto px-4 sm:px-6 py-5 flex justify-between items-center gap-3">
            <a href="/"><img src="{{ asset('images/netbill-logo.png') }}" alt="NetBill" class="h-9 sm:h-11 w-auto"></a>
            <div class="flex items-center gap-3">
                <span class="hidden sm:inline text-white/60 text-sm">{{ Auth::user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white/90 ring-1 ring-white/20 hover:bg-white/10">Keluar</button>
                </form>
            </div>
        </nav>

        <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
            <div class="text-center mb-10">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Pilih usaha</h1>
                <p class="text-white/60 mt-2">Masuk ke salah satu usaha Anda, atau buat usaha baru.</p>
            </div>

            @if (session('success'))
                <div class="mb-6 rounded-xl bg-brand-400/15 ring-1 ring-brand-300/40 px-4 py-3 text-sm text-brand-100">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-6 rounded-xl bg-rose-500/15 ring-1 ring-rose-300/40 px-4 py-3 text-sm text-rose-100">{{ session('error') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-5 sm:p-6 flex flex-col">
                    <h2 class="font-bold text-lg mb-4">Usaha terdaftar</h2>
                    <div class="space-y-2.5 flex-1 overflow-y-auto max-h-[420px]">
                        @forelse($businesses as $item)
                        <form action="{{ route('admin.business.select', $item->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-white/5 hover:bg-white/10 ring-1 ring-white/10 hover:ring-brand-300/60 px-4 py-3.5 rounded-xl flex items-center justify-between gap-3 transition group text-left">
                                <div class="min-w-0">
                                    <p class="font-semibold text-white truncate">{{ $item->name }}</p>
                                    <p class="text-xs text-brand-300 mt-0.5">Masuk sebagai {{ ucfirst($item->pivot->role ?? 'owner') }}</p>
                                </div>
                                <span class="w-8 h-8 shrink-0 rounded-full bg-white/10 group-hover:bg-gold-400 group-hover:text-navy-950 flex items-center justify-center transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                </span>
                            </button>
                        </form>
                        @empty
                        <div class="py-14 text-center rounded-xl ring-1 ring-dashed ring-white/15 text-white/50 text-sm">Belum ada usaha.</div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl bg-white text-slate-700 p-5 sm:p-6 flex flex-col shadow-2xl shadow-black/30">
                    <h2 class="font-bold text-lg text-slate-900">Buat usaha baru</h2>
                    <p class="text-sm text-slate-500 mt-1 mb-5">Untuk cabang atau jaringan lain. Masa coba {{ (int) config('netbill.trial_days', 14) }} hari langsung aktif.</p>

                    <form action="{{ route('admin.business.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama usaha</label>
                            <input type="text" name="name" required placeholder="Contoh: Tanjungpura Net"
                                class="w-full px-4 py-2.5 rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500/30">
                        </div>
                        <button type="submit" class="w-full py-3 rounded-xl bg-gold-400 hover:bg-gold-300 text-navy-950 font-bold transition">Buat usaha</button>
                    </form>

                    <p class="mt-auto pt-6 text-xs text-slate-400 text-center">Fitur & pengaturan bisa diubah nanti di menu Pengaturan.</p>
                </div>
            </div>

            <div class="mt-12">@include('partials.netbill_footer', ['class' => 'text-white/50', 'dark' => true])</div>
        </main>
    </div>
</body>
</html>
