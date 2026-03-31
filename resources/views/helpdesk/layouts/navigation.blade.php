<nav x-data="{ open: false }" class="bg-white border-b border-slate-200 sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 sm:h-20">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
                        <div class="p-1.5 bg-indigo-600 rounded-lg group-hover:bg-slate-900 transition-colors shadow-lg shadow-indigo-200">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-black text-lg sm:text-xl text-slate-800 tracking-tight italic leading-none">Tanjungpura <span class="text-indigo-600">Net</span></span>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Parameter HelpDesk</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <div class="hidden space-x-4 sm:ms-10 sm:flex h-full">
                    @if(auth()->user()->role !== 'client')
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-sm font-bold uppercase tracking-widest">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('sites.index')" :active="request()->routeIs('sites.index')" class="text-sm font-bold uppercase tracking-widest">
                            {{ __('OLT Site') }}
                        </x-nav-link>
                        <x-nav-link :href="route('helpdesk.odps.index')" :active="request()->routeIs('helpdesk.odps.index')" class="text-sm font-bold uppercase tracking-widest">
                            {{ __('Titik ODP') }}
                        </x-nav-link>
                        <x-nav-link :href="route('helpdesk.map')" :active="request()->routeIs('helpdesk.map')" class="text-sm font-bold uppercase tracking-widest">
                            {{ __('Peta') }}
                        </x-nav-link>
                        <x-nav-link :href="route('helpdesk.users.index')" :active="request()->routeIs('helpdesk.users.index')" class="text-sm font-bold uppercase tracking-widest text-indigo-600">
                            {{ __('Admin') }}
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-sm font-bold uppercase tracking-widest">
                            {{ __('Status WiFi Anda') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 bg-slate-50 border border-slate-200 text-sm font-bold rounded-xl text-slate-700 hover:bg-slate-100 transition ease-in-out duration-150">
                            <div class="mr-2 p-1 bg-white rounded-md shadow-sm">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="ms-2 h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 text-xs text-slate-400 font-black uppercase border-b border-slate-100 mb-1">Akun Saya</div>
                        <x-dropdown-link :href="route('profile.edit')" class="text-sm font-bold flex items-center gap-2">
                             <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                             Pengaturan Profil
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" class="text-rose-600 text-sm font-bold flex items-center gap-2"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                Keluar Sistem
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2.5 rounded-xl text-slate-600 bg-slate-50 hover:bg-slate-100 transition duration-150 ease-in-out border border-slate-200">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile) -->
    <div x-show="open" @click.away="open = false" x-transition.origin.top class="sm:hidden bg-white border-b border-slate-200">
        <div class="pt-2 pb-3 space-y-1 px-4">
            @if(auth()->user()->role !== 'client')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="rounded-xl font-bold">
                    Monitoring Jaringan
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('sites.index')" :active="request()->routeIs('sites.index')" class="rounded-xl font-bold">
                    Kelola OLT Site
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('helpdesk.odps.index')" :active="request()->routeIs('helpdesk.odps.index')" class="rounded-xl font-bold">
                    Kelola Titik ODP
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('helpdesk.map')" :active="request()->routeIs('helpdesk.map')" class="rounded-xl font-bold">
                    Peta Satelit
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="rounded-xl font-bold text-center">
                    Dashboard Wi-Fi Saya
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-slate-100 bg-slate-50 px-4 mb-4 rounded-b-2xl mx-2 shadow-inner">
            <div class="flex items-center gap-3 py-2">
                <div class="p-2 bg-white rounded-xl shadow-sm">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div>
                    <div class="font-black text-slate-800 text-sm uppercase italic leading-tight">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-[9px] text-slate-400 uppercase tracking-widest leading-none">Parameter HelpDesk Platform</div>
                </div>
            </div>

            <div class="mt-4 space-y-1 pb-2">
                <x-responsive-nav-link :href="route('profile.edit')" class="rounded-lg text-sm font-bold flex items-center justify-between">
                    Profil Saya
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" class="text-rose-600 rounded-lg text-sm font-bold"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Logout / Keluar') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
