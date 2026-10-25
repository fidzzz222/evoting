<x-layouts.base :title="$title ?? 'Bilik Suara Pemilih'">
    <div class="min-h-full flex flex-col justify-between bg-slate-50 text-slate-800 relative selection:bg-blue-600 selection:text-white">
        {{-- Header Bilik Suara Clean, Solid & Modern --}}
        <header class="border-b border-slate-200 bg-white sticky top-0 z-30 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center border border-slate-800 shadow-xs">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 9V5a2 2 0 012-2h6a2 2 0 012 2v4" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 5.5l1.5 1.5 3-3" />
                            <rect x="3" y="9" width="18" height="12" rx="2.5" stroke-width="1.8" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14h6" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-black text-base tracking-tight text-slate-900 flex items-center gap-2">
                            BILIK SUARA ELEKTRONIK
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                RAHASIA & SAH
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 font-medium">Sistem Pemilihan Terenkripsi & Anonim</div>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    {{-- Voter Badge --}}
                    @if(Auth::guard('voter')->check())
                        @php $voter = Auth::guard('voter')->user(); @endphp
                        <div class="hidden sm:flex items-center space-x-3 bg-white border border-slate-200 rounded-xl px-3.5 py-1.5 shadow-xs">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 font-black flex items-center justify-center text-xs">
                                {{ substr($voter->nama, 0, 1) }}
                            </div>
                            <div class="text-left">
                                <div class="text-xs font-bold text-slate-900 leading-tight">{{ $voter->nama }}</div>
                                <div class="text-[11px] text-blue-600 font-semibold leading-tight">
                                    {{ $voter->type }} &bull; {{ $voter->kelas ?? $voter->identifier }}
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('voter.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-rose-600 bg-white hover:bg-rose-50 border border-slate-200 hover:border-rose-200 shadow-xs transition-colors cursor-pointer">
                                <svg class="w-4 h-4 text-slate-400 group-hover:text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Keluar Sesi
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

        {{-- Security Footer --}}
        <footer class="border-t border-slate-200 py-4 text-center text-xs text-slate-500 bg-white">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-600 inline" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>Jaminan Keamanan: <strong>Pilihan Surat Suara bersifat Anonim</strong> & tidak dapat diakses siapapun.</span>
                </div>
                <div class="text-slate-400 font-medium">
                    Protokol Demokrasi Digital Sekolah &bull; Hak Suara Tunggal Terverifikasi
                </div>
            </div>
        </footer>
    </div>
</x-layouts.base>
