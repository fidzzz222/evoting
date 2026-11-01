@extends('layouts.app', ['title' => 'Kelola Paslon - ' . $event->title])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('events.show', $event) }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors mb-2">
                Detail Agenda
            </a>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Kelola Pasangan Calon
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Agenda: <strong>{{ $event->title }}</strong> &bull; {{ $candidates->count() }} Paslon
            </p>
        </div>

        <a 
            href="{{ route('events.candidates.create', $event) }}" 
            class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs shadow-xs border border-slate-900 transition-colors"
        >
            <span>Tambah Paslon</span>
        </a>
    </div>

    <x-insufficient-candidates-alert :event="$event" :count="$candidates->count()" />

    <!-- Grid Paslon -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($candidates as $candidate)
            <x-candidate-card :candidate="$candidate" :showActions="true">
                <div class="w-full flex items-center justify-between pt-2 border-t border-slate-100">
                    <div class="text-xs">
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Perolehan</span>
                        <span class="text-base font-bold tabular-nums text-slate-900 font-mono">{{ $candidate->votes_count }} Suara</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <a 
                            href="{{ route('events.candidates.edit', [$event, $candidate]) }}" 
                            class="px-3 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-semibold text-xs transition-colors shadow-xs"
                        >
                            Edit
                        </a>
                        @if($candidate->votes_count === 0)
                            <form action="{{ route('events.candidates.destroy', [$event, $candidate]) }}" method="POST" onsubmit="return confirm('Hapus kandidat ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 font-semibold text-xs transition-colors cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </x-candidate-card>
        @empty
            <div class="col-span-3 text-center py-16 bg-white rounded-2xl border border-dashed border-slate-300">
                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800 mb-1">Belum Ada Pasangan Calon</h3>
                <p class="text-xs text-slate-500 mb-4 max-w-sm mx-auto">
                    Daftarkan pasangan calon untuk agenda pemilihan ini.
                </p>
                <a 
                    href="{{ route('events.candidates.create', $event) }}" 
                    class="inline-flex items-center px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs shadow-xs border border-slate-900 transition-colors"
                >
                    <span>Tambah Paslon</span>
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
