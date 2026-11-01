@extends('layouts.voter', ['title' => 'Hak Suara Telah Digunakan'])

@section('content')
<div class="max-w-lg mx-auto px-4 py-8 sm:py-12 text-center">
    <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-10 shadow-xs relative overflow-hidden">
        <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mx-auto mb-5">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-medium mb-3">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
            Status hak pilih
        </span>

        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mb-2">
            Anda Sudah Memberikan Suara
        </h2>

        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed mb-6">
            Akun DPT Anda telah tercatat menggunakan hak suara pada pemilihan 
            <strong class="text-slate-800 font-semibold">{{ $event->title }}</strong>.
        </p>

        <!-- Details Card -->
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-left space-y-2.5 mb-6 text-xs shadow-2xs">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
                <span class="text-slate-500">Agenda:</span>
                <span class="font-semibold text-slate-900 text-right">{{ $event->title }}</span>
            </div>
            <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
                <span class="text-slate-500">Pemilih:</span>
                <span class="font-semibold text-slate-900">{{ $voter->nama }}</span>
            </div>
            <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
                <span class="text-slate-500">Nomor DPT:</span>
                <span class="font-mono font-semibold text-slate-900">{{ $voter->identifier }}</span>
            </div>
            @if($participation)
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Waktu Memilih:</span>
                    <span class="font-medium text-emerald-700 font-mono">
                        {{ \Carbon\Carbon::parse($participation->voted_at)->translatedFormat('d F Y, H:i:s') }} WIB
                    </span>
                </div>
            @endif
        </div>

        <div>
            <form action="{{ route('voter.logout') }}" method="POST">
                @csrf
                <button 
                    type="submit" 
                    class="w-full sm:w-auto min-w-[220px] px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs sm:text-sm shadow-xs transition-colors cursor-pointer inline-flex items-center justify-center"
                >
                    Selesai & Keluar
                </button>
            </form>
            <p class="text-[11px] text-slate-400 mt-2.5">
                Setiap pemilih hanya dapat memberikan satu kali suara per agenda.
            </p>
        </div>
    </div>
</div>
@endsection
