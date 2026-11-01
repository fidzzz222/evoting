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
        <aside :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 border-r border-slate-800 flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 lg:shadow-none">
            {{-- Brand header --}}
            <div class="h-16 px-5 flex items-center justify-between border-b border-slate-800 bg-slate-900">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 group">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo E-Voting" class="h-9 w-auto object-contain shrink-0 transition-transform duration-200 group-hover:scale-105">
                    <div>
                        <span class="text-sm font-bold tracking-tight text-white group-hover:text-blue-300 transition-colors block">E-VOTING</span>
                        <span class="text-[10px] font-medium tracking-wide text-slate-400 block -mt-0.5 uppercase">
                            {{ Auth::guard('web')->user()->isSuperAdmin() ? 'Panel Administrator' : 'Panel Panitia' }}
                        </span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            {{-- Nav links --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <div class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Navigasi Utama</div>

                {{-- Dashboard --}}
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors duration-150 group {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white font-semibold border border-slate-700/60 shadow-2xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-medium' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-blue-400' : 'text-slate-400 group-hover:text-slate-200' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="3" width="8" height="9" rx="1.5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        <rect x="13" y="3" width="8" height="7" rx="1.5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        <rect x="13" y="12" width="8" height="9" rx="1.5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        <rect x="3" y="14" width="8" height="7" rx="1.5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Dashboard
                </a>

                {{-- Events / Pemilihan --}}
                <a href="{{ route('events.index') }}"
                   class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors duration-150 group {{ request()->routeIs('events.*') ? 'bg-slate-800 text-white font-semibold border border-slate-700/60 shadow-2xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-medium' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0 {{ request()->routeIs('events.*') ? 'text-blue-400' : 'text-slate-400 group-hover:text-slate-200' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="17" rx="2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        <line x1="16" y1="2" x2="16" y2="6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        <line x1="8" y1="2" x2="8" y2="6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        <line x1="3" y1="10" x2="21" y2="10" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M9 15.5l2 2 4.5-4.5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Agenda Pemilihan
                </a>

                {{-- Super Admin Only Menus --}}
                @if(Auth::guard('web')->user()->isSuperAdmin())
                    {{-- Data Pemilih --}}
                    <a href="{{ route('admin.voters.index') }}"
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors duration-150 group {{ request()->routeIs('admin.voters.*') ? 'bg-slate-800 text-white font-semibold border border-slate-700/60 shadow-2xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-medium' }}">
                        <svg class="w-5 h-5 mr-3 shrink-0 {{ request()->routeIs('admin.voters.*') ? 'text-blue-400' : 'text-slate-400 group-hover:text-slate-200' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="2.5" y="4.5" width="19" height="15" rx="2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="8" cy="10" r="2.2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M5.5 15.5a2.5 2.5 0 0 1 5 0" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <line x1="13.5" y1="9.5" x2="18.5" y2="9.5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <line x1="13.5" y1="13" x2="16.5" y2="13" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Data Pemilih
                    </a>

                    {{-- Panitia & Admin --}}
                    <a href="{{ route('admin.users.index') }}"
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors duration-150 group {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white font-semibold border border-slate-700/60 shadow-2xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-medium' }}">
                        <svg class="w-5 h-5 mr-3 shrink-0 {{ request()->routeIs('admin.users.*') ? 'text-blue-400' : 'text-slate-400 group-hover:text-slate-200' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 2.5 19 6v5.5c0 5-3.5 9.5-7 10.5-3.5-1-7-5.5-7-10.5V6l7-3.5Z" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="12" cy="10" r="2.2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M8 16.5a4 4 0 0 1 8 0" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Panitia & Admin
                    </a>

                    {{-- Log Audit --}}
                    <a href="{{ route('admin.logs.index') }}"
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors duration-150 group {{ request()->routeIs('admin.logs.*') ? 'bg-slate-800 text-white font-semibold border border-slate-700/60 shadow-2xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 font-medium' }}">
                        <svg class="w-5 h-5 mr-3 shrink-0 {{ request()->routeIs('admin.logs.*') ? 'text-blue-400' : 'text-slate-400 group-hover:text-slate-200' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="9" y="2" width="6" height="3.5" rx="1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <line x1="8" y1="10" x2="16" y2="10" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M9 15.5l2 2 4.5-4.5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Log Audit
                    </a>
                @endif
            </nav>

            {{-- User card bottom --}}
            <div class="p-3 border-t border-slate-800 bg-slate-900">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2.5 overflow-hidden">
                        <div class="w-8 h-8 rounded-lg bg-slate-800 text-slate-200 font-semibold border border-slate-700/80 flex items-center justify-center shrink-0 text-xs shadow-2xs">
                            {{ substr(Auth::guard('web')->user()->nama, 0, 1) }}
                        </div>
                        <div class="truncate min-w-0">
                            <div class="text-xs font-semibold text-white truncate">{{ Auth::guard('web')->user()->nama }}</div>
                            <div class="text-[11px] text-slate-400 font-medium truncate">
                                {{ Auth::guard('web')->user()->isSuperAdmin() ? 'Super Admin' : 'Petugas' }}
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Logout" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Content Area --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            {{-- Mobile sidebar toggle --}}
            <div class="lg:hidden sticky top-0 z-30 h-14 bg-slate-900 border-b border-slate-800 px-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <button @click="sidebarOpen = true" class="p-1.5 -ml-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2.5">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo E-Voting" class="h-7 w-auto object-contain shrink-0">
                        <span class="text-sm font-bold text-white tracking-tight">E-VOTING</span>
                    </a>
                </div>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-800 text-slate-300 border border-slate-700">
                    {{ Auth::guard('web')->user()->isSuperAdmin() ? 'Admin' : 'Panitia' }}
                </span>
            </div>

            {{-- Main page slot --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>
</x-layouts.base>
