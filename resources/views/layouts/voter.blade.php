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
                    {{-- Voter Badge --}}
                    @if(Auth::guard('voter')->check())
                        @php $voter = Auth::guard('voter')->user(); @endphp
                        <div class="hidden sm:flex items-center space-x-2.5 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 shadow-2xs">
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
