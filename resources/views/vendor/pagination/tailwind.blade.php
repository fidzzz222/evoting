@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <!-- Info Ringkasan Data -->
        <div class="text-xs text-slate-500 font-medium">
            <span>Menampilkan</span>
            @if ($paginator->firstItem())
                <span class="font-semibold text-slate-800 font-mono">{{ $paginator->firstItem() }}</span>
                <span>sampai</span>
                <span class="font-semibold text-slate-800 font-mono">{{ $paginator->lastItem() }}</span>
            @else
                <span class="font-semibold text-slate-800 font-mono">{{ $paginator->count() }}</span>
            @endif
            <span>dari</span>
            <span class="font-semibold text-slate-800 font-mono">{{ $paginator->total() }}</span>
            <span>data</span>
        </div>

        <!-- Tombol Pagination -->
        <div class="flex items-center gap-1.5 self-center sm:self-auto">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="Halaman sebelumnya" class="inline-flex items-center justify-center h-8 px-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-300 text-xs font-semibold cursor-not-allowed select-none">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden sm:inline-block ml-1">Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya" class="inline-flex items-center justify-center h-8 px-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 hover:text-slate-900 text-xs font-semibold transition-colors shadow-2xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden sm:inline-block ml-1">Sebelumnya</span>
                </a>
            @endif

            {{-- Elemen Angka Halaman --}}
            <div class="flex items-center gap-1">
                @foreach ($elements as $element)
                    {{-- Separator Titik Tiga --}}
                    @if (is_string($element))
                        <span aria-disabled="true" class="inline-flex items-center justify-center min-w-8 h-8 px-2 text-xs font-medium text-slate-400 select-none">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Daftar Link Angka --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex items-center justify-center min-w-8 h-8 px-2.5 rounded-lg border border-slate-900 bg-slate-900 text-white text-xs font-bold font-mono shadow-2xs select-none">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}" class="inline-flex items-center justify-center min-w-8 h-8 px-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 hover:text-slate-900 text-xs font-semibold font-mono transition-colors shadow-2xs">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Tombol Selanjutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman selanjutnya" class="inline-flex items-center justify-center h-8 px-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 hover:text-slate-900 text-xs font-semibold transition-colors shadow-2xs">
                    <span class="hidden sm:inline-block mr-1">Selanjutnya</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span aria-disabled="true" aria-label="Halaman selanjutnya" class="inline-flex items-center justify-center h-8 px-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-300 text-xs font-semibold cursor-not-allowed select-none">
                    <span class="hidden sm:inline-block mr-1">Selanjutnya</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
