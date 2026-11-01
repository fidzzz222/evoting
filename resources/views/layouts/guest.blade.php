<x-layouts.base :title="$title ?? 'E-Voting Sekolah'">
    <div class="min-h-full flex flex-col bg-slate-50 text-slate-800 relative selection:bg-slate-900 selection:text-white">
        {{-- Navbar Modern, Clean & Solid --}}
        <header class="border-b border-slate-200 bg-white sticky top-0 z-40 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo E-Voting" class="h-9 w-auto object-contain shrink-0 transition-transform duration-200 group-hover:scale-105">
                    <span class="font-bold text-base tracking-tight text-slate-900 group-hover:text-blue-600 transition-colors">E-VOTING SEKOLAH</span>
                </a>
            </div>
        </header>

        {{-- Konten Utama --}}
        <main class="flex-grow flex flex-col justify-center py-6 sm:py-10">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>
</x-layouts.base>
