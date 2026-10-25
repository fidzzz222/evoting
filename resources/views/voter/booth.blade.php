@extends('layouts.voter', ['title' => 'Bilik Suara: ' . $event->title])

@section('content')
<div class="space-y-8" x-data="{ submitting: false }">
    <!-- Header Bilik Suara -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    {{ $event->category_label }}
                </span>
                <span class="text-slate-300">•</span>
                <span class="text-xs font-mono font-bold text-slate-600">
                    #{{ $event->room_code }}
                </span>
                <span class="text-slate-300">•</span>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Sesi Aktif
                </span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                {{ $event->title }}
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
                Silakan cermati profil, visi, dan misi setiap pasangan calon di bawah ini. Klik tombol 
                <strong class="text-slate-800">Pilih Paslon</strong> untuk menetapkan suara Anda.
            </p>
        </div>

        <div class="shrink-0 bg-slate-50 border border-slate-200 rounded-2xl p-4 max-w-xs text-xs text-slate-700 flex items-start gap-3 shadow-2xs">
            <svg class="w-5 h-5 text-slate-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <span class="font-bold text-slate-900 block">Peringatan Integritas:</span>
                Hak suara hanya berlaku 1 kali. Pilihan tidak dapat diubah setelah Anda konfirmasi.
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
            <x-modal name="confirm-vote-{{ $candidate->id }}" title="Konfirmasi Surat Suara" maxWidth="md">
                <div class="text-center py-2">
                    <div class="w-14 h-14 rounded-xl bg-slate-900 border border-slate-800 text-white flex items-center justify-center mx-auto mb-4 font-bold text-xl">
                        #{{ sprintf('%02d', $candidate->candidate_number) }}
                    </div>

                    <h4 class="text-lg font-bold text-slate-900 mb-1">
                        Konfirmasi Pilihan Calon
                    </h4>
                    <p class="text-xs text-slate-500 mb-4">
                        Apakah Anda yakin ingin memberikan suara sah Anda kepada:
                    </p>

                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-left mb-5">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5">Pasangan Calon #{{ sprintf('%02d', $candidate->candidate_number) }}</div>
                        <div class="text-base font-bold text-slate-900">{{ $candidate->name }}</div>
                        @if($candidate->description)
                            <div class="text-xs text-slate-500 mt-1">{{ $candidate->description }}</div>
                        @endif
                    </div>

                    <p class="text-xs font-medium text-rose-700 bg-rose-50 p-3 rounded-xl border border-rose-200 mb-4">
                        Perhatian: Suara ini bersifat final, rahasia, dan langsung dimasukkan ke kotak suara digital.
                    </p>
                </div>

                <x-slot name="footer">
                    <button 
                        type="button" 
                        @click="$dispatch('close-modal', 'confirm-vote-{{ $candidate->id }}')"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                        :disabled="submitting"
                    >
                        Batal, Kembali
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
                            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold shadow-xs flex items-center gap-2 cursor-pointer transition-colors"
                            :disabled="submitting"
                        >
                            <span x-show="!submitting">Ya, Berikan Suara Sah</span>
                            <span x-show="submitting" x-cloak class="flex items-center gap-1.5">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Menyimpan Suara...
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
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Bilik Suara Terenkripsi &bull; DPT: <strong class="text-slate-700">{{ Auth::guard('voter')->user()->nama }}</strong> ({{ Auth::guard('voter')->user()->identifier }})</span>
        </div>
        <form action="{{ route('voter.logout') }}" method="POST">
            @csrf
            <button type="submit" class="font-semibold text-rose-600 hover:text-rose-700 hover:underline flex items-center gap-1 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Batalkan & Keluar Bilik Suara
            </button>
        </form>
    </div>
</div>
@endsection
