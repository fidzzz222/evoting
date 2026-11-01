@extends('layouts.voter', ['title' => 'Portal Pemilih'])

@section('content')
<div class="space-y-8">
    <!-- Welcome Voter Card Solid Agency-Grade -->
    <div class="bg-slate-900 rounded-2xl p-6 sm:p-8 text-white border border-slate-800 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-lg text-slate-200">
                    {{ substr($voter->nama, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-medium bg-slate-800 border border-slate-700 px-2.5 py-0.5 rounded-full text-slate-300">
                            {{ $voter->type_label }}
                        </span>
                        @if($voter->kelas)
                            <span class="text-xs font-medium bg-slate-800 border border-slate-700 px-2 py-0.5 rounded-full text-slate-400">
                                {{ $voter->kelas }}
                            </span>
                        @endif
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white">{{ $voter->nama }}</h2>
                    <p class="text-xs text-slate-400 font-mono mt-0.5">DPT: {{ $voter->identifier }}</p>
                </div>
            </div>

            <!-- Fast Room Code Input inside Banner -->
            <form action="{{ route('voter.room.join') }}" method="POST" class="bg-slate-800 p-1.5 rounded-xl border border-slate-700 flex items-center gap-2">
                @csrf
                <input 
                    type="text" 
                    name="room_code" 
                    placeholder="Kode bilik (6 digit)" 
                    maxlength="6" 
                    required
                    class="px-3.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs font-mono font-medium tracking-wider uppercase placeholder-slate-500 focus:outline-none focus:border-slate-500 w-44"
                />
                <button type="submit" class="px-3.5 py-2 rounded-lg bg-white hover:bg-slate-100 active:bg-slate-200 text-slate-900 font-semibold text-xs transition-colors shrink-0 cursor-pointer">
                    Masuk
                </button>
            </form>
        </div>
    </div>

    <!-- Active Elections Section -->
    <div>
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-bold text-slate-900 tracking-tight">Daftar Agenda Pemilihan</h3>
                <p class="text-xs text-slate-500">Pilih bilik suara aktif untuk menyalurkan hak suara Anda</p>
            </div>
            <span class="text-xs font-medium text-slate-600 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">
                Total {{ $events->count() }} agenda
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($events as $event)
                @php
                    $hasVoted = isset($participations[$event->id]);
                    $isActive = $event->status === 'PUBLISHED';
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-xs hover:border-slate-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                    <div>
                        <!-- Header Eyebrow & Status Badge (One-Badge Rule) -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-medium text-slate-500">
                                {{ $event->category_label }}
                            </span>
                            
                            @if($hasVoted)
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Sudah memilih
                                </span>
                            @elseif($isActive)
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Belum memilih
                                </span>
                            @else
                                <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200">
                                    {{ $event->status_label }}
                                </span>
                            @endif
                        </div>

                        <!-- Event Title -->
                        <h4 class="text-xl font-bold text-slate-900 mb-2">
                            {{ $event->title }}
                        </h4>
                        
                        <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed mb-4">
                            {{ $event->description ?? 'Pemilihan resmi OSIS/MPK tingkat sekolah.' }}
                        </p>

                        <!-- Event Meta -->
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-center justify-between text-xs text-slate-600 mb-5">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-slate-400">Kode:</span>
                                <span class="font-mono font-semibold text-slate-800 bg-white px-2 py-0.5 rounded border border-slate-200">
                                    #{{ $event->room_code }}
                                </span>
                            </div>
                            <div>
                                <span class="font-medium text-slate-400">Kandidat:</span>
                                <span class="font-semibold text-slate-800">{{ $event->candidates_count }} Paslon</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div>
                        @if($hasVoted)
                            <a 
                                href="{{ route('voter.voted', $event->room_code) }}" 
                                class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 font-semibold text-xs flex items-center justify-center transition-colors shadow-xs"
                            >
                                <span>Lihat Bukti Pemilihan</span>
                            </a>
                        @elseif($isActive)
                            <a 
                                href="{{ route('voter.booth', $event->room_code) }}" 
                                class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs sm:text-sm flex items-center justify-center shadow-xs transition-colors cursor-pointer"
                            >
                                <span>Masuk Bilik Suara</span>
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
