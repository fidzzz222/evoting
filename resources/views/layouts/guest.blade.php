<x-layouts.base :title="$title ?? 'E-Voting Sekolah'">
    <div class="min-h-full flex flex-col justify-between bg-slate-50 text-slate-800 relative selection:bg-slate-900 selection:text-white">
        {{-- Navbar Modern, Clean & Solid --}}
        <header class="border-b border-slate-200 bg-white sticky top-0 z-40 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center border border-slate-800 shadow-xs transition-colors group-hover:bg-slate-800">
                        <svg class="w-5 h-5 text-blue-400 group-hover:text-blue-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 9V5a2 2 0 012-2h6a2 2 0 012 2v4" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 5.5l1.5 1.5 3-3" />
                            <rect x="3" y="9" width="18" height="12" rx="2.5" stroke-width="1.8" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14h6" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-black text-base tracking-tight text-slate-900 group-hover:text-blue-600 transition-colors">E-VOTING SEKOLAH</span>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">SCH</span>
                        </div>
                        <div class="text-[10px] text-slate-500 font-bold tracking-widest uppercase">Demokrasi Digital &bull; LUBER JURDIL</div>
                    </div>
                </a>

                @if(Auth::guard('web')->check() || Auth::guard('voter')->check())
                    <nav class="flex items-center space-x-2 sm:space-x-3">
                        @if(Auth::guard('web')->check())
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-xl border border-slate-800 shadow-xs transition-colors">
                                <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                Panel Panitia
                            </a>
                        @elseif(Auth::guard('voter')->check())
                            <a href="{{ route('voter.index') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl border border-blue-700 shadow-xs transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 9V5a2 2 0 012-2h6a2 2 0 012 2v4" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 5.5l1.5 1.5 3-3" />
                                    <rect x="3" y="9" width="18" height="12" rx="2.5" stroke-width="1.8" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14h6" />
                                </svg>
                                Bilik Suara ({{ Auth::guard('voter')->user()->nama }})
                            </a>
                        @endif
                    </nav>
                @endif
            </div>
        </header>

        {{-- Konten Utama --}}
        <main class="flex-grow flex flex-col justify-center">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        {{-- Footer LUBER JURDIL --}}
        <footer class="border-t border-slate-200 py-6 text-center text-xs text-slate-500 bg-white">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center space-x-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-slate-600">Asas Pemilihan: <strong class="text-slate-800">LUBER JURDIL</strong> (Langsung, Umum, Bebas, Rahasia, Jujur & Adil)</span>
                </div>
                <div class="text-slate-400 font-medium">
                    &copy; {{ date('Y') }} E-Voting Sekolah. Sistem Pemilihan Digital Terenkripsi & Anonim.
                </div>
            </div>
        </footer>
    </div>
</x-layouts.base>
