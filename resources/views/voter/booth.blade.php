@extends('layouts.voter', ['title' => 'Bilik Suara: ' . $event->title])

@section('content')
<div class="space-y-8" x-data="{ submitting: false }">
    <!-- Header Bilik Suara -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xs font-medium text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200">
                    {{ $event->category_label }}
                </span>
                <span class="text-slate-300">•</span>
                <span class="text-xs font-mono font-medium text-slate-500">
                    #{{ $event->room_code }}
                </span>
                <span class="text-slate-300">•</span>
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Sesi aktif
                </span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                {{ $event->title }}
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
                Silakan cermati profil serta visi dan misi pasangan calon. Klik tombol 
                <strong class="text-slate-800 font-semibold">Pilih Paslon</strong> untuk menetapkan suara Anda.
            </p>
        </div>

        <div class="shrink-0 bg-slate-50 border border-slate-200 rounded-xl p-3.5 max-w-xs text-xs text-slate-600 flex items-start gap-2.5">
            <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="leading-relaxed">
                <span class="font-semibold text-slate-800 block">Ketentuan Pemilihan</span>
                Setiap pemilih memiliki 1 hak suara. Pilihan bersifat final setelah dikonfirmasi.
            </div>
        </div>
    </div>

    <!-- Candidate Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($candidates as $candidate)
            <x-candidate-card 
                :candidate="$candidate" 
                :selectable="true" 
            />

            <!-- Modal Konfirmasi Pilihan -->
            <x-modal name="confirm-vote-{{ $candidate->id }}" title="Konfirmasi Pilihan" maxWidth="md">
                <div class="text-center py-2">
                    <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center mx-auto mb-3 font-bold text-lg">
                        {{ sprintf('%02d', $candidate->candidate_number) }}
                    </div>

                    <h4 class="text-base font-bold text-slate-900 mb-1">
                        Konfirmasi Pilihan
                    </h4>
                    <p class="text-xs text-slate-500 mb-4">
                        Pastikan pilihan Anda sudah sesuai sebelum melanjutkan:
                    </p>

                    <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200 text-left mb-4">
                        <div class="text-[11px] font-medium text-slate-500 mb-0.5">Pasangan Calon {{ sprintf('%02d', $candidate->candidate_number) }}</div>
                        <div class="text-sm font-semibold text-slate-900">{{ $candidate->name }}</div>
                        @if($candidate->description)
                            <div class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $candidate->description }}</div>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500 bg-slate-100/70 p-2.5 rounded-lg border border-slate-200/80 mb-2">
                        Pilihan Anda akan disimpan secara langsung dan tidak dapat diubah kembali.
                    </p>
                </div>

                <x-slot name="footer">
                    <button 
                        type="button" 
                        @click="$dispatch('close-modal', 'confirm-vote-{{ $candidate->id }}')"
                        class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 transition-colors cursor-pointer"
                        :disabled="submitting"
                    >
                        Batal
                    </button>

                    <form 
                        action="{{ route('voter.vote.submit', $event->room_code) }}" 
                        method="POST" 
                        @submit="submitting = true"
                    >
                        @csrf
                        <input type="hidden" name="candidate_id" value="{{ $candidate->id }}" />
                        <button 
                            type="submit" 
                            class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white text-xs font-semibold shadow-xs flex items-center gap-2 cursor-pointer transition-colors"
                            :disabled="submitting"
                        >
                            <span x-show="!submitting">Konfirmasi Pilihan</span>
                            <span x-show="submitting" x-cloak class="flex items-center gap-1.5">
                                <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Menyimpan...
                            </span>
                        </button>
                    </form>
                </x-slot>
            </x-modal>
        @endforeach
    </div>

    <!-- Footer Booth Session info & Cancellation -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-slate-200/80 text-xs text-slate-500">
        <div class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
            <span>Pemilih: <span class="font-medium text-slate-700">{{ Auth::guard('voter')->user()->nama }}</span> ({{ Auth::guard('voter')->user()->identifier }})</span>
        </div>
        <form action="{{ route('voter.logout') }}" method="POST">
            @csrf
            <button type="submit" class="font-medium text-slate-500 hover:text-slate-800 transition-colors cursor-pointer">
                Keluar tanpa memilih
            </button>
        </form>
    </div>
</div>
@endsection
