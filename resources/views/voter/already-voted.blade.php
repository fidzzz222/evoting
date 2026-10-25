@extends('layouts.voter', ['title' => 'Hak Suara Telah Digunakan'])

@section('content')
<div class="max-w-xl mx-auto px-4 py-8 sm:py-14 text-center">
    <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-12 shadow-sm relative overflow-hidden">
        <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold uppercase tracking-wider mb-3">
            Pemberitahuan Sistem
        </span>

        <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-2">
            Anda Sudah Memberikan Suara
        </h2>

        <p class="text-sm text-slate-600 leading-relaxed mb-6">
            Sistem mencatat bahwa akun DPT Anda telah menggunakan hak pilih pada pemilihan 
            <strong class="text-slate-800">{{ $event->title }}</strong>.
        </p>

        <!-- Details Card -->
        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/90 text-left space-y-2.5 mb-8 text-xs shadow-xs">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                <span class="text-slate-500">Agenda:</span>
                <span class="font-bold text-slate-800">{{ $event->title }}</span>
            </div>
            <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                <span class="text-slate-500">Nama Pemilih:</span>
                <span class="font-bold text-slate-800">{{ $voter->nama }}</span>
            </div>
            <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                <span class="text-slate-500">Nomor DPT:</span>
                <span class="font-mono font-bold text-slate-800">{{ $voter->identifier }}</span>
            </div>
            @if($participation)
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Waktu Memilih:</span>
                    <span class="font-bold text-emerald-700 font-mono">
                        {{ \Carbon\Carbon::parse($participation->voted_at)->translatedFormat('d F Y, H:i:s') }} WIB
                    </span>
                </div>
            @endif
        </div>

        <div class="pt-2">
            <form action="{{ route('voter.logout') }}" method="POST">
                @csrf
                <button 
                    type="submit" 
                    class="w-full sm:w-auto min-w-[260px] px-8 py-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-bold text-sm border border-slate-800 shadow-xs transition-colors cursor-pointer inline-flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Selesai & Keluar Sesi</span>
                </button>
            </form>
            <p class="text-[11px] text-slate-400 mt-2.5">
                Setiap pemilih hanya dapat memberikan satu kali hak suara per agenda pemilihan.
            </p>
        </div>
    </div>
</div>
@endsection
