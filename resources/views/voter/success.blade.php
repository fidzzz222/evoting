@extends('layouts.voter', ['title' => 'Suara Berhasil Disimpan'])

@section('content')
<div class="max-w-xl mx-auto px-4 py-8 sm:py-14 text-center">
    <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-12 shadow-sm relative overflow-hidden">
        <!-- Success Decorative Background -->
        <div class="w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold uppercase tracking-wider mb-3">
            Hak Suara Sah Tersimpan
        </span>

        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mb-3">
            Terima Kasih Atas Partisipasi Anda!
        </h2>

        <p class="text-sm text-slate-600 leading-relaxed mb-6">
            Suara Anda pada agenda pemilihan 
            <strong class="text-slate-800">{{ $event->title }}</strong> 
            telah berhasil dicatat secara resmi ke dalam sistem e-voting.
        </p>

        <!-- Digital Receipt Card -->
        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/90 text-left space-y-3 mb-8 shadow-xs">
            <div class="flex items-center justify-between text-xs pb-2 border-b border-slate-200">
                <span class="text-slate-500 font-medium">Agenda Pemilihan:</span>
                <span class="font-bold text-slate-900">{{ $event->title }}</span>
            </div>
            <div class="flex items-center justify-between text-xs pb-2 border-b border-slate-200">
                <span class="text-slate-500 font-medium">Nama Pemilih:</span>
                <span class="font-bold text-slate-900">{{ $voter->nama }}</span>
            </div>
            <div class="flex items-center justify-between text-xs pb-2 border-b border-slate-200">
                <span class="text-slate-500 font-medium">Nomor DPT:</span>
                <span class="font-mono font-bold text-slate-900">{{ $voter->identifier }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Waktu Presensi Suara:</span>
                <span class="font-bold text-emerald-700 font-mono">{{ now()->translatedFormat('d F Y, H:i:s') }} WIB</span>
            </div>
        </div>

        <!-- Privacy Assurance Note -->
        <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-4 text-xs text-blue-900 text-left flex items-start gap-3 mb-8">
            <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <div>
                <span class="font-bold block mb-0.5">Asas Kerahasiaan Terjamin:</span>
                Identitas Anda tercatat sebagai pemilih yang telah berpartisipasi, namun 
                <em>pilihan kandidat Anda telah dipisahkan secara anonim</em> sehingga tidak ada yang dapat melacak pilihan Anda.
            </div>
        </div>

        <!-- Action Button for Kiosk Bilik Suara -->
        <div class="pt-2">
            <form action="{{ route('voter.logout') }}" method="POST">
                @csrf
                <button 
                    type="submit" 
                    class="w-full sm:w-auto min-w-[280px] px-8 py-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-bold text-sm border border-slate-800 shadow-xs transition-colors cursor-pointer inline-flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Selesai & Keluar Bilik Suara</span>
                </button>
            </form>
            <p class="text-[11px] text-slate-400 mt-2.5">
                Klik tombol di atas untuk mengakhiri sesi dan menyiapkan bilik untuk pemilih berikutnya.
            </p>
        </div>
    </div>
</div>
@endsection
