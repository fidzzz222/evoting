@extends('layouts.app', ['title' => Auth::user()->isSuperAdmin() ? 'Dashboard Admin' : 'Dashboard Panitia'])

@section('content')
<div class="space-y-8">
    <!-- Welcome Header Banner - Clean Surface -->
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/90 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                    Selamat Datang, {{ $user->nama }}!
                </h1>
            </div>
        </div>
    </div>

    <!-- KPI Metric Cards Grid - Focused on Election Lifecycle -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-stat-card
            title="Total Agenda"
            :value="$totalEvents"
            color="blue"
            icon='<svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><line x1="16" y1="2" x2="16" y2="6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><line x1="8" y1="2" x2="8" y2="6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><line x1="3" y1="10" x2="21" y2="10" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><circle cx="8" cy="14" r="1" fill="currentColor" /><circle cx="12" cy="14" r="1" fill="currentColor" /><circle cx="16" cy="14" r="1" fill="currentColor" /><circle cx="8" cy="17.5" r="1" fill="currentColor" /><circle cx="12" cy="17.5" r="1" fill="currentColor" /></svg>'
        />

        <x-stat-card
            title="Bilik Suara Aktif"
            :value="$activeEventsCount"
            color="emerald"
            icon='<svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><rect x="7" y="2.5" width="10" height="8" rx="1.5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M10 6.5l1.5 1.5 3-3" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" /><line x1="2.5" y1="11.5" x2="21.5" y2="11.5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M4.5 11.5v7.5a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-7.5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        />

        <x-stat-card
            title="Draft"
            :value="$draftEventsCount"
            color="amber"
            icon='<svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2.5H6a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-12l-6-5Z" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M14 2.5v5h5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><line x1="8" y1="12.5" x2="13" y2="12.5" stroke-width="1.8" stroke-linecap="round" stroke-dasharray="2 2" /><line x1="8" y1="16.5" x2="16" y2="16.5" stroke-width="1.8" stroke-linecap="round" stroke-dasharray="2 2" /></svg>'
        />

        <x-stat-card
            title="Pemilihan Selesai"
            :value="$closedEventsCount"
            color="slate"
            icon='<svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3.5" width="18" height="5" rx="1.5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M4.5 8.5v10a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-10" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M9 14.5l2 2 4-4" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        />
    </div>

    <!-- Active & Managed Elections Grid -->
    <div>
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">
                {{ $user->isSuperAdmin() ? 'Agenda Pemilihan' : 'Agenda Pemilihan Anda' }}
            </h2>
            <a href="{{ route('events.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors inline-flex items-center">
                <span>Lihat Semua</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($events as $event)
                @php
                    $targetDpt = $event->eligibleVotersCount();
                    $turnoutRate = $targetDpt > 0 ? min(100, round(($event->votes_count / $targetDpt) * 100, 1)) : 0;
                @endphp
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
                            @elseif($event->status === 'DRAFT')
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200/80">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Draft
                                </span>
                            @else
                                <span class="inline-flex items-center text-xs font-medium text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200/80">
                                    {{ $event->status }}
                                </span>
                            @endif
                        </div>

                        <!-- Title -->
                        <h3 class="text-base sm:text-lg font-semibold text-slate-900 mb-2.5 line-clamp-2 leading-snug tracking-tight">
                            {{ $event->title }}
                        </h3>

                        <!-- Horizontal Metadata Byline (Target Scope, Room Code, Creator) -->
                        <div class="flex items-center gap-2 text-xs text-slate-500 mb-5 flex-wrap">
                            <span class="inline-flex items-center gap-1 font-medium text-slate-600">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="9" cy="7" r="4" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>{{ $event->target_scope_short_label }}</span>
                            </span>
                            <span class="text-slate-300">•</span>
                            <span class="font-mono text-xs font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                                #{{ $event->room_code }}
                            </span>
                            @if($user->isSuperAdmin() && $event->creator)
                                <span class="text-slate-300">•</span>
                                <span class="truncate max-w-[130px] text-slate-500 font-normal" title="Dibuat oleh: {{ $event->creator->nama }}">
                                    {{ $event->creator->nama }}
                                </span>
                            @endif
                        </div>

                        <!-- Turnout Stats Bar -->
                        <div class="space-y-1.5 mb-5 text-xs">
                            <div class="flex items-center justify-between font-medium">
                                <span class="text-slate-500">Suara Masuk</span>
                                <span class="text-slate-700">
                                    <span class="font-semibold text-slate-900 tabular-nums">{{ $event->votes_count }}</span>
                                    <span class="text-slate-400 font-normal">/</span>
                                    <span class="font-semibold text-slate-900 tabular-nums">{{ $targetDpt }}</span>
                                    <span class="text-slate-500 font-normal ml-0.5">pemilih</span>
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden border border-slate-200/60">
                                <div class="bg-blue-600 h-full rounded-full transition-all duration-500" style="width: {{ $turnoutRate }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                                <div class="flex items-center gap-1">
                                    <span class="font-semibold text-slate-800 tabular-nums">{{ $event->candidates_count }}</span>
                                    <span>Paslon</span>
                                </div>
                                <span class="font-semibold text-slate-700 tabular-nums">{{ $turnoutRate }}% Partisipasi</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-4 border-t border-slate-100 grid grid-cols-2 gap-2">
                        <a
                            href="{{ route('events.monitoring', $event) }}"
                            class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 hover:text-slate-900 font-medium text-xs flex items-center justify-center gap-1.5 transition-colors shadow-2xs"
                        >
                            <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="3.5" y="13" width="4" height="7" rx="1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                <rect x="10" y="5" width="4" height="15" rx="1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                <rect x="16.5" y="9" width="4" height="11" rx="1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>Monitoring</span>
                        </a>
                        <a
                            href="{{ route('events.show', $event) }}"
                            class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-medium text-xs flex items-center justify-center transition-colors shadow-xs"
                        >
                            <span>Kelola</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 px-6 bg-white rounded-3xl border border-dashed border-slate-300">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-500 border border-slate-200 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="17" rx="2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <line x1="16" y1="2" x2="16" y2="6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <line x1="8" y1="2" x2="8" y2="6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <line x1="3" y1="10" x2="21" y2="10" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <line x1="12" y1="13.5" x2="12" y2="17.5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <line x1="10" y1="15.5" x2="14" y2="15.5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Belum Ada Agenda Pemilihan</h3>
                    <p class="text-xs text-slate-500 mt-1 mb-4 max-w-sm mx-auto">
                        Belum ada agenda pemilihan yang dibuat. Buat agenda baru untuk memulai pemungutan suara.
                    </p>
                    <a href="{{ route('events.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs shadow-xs border border-slate-900 transition-colors">
                        <span>Buat Agenda Pemilihan</span>
                    </a>
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
