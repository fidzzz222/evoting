<x-layouts.base :title="$title ?? 'Bilik Suara Pemilih'">
    <div class="min-h-full flex flex-col justify-between bg-slate-50 text-slate-800 relative selection:bg-blue-600 selection:text-white">
        {{-- Header Bilik Suara Clean, Solid & Modern --}}
        <header class="border-b border-slate-200 bg-white sticky top-0 z-30 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo E-Voting" class="h-9 w-auto object-contain shrink-0">
                    <div>
                        <span class="text-sm font-bold tracking-tight text-slate-900 block">E-VOTING</span>
                        <span class="text-[11px] font-medium text-slate-500 block -mt-0.5">Bilik Suara Pemilih</span>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    {{-- Voter Badge & Nav --}}
                    @if(Auth::guard('voter')->check())
                        @php $voter = Auth::guard('voter')->user(); @endphp
                        
                        <nav class="flex items-center gap-1 sm:gap-1.5 mr-1 sm:mr-2">
                            <a href="{{ route('voter.index') }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('voter.index') ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                <span class="hidden sm:inline">Bilik</span>
                            </a>
                            <a href="{{ route('voter.history') }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ request()->routeIs('voter.history') ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Riwayat & Log</span>
                            </a>
                        </nav>

                        <div class="hidden md:flex items-center space-x-2.5 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 shadow-2xs">
                            <div class="w-7 h-7 rounded-lg bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs shrink-0">
                                {{ substr($voter->nama, 0, 1) }}
                            </div>
                            <div class="text-left">
                                <div class="text-xs font-semibold text-slate-900 leading-tight">{{ $voter->nama }}</div>
                                <div class="text-[11px] text-slate-500 font-medium leading-tight">
                                    {{ $voter->type_label ?? $voter->type }} &bull; {{ $voter->kelas ?? $voter->identifier }}
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('voter.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-medium text-slate-700 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 shadow-2xs transition-colors cursor-pointer">
                                Keluar
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </header>

        {{-- Main Booth Content --}}
        <main class="flex-grow max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 flex flex-col justify-center">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>
</x-layouts.base>
