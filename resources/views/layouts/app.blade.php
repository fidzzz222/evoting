<x-layouts.base :title="$title ?? 'Panel Panitia'">
    <div x-data="{ sidebarOpen: false }" class="min-h-full flex bg-slate-50">
        {{-- Mobile sidebar backdrop --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden" style="display: none;"></div>

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-white flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 shadow-2xl">
            {{-- Brand header --}}
            <div class="h-20 px-6 flex items-center justify-between border-b border-slate-800 bg-slate-950">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-xl bg-slate-800/90 border border-slate-700/80 flex items-center justify-center text-blue-400 shadow-xs group-hover:border-blue-500/50 group-hover:text-blue-300 transition-all">
                        <svg class="w-5 h-5 text-blue-400 group-hover:text-blue-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 9V5a2 2 0 012-2h6a2 2 0 012 2v4" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 5.5l1.5 1.5 3-3" />
                            <rect x="3" y="9" width="18" height="12" rx="2.5" stroke-width="1.8" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14h6" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-base font-extrabold tracking-tight text-white group-hover:text-blue-200 transition-colors">E-VOTING</span>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20">SCH</span>
                        </div>
                        <span class="text-[11px] font-medium tracking-wide text-slate-400 block -mt-0.5 uppercase">
                            {{ Auth::guard('web')->user()->isSuperAdmin() ? 'Panel Administrator' : 'Panel Panitia' }}
                        </span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            {{-- Nav links --}}
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                <div class="px-3 pb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Navigasi Utama</div>

                {{-- Dashboard --}}
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center pl-3 pr-3.5 py-3 rounded-r-xl text-sm transition-colors duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white border-l-4 border-blue-500 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 border-l-4 border-transparent font-medium' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.dashboard') ? 'text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard Utama
                </a>

                {{-- Events / Pemilihan --}}
                <a href="{{ route('events.index') }}"
                   class="flex items-center pl-3 pr-3.5 py-3 rounded-r-xl text-sm transition-colors duration-150 {{ request()->routeIs('events.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 border-l-4 border-transparent font-medium' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('events.*') ? 'text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Data Pemilihan (Events)
                </a>

                {{-- Super Admin Only Menus --}}
                @if(Auth::guard('web')->user()->isSuperAdmin())
                    {{-- Master DPT Pemilih --}}
                    <a href="{{ route('admin.voters.index') }}"
                       class="flex items-center pl-3 pr-3.5 py-3 rounded-r-xl text-sm transition-colors duration-150 {{ request()->routeIs('admin.voters.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 border-l-4 border-transparent font-medium' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.voters.*') ? 'text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Master DPT Pemilih
                    </a>

                    {{-- Manajemen Pengguna & Petugas --}}
                    <a href="{{ route('admin.users.index') }}"
                       class="flex items-center pl-3 pr-3.5 py-3 rounded-r-xl text-sm transition-colors duration-150 {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 border-l-4 border-transparent font-medium' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.users.*') ? 'text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Panitia & Pengguna
                    </a>

                    {{-- Log Audit Keamanan --}}
                    <a href="{{ route('admin.logs.index') }}"
                       class="flex items-center pl-3 pr-3.5 py-3 rounded-r-xl text-sm transition-colors duration-150 {{ request()->routeIs('admin.logs.*') ? 'bg-slate-800 text-white border-l-4 border-blue-500 font-semibold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 border-l-4 border-transparent font-medium' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.logs.*') ? 'text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Log Audit Keamanan
                    </a>
                @endif
            </nav>

            {{-- User card bottom --}}
            <div class="p-4 border-t border-slate-800 bg-slate-950">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <div class="w-10 h-10 rounded-xl bg-slate-800 text-slate-200 font-bold flex items-center justify-center shrink-0 border border-slate-700">
                            {{ substr(Auth::guard('web')->user()->nama, 0, 1) }}
                        </div>
                        <div class="truncate">
                            <div class="text-sm font-semibold text-white truncate">{{ Auth::guard('web')->user()->nama }}</div>
                            <div class="text-xs text-slate-400 font-medium">
                                {{ Auth::guard('web')->user()->isSuperAdmin() ? 'Super Admin' : 'Petugas Pemilihan' }}
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Logout" class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Content Area --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            {{-- Mobile sidebar toggle --}}
            <div class="lg:hidden sticky top-0 z-30 h-14 bg-white/90 backdrop-blur-md border-b border-slate-200 px-4 flex items-center shadow-sm">
                <button @click="sidebarOpen = true" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>

            {{-- Main page slot --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>
</x-layouts.base>
