<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Bisnis | Parameter Fintech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; color: #f8fafc; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>
<body class="min-h-screen relative overflow-y-auto">
    
    <!-- Minimalist Background -->
    <div class="fixed inset-0 pointer-events-none -z-10">
        <div class="absolute top-[10%] left-[10%] w-[400px] h-[400px] bg-sky-500/5 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-[10%] right-[10%] w-[400px] h-[400px] bg-indigo-500/5 rounded-full blur-[100px]"></div>
    </div>

    <!-- Header -->
    <nav class="max-w-6xl mx-auto px-6 py-8 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <span class="text-2xl font-black tracking-tighter">PARAMETER<span class="text-sky-500">.</span></span>
        </div>
        <div class="flex items-center gap-4">
            <span class="hidden sm:inline text-slate-500 text-xs font-bold uppercase tracking-widest">{{ Auth::user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-rose-600 hover:bg-rose-500 text-white px-5 py-2.5 rounded-xl text-xs font-black transition-all shadow-lg shadow-rose-900/20">
                    LOGOUT
                </button>
            </form>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-6 py-10">
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-black mb-4">Pilih Kendali Bisnis</h1>
            <p class="text-slate-400 text-lg">Silakan pilih unit usaha atau buat profil baru</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- UNIT USAHA LIST -->
            <div class="bg-slate-800/40 border border-slate-700/50 rounded-[2.5rem] p-8 flex flex-col">
                <h2 class="text-xl font-black mb-6 flex items-center gap-3">
                    <span class="w-2 h-8 bg-sky-500 rounded-full"></span>
                    Unit Usaha Terdaftar
                </h2>
                
                <div class="space-y-3 flex-1 overflow-y-auto pr-2 max-h-[400px]">
                    @forelse($businesses as $item)
                    <form action="{{ route('admin.business.select', $item->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-sky-500/50 p-6 rounded-3xl flex items-center justify-between transition-all group">
                            <div class="text-left">
                                <p class="font-black text-white text-lg leading-tight">{{ $item->name }}</p>
                                <p class="text-[10px] font-bold text-sky-500 uppercase tracking-widest mt-1">MASUK SEBAGAI {{ $item->pivot->role ?? 'OWNER' }}</p>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-slate-700 group-hover:bg-sky-500 flex items-center justify-center transition-all">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </button>
                    </form>
                    @empty
                    <div class="py-20 text-center border-2 border-dashed border-slate-700 rounded-3xl opacity-50">
                        <p class="text-[10px] font-black uppercase tracking-widest">Belum ada bisnis</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- CREATE BUSINESS -->
            <div class="bg-sky-600 rounded-[2.5rem] p-8 flex flex-col shadow-2xl shadow-sky-900/40">
                <h2 class="text-xl font-black text-white mb-6 flex items-center gap-3">
                    <span class="w-2 h-8 bg-white/30 rounded-full"></span>
                    Mulai Bisnis Baru
                </h2>
                
                <p class="text-sky-100/70 text-sm mb-10 font-medium leading-relaxed">Siapkan profil sistem untuk unit usaha atau cabang baru Anda dalam hitungan detik.</p>

                <form action="{{ route('admin.business.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-sky-200 uppercase tracking-widest mb-3 ml-1">Nama Profil Bisnis</label>
                        <input type="text" name="name" required placeholder="Contoh: Parameter Coffee" 
                            class="w-full bg-white/10 border-2 border-white/10 focus:border-white focus:ring-0 rounded-2xl p-5 text-white font-bold placeholder:text-white/20 transition-all">
                    </div>
                    <button type="submit" class="w-full bg-white hover:bg-sky-50 text-sky-700 py-6 rounded-2xl font-black tracking-widest transition-all active:scale-[0.98] shadow-xl">
                        BUAT PROFIL BISNIS
                    </button>
                </form>

                <div class="mt-auto pt-10">
                    <p class="text-xs text-sky-100/50 italic text-center">Konfigurasi fitur menyusul di Master Setting.</p>
                </div>
            </div>
        </div>

        <p class="mt-20 text-center text-[10px] font-black text-slate-700 uppercase tracking-[0.4em]">PARAMETER FINTECH &bull; SECURE INFRASTRUCTURE</p>
    </main>
</body>
</html>
