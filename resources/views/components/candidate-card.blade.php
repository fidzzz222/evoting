@props([
    'candidate',
    'selectable' => false,
    'selected' => false,
    'showActions' => false,
])

<div 
    x-data="{ showDetails: false }"
    {{ $attributes->merge(['class' => 'bg-white rounded-2xl border transition-colors overflow-hidden shadow-xs flex flex-col justify-between ' . ($selected ? 'border-blue-600 ring-2 ring-blue-600/20' : 'border-slate-200 hover:border-slate-300')]) }}
>
    <!-- Card Header / Number Banner -->
    <div class="p-6 pb-4">
        <div class="flex items-center justify-between gap-4 mb-4">
            <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-slate-900 text-white font-bold text-lg">
                {{ sprintf('%02d', $candidate->candidate_number) }}
            </span>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">
                Kandidat {{ $candidate->candidate_number }}
            </span>
        </div>

        <!-- Candidate Photo -->
        <div class="relative w-full aspect-4/3 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 mb-5 group">
            @if($candidate->photo_url)
                <img 
                    src="{{ asset($candidate->photo_url) }}" 
                    alt="{{ $candidate->name }}" 
                    class="w-full h-full object-cover object-top transition-transform duration-300 group-hover:scale-102"
                    onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($candidate->name) }}&background=0f172a&color=fff&size=512';"
                />
            @else
                <div class="w-full h-full flex flex-col items-center justify-center bg-slate-100 text-slate-400">
                    <svg class="w-16 h-16 mb-2 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-xs font-semibold">Foto Paslon</span>
                </div>
            @endif
        </div>

        <!-- Candidate Name & Tagline -->
        <h4 class="text-xl font-bold text-slate-900 tracking-tight leading-snug">{{ $candidate->name }}</h4>
        @if($candidate->description)
            <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">{{ $candidate->description }}</p>
        @endif
    </div>

    <!-- Vision & Mission Preview / Drawer -->
    <div class="px-6 py-3 bg-slate-50 border-t border-slate-200">
        <button 
            type="button" 
            @click="showDetails = !showDetails" 
            class="w-full flex items-center justify-between text-xs font-bold text-slate-700 hover:text-slate-900 transition-colors py-1 cursor-pointer"
        >
            <span x-text="showDetails ? 'Tutup Visi & Misi' : 'Lihat Visi & Misi Lengkap'"></span>
            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': showDetails }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="showDetails" x-collapse x-cloak class="pt-3 pb-2 space-y-3 text-xs text-slate-600 border-t border-slate-200 mt-2">
            @if($candidate->vision)
                <div>
                    <span class="font-bold text-slate-800 block mb-1 uppercase tracking-wider text-[10px]">Visi:</span>
                    <p class="italic bg-white p-2.5 rounded-xl border border-slate-200 leading-relaxed">{{ $candidate->vision }}</p>
                </div>
            @endif
            @if($candidate->mission)
                <div>
                    <span class="font-bold text-slate-800 block mb-1 uppercase tracking-wider text-[10px]">Misi:</span>
                    <div class="bg-white p-2.5 rounded-xl border border-slate-200 leading-relaxed whitespace-pre-line">{{ $candidate->mission }}</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Actions Footer -->
    <div class="p-6 pt-4 bg-white border-t border-slate-200 flex items-center justify-between gap-3">
        @if($selectable)
            <button 
                type="button" 
                @click="$dispatch('open-modal', 'confirm-vote-{{ $candidate->id }}')"
                class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-sm sm:text-base flex items-center justify-center gap-2 shadow-xs transition-colors cursor-pointer"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                PILIH PASLON #{{ sprintf('%02d', $candidate->candidate_number) }}
            </button>
        @endif

        @if($showActions)
            <div class="w-full flex items-center justify-end gap-2">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
