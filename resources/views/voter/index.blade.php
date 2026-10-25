@extends('layouts.voter', ['title' => 'Portal Pemilih'])

@section('content')
<div class="space-y-8">
    <!-- Welcome Voter Card Solid Agency-Grade -->
    <div class="bg-slate-900 rounded-2xl p-6 sm:p-8 text-white border border-slate-800 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xl text-blue-400">
                    {{ substr($voter->nama, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold uppercase tracking-wider bg-slate-800 border border-slate-700 px-2.5 py-0.5 rounded-full text-blue-400">
                            {{ $voter->type_label }}
                        </span>
                        @if($voter->kelas)
                            <span class="text-xs font-semibold bg-slate-800 border border-slate-700 px-2 py-0.5 rounded-full text-slate-300">
                                {{ $voter->kelas }}
                            </span>
                        @endif
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white">{{ $voter->nama }}</h2>
                    <p class="text-xs text-slate-400 font-mono mt-0.5">Identitas DPT: {{ $voter->identifier }}</p>
                </div>
            </div>

            <!-- Fast Room Code Input inside Banner -->
            <form action="{{ route('voter.room.join') }}" method="POST" class="bg-slate-800 p-1.5 rounded-xl border border-slate-700 flex items-center gap-2">
                @csrf
                <input 
                    type="text" 
                    name="room_code" 
                    placeholder="KODE BILIK (6 DIGIT)" 
                    maxlength="6" 
                    required
                    class="px-3.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs font-mono font-bold tracking-widest uppercase placeholder-slate-500 focus:outline-none focus:border-blue-500 w-44"
                />
                <button type="submit" class="px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs transition-colors shrink-0 cursor-pointer">
                    Masuk &rarr;
                </button>
            </form>
        </div>
    </div>

    <!-- Active Elections Section -->
    <div>
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-bold text-slate-900 tracking-tight">Daftar Agenda Pemilihan</h3>
                <p class="text-xs text-slate-500">Pilih bilik suara yang sedang aktif untuk menyalurkan hak suara Anda</p>
            </div>
            <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">
                Total {{ $events->count() }} Agenda
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($events as $event)
                @php
                    $hasVoted = isset($participations[$event->id]);
                    $isActive = $event->status === 'PUBLISHED';
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-xs hover:border-slate-300 transition-colors flex flex-col justify-between group">
                    <div>
                        <!-- Header Eyebrow & Status Badge (One-Badge Rule) -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                {{ $event->category_label }}
                            </span>
                            
                            @if($hasVoted)
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Sudah Memilih
                                </span>
                            @elseif($isActive)
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full border border-blue-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                    Belum Memilih
                                </span>
                            @else
                                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full border border-slate-200">
                                    {{ $event->status_label }}
                                </span>
                            @endif
                        </div>

                        <!-- Event Title -->
                        <h4 class="text-xl font-bold text-slate-900 group-hover:text-blue-600 transition-colors mb-2">
                            {{ $event->title }}
                        </h4>
                        
                        <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed mb-4">
                            {{ $event->description ?? 'Pemilihan resmi OSIS/MPK tingkat sekolah.' }}
                        </p>

                        <!-- Event Meta -->
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-center justify-between text-xs text-slate-600 mb-5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-400">Kode:</span>
                                <span class="font-mono font-bold text-slate-800 bg-white px-2 py-0.5 rounded border border-slate-200">
                                    #{{ $event->room_code }}
                                </span>
                            </div>
                            <div>
                                <span class="font-bold text-slate-400">Kandidat:</span>
                                <span class="font-semibold text-slate-800">{{ $event->candidates_count }} Paslon</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div>
                        @if($hasVoted)
                            <a 
                                href="{{ route('voter.voted', $event->room_code) }}" 
                                class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 font-semibold text-xs flex items-center justify-center gap-2 transition-colors shadow-xs"
                            >
                                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Lihat Status Bukti Partisipasi</span>
                            </a>
                        @elseif($isActive)
                            <a 
                                href="{{ route('voter.booth', $event->room_code) }}" 
                                class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-xs transition-colors cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 9V5a2 2 0 012-2h6a2 2 0 012 2v4" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 5.5l1.5 1.5 3-3" />
                                    <rect x="3" y="9" width="18" height="12" rx="2.5" stroke-width="1.8" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14h6" />
                                </svg>
                                <span>Buka Bilik Suara & Pilih Sekarang</span>
                            </a>
                        @else
                            <button 
                                disabled 
                                class="w-full py-2.5 px-4 rounded-xl bg-slate-100 text-slate-400 font-semibold text-xs flex items-center justify-center gap-1.5 cursor-not-allowed border border-slate-200"
                            >
                                Pemilihan Sedang Tidak Aktif
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-2 text-center py-12 bg-white rounded-2xl border border-dashed border-slate-300">
                    <p class="text-sm font-semibold text-slate-500">Belum ada agenda pemilihan yang terdaftar saat ini.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
