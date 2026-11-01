@extends('layouts.app', ['title' => 'Agenda Pemilihan'])

@section('content')
<div class="space-y-6">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Agenda Pemilihan</h1>
        </div>

        <a
            href="{{ route('events.create') }}"
            class="inline-flex items-center px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs sm:text-sm shadow-xs border border-slate-900 transition-colors cursor-pointer"
        >
            <span>Tambah Agenda</span>
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs">
        <form action="{{ route('events.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari judul agenda atau kode bilik..."
                    class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-colors"
                />
            </div>

            <div class="w-full md:w-48">
                <select
                    name="status"
                    onchange="this.form.submit()"
                    class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 bg-white"
                >
                    <option value="">Semua Status</option>
                    <option value="PUBLISHED" {{ request('status') === 'PUBLISHED' ? 'selected' : '' }}>Aktif</option>
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>Draft</option>
                    <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>Ditutup</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-semibold text-xs hover:bg-slate-800 active:bg-slate-950 transition-colors shadow-xs cursor-pointer">
                Terapkan
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('events.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold text-xs hover:bg-slate-200 transition-colors flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Events Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($events as $event)
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-xs hover:border-slate-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                <div>
                    <!-- Header: Eyebrow Category & Single Status Badge -->
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <span class="text-xs font-medium text-slate-500">
                            {{ $event->category_label }}
                        </span>

                        @if($event->status === 'PUBLISHED' && !$event->isAfterEndTime())
                            @if($event->isBeforeStartTime())
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200/80">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Menunggu Jadwal
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/80">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Aktif
                                </span>
                            @endif
                        @elseif($event->status === 'CLOSED' || $event->isAfterEndTime())
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 bg-slate-50 px-2.5 py-0.5 rounded-full border border-slate-200/80">
                                Ditutup
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Draft
                            </span>
                        @endif
                    </div>

                    <!-- Title -->
                    <h3 class="text-base sm:text-lg font-semibold text-slate-900 mb-2.5 line-clamp-2 leading-snug tracking-tight">
                        {{ $event->title }}
                    </h3>

                    <!-- Horizontal Metadata Byline (Target Scope, Room Code, Creator) -->
                    <div class="flex items-center gap-2 text-xs text-slate-500 mb-3 flex-wrap">
                        <span class="inline-flex items-center gap-1 font-medium text-slate-600">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>{{ $event->target_scope_short_label }}</span>
                        </span>
                        <span class="text-slate-300">•</span>
                        <span class="font-mono text-xs font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                            #{{ $event->room_code }}
                        </span>
                        @if(auth()->user()->isSuperAdmin() && $event->creator)
                            <span class="text-slate-300">•</span>
                            <span class="truncate max-w-[130px] text-slate-500 font-normal" title="Dibuat oleh: {{ $event->creator->nama }}">
                                {{ $event->creator->nama }}
                            </span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed mb-4">
                        {{ $event->description ?? 'Tidak ada deskripsi.' }}
                    </p>

                    <!-- Schedule Meta (WIB) -->
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 mb-3.5 font-medium bg-slate-50/70 px-2.5 py-1.5 rounded-lg border border-slate-100">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="truncate tabular-nums">
                            @if($event->start_time && $event->end_time)
                                @if(now()->between($event->start_time, $event->end_time) && $event->status === 'PUBLISHED')
                                    <span class="text-emerald-700 font-medium">Sekarang &ndash; {{ $event->end_time->isToday() ? $event->end_time->format('H:i') : $event->end_time->format('d/m/y H:i') }} WIB</span>
                                @elseif($event->isAfterEndTime() || $event->status === 'CLOSED')
                                    Selesai &bull; {{ $event->end_time->format('d/m/y H:i') }} WIB
                                @else
                                    @if($event->start_time->isSameDay($event->end_time))
                                        {{ $event->start_time->format('d/m/y') }} &bull; {{ $event->start_time->format('H:i') }} &ndash; {{ $event->end_time->format('H:i') }} WIB
                                    @else
                                        {{ $event->start_time->format('d/m/y H:i') }} &ndash; {{ $event->end_time->format('d/m/y H:i') }} WIB
                                    @endif
                                @endif
                            @else
                                Jadwal belum diatur
                            @endif
                        </span>
                    </div>

                    <!-- Meta -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between text-xs mb-5">
                        <div class="flex items-center gap-1.5">
                            <span class="font-semibold text-slate-900 tabular-nums">{{ $event->candidates_count }}</span>
                            <span class="text-slate-500 font-medium">Paslon</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-semibold text-slate-900 tabular-nums">{{ $event->votes_count }}</span>
                            <span class="text-slate-500 font-medium">Suara Masuk</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a
                        href="{{ route('events.monitoring', $event) }}"
                        class="flex-1 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 hover:text-slate-900 font-medium text-xs flex items-center justify-center gap-1.5 transition-colors shadow-2xs"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Monitoring</span>
                    </a>
                    <a
                        href="{{ route('events.show', $event) }}"
                        class="flex-1 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-medium text-xs flex items-center justify-center transition-colors shadow-xs"
                    >
                        <span>Detail</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-16 bg-white rounded-2xl border border-dashed border-slate-300">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="text-base font-bold text-slate-700 mb-1">Belum Ada Agenda</h3>
                <p class="text-xs text-slate-400 mb-4">Belum ada agenda pemilihan yang sesuai pencarian atau terdaftar.</p>
                <a href="{{ route('events.create') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs shadow-xs border border-slate-900 transition-colors">
                    <span>Tambah Agenda</span>
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $events->links() }}
    </div>
</div>
@endsection
