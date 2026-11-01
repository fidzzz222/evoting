@props([
    'event',
    'count' => null,
])

@php
    $candidateCount = $count ?? ($event->relationLoaded('candidates') ? $event->candidates->count() : ($event->candidates_count ?? $event->candidates()->count()));
@endphp

@if($candidateCount < 2)
    <div {{ $attributes->merge(['class' => 'rounded-2xl border border-amber-200 bg-amber-50/90 p-5 text-xs text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs']) }}>
        <div class="flex items-start gap-3">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-200 text-amber-800 font-bold text-xs shrink-0 mt-0.5">!</span>
            <div>
                <h3 class="font-semibold text-sm text-amber-950">Langkah Berikutnya: Pendaftaran Pasangan Calon</h3>
                <p class="text-amber-800 mt-0.5 leading-relaxed">
                    Agenda ini baru memiliki <strong>{{ $candidateCount }} pasangan calon</strong>. Minimal <strong>2 paslon</strong> wajib terdaftar sebelum agenda pemilihan dapat dibuka untuk pemungutan suara.
                </p>
            </div>
        </div>
        <a 
            href="{{ route('events.candidates.create', $event) }}" 
            class="shrink-0 inline-flex items-center px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs shadow-xs transition-colors"
        >
            <span>Daftarkan Paslon #{{ $candidateCount + 1 }}</span>
        </a>
    </div>
@endif
