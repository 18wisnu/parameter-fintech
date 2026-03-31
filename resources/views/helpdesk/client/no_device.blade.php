<x-app-layout>
    <div class="py-12 flex flex-col items-center justify-center min-h-[70vh] text-center px-4">
        <div class="mb-8 p-6 bg-slate-100 rounded-full">
            <svg class="w-16 h-16 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <h2 class="text-3xl font-black text-slate-800 mb-2">Perangkat Belum Terdaftar</h2>
        <p class="text-slate-500 max-w-md mx-auto mb-10 leading-relaxed font-medium">Mohon maaf, akun Anda belum terhubung dengan perangkat modem WiFi. Silakan hubungi admin atau teknisi kami untuk aktivasi akun Anda.</p>
        
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="https://wa.me/628XXXXXXXXXX" target="_blank" class="px-8 py-4 bg-emerald-500 text-white rounded-2xl font-black uppercase text-sm tracking-widest hover:bg-emerald-600 transition shadow-lg shadow-emerald-100">Hubungi Admin CS</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-8 py-4 bg-white border border-slate-200 text-slate-500 rounded-2xl font-black uppercase text-sm tracking-widest hover:bg-slate-50 transition">Keluar Akun</button>
            </form>
        </div>
    </div>
</x-app-layout>
