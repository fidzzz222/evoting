@extends('layouts.voter', ['title' => 'Riwayat &amp; Log Pemilihan'])

@section('content')
<div class="space-y-8">
    <!-- Header / Navigation Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                <a href="{{ route('voter.index') }}" class="hover:text-slate-800 transition-colors inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Portal Bilik Suara</span>
                </a>
                <span>&bull;</span>
                <span class="text-slate-800 font-semibold">Riwayat &amp; Log</span>
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Riwayat &amp; Log Pemilihan</h2>
            <p class="text-xs text-slate-500 mt-0.5">Catatan rekapitulasi partisipasi suara dan jejak audit keamanan sesi pemilih.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('voter.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 hover:border-slate-300 shadow-2xs transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Bilik Suara</span>
            </a>
            <form action="{{ route('voter.logout') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-xs transition-colors cursor-pointer">
                    Keluar Sesi
                </button>
            </form>
        </div>
    </div>

    <!-- Voter Profile Card -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white font-bold flex items-center justify-center text-lg shrink-0 shadow-xs">
                {{ substr($voter->nama, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-bold text-slate-900">{{ $voter->nama }}</h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $voter->type_label ?? $voter->type }}
                    </span>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500 mt-1">
                    <span>ID / NIS: <strong class="font-mono text-slate-700">{{ $voter->identifier }}</strong></span>
                    @if($voter->kelas)
                        <span>&bull;</span>
                        <span>Kelas: <strong class="text-slate-700">{{ $voter->kelas }}</strong></span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Pemilih Terdaftar Aktif
            </span>
        </div>
    </div>

    <!-- Summary Metrics Card -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total Votes Cast -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500">Total Suara Disalurkan</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold tracking-tight text-slate-900">{{ $totalVotes }}</span>
                    <span class="text-xs font-medium text-slate-500">Agenda</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Last Voted At -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500">Terakhir Kali Memilih</p>
                <div class="mt-1">
                    @if($lastVotedAt)
                        <span class="text-sm font-bold text-slate-900 block font-mono">
                            {{ \Carbon\Carbon::parse($lastVotedAt)->translatedFormat('d M Y, H:i') }} WIB
                        </span>
                        <span class="text-[11px] text-slate-400">
                            {{ \Carbon\Carbon::parse($lastVotedAt)->diffForHumans() }}
                        </span>
                    @else
                        <span class="text-sm font-semibold text-slate-400 block">Belum pernah</span>
                        <span class="text-[11px] text-slate-400">Belum menyalurkan suara</span>
                    @endif
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-200 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Eligible Ongoing Events -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500">Agenda Berlangsung Lainnya</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold tracking-tight text-slate-900">{{ $eligibleOngoingEvents->count() }}</span>
                    <span class="text-xs font-medium text-slate-500">Agenda Aktif</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Asas LUBER Banner -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/80 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5 shadow-2xs">
        <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
        </div>
        <div>
            <h4 class="text-sm font-bold text-slate-900">Jaminan Kerahasiaan Suara (Asas LUBER)</h4>
            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                Sesuai prinsip Langsung, Umum, Bebas, Rahasia, Jujur, dan Adil, pilihan kandidat Anda <strong>tidak disimpan</strong> pada profil maupun log akun ini. Sistem hanya mencatat bahwa hak suara Anda telah sah digunakan demi menjamin integritas rekapitulasi tanpa mengungkap preferensi rahasia Anda.
            </p>
        </div>
    </div>

    <!-- Section 1: Riwayat Agenda Pemilihan -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 tracking-tight">Riwayat Partisipasi Pemilihan</h3>
                <p class="text-xs text-slate-500">Daftar agenda pemilihan yang telah Anda ikuti dan gunakan hak pilihnya</p>
            </div>
            <span class="text-xs font-medium text-slate-600 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">
                {{ $participations->count() }} Tercatat
            </span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($participations as $participation)
                <div class="p-6 hover:bg-slate-50/70 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Suara Sah Terverifikasi
                            </span>
                            <span class="text-xs font-mono font-medium text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                Bilik #{{ $participation->event->room_code }}
                            </span>
                        </div>
                        <h4 class="text-base font-bold text-slate-900">
                            {{ $participation->event->title }}
                        </h4>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ \Carbon\Carbon::parse($participation->voted_at)->translatedFormat('l, d F Y, H:i:s') }} WIB
                            </span>
                            <span class="text-slate-300">&bull;</span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                {{ $participation->event->candidates_count }} Pasangan Calon
                            </span>
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        <a href="{{ route('voter.voted', $participation->event->room_code) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-colors shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Lihat Bukti Suara</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-semibold text-slate-800">Belum Ada Riwayat Pemilihan</h4>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Anda belum pernah memberikan suara pada agenda pemilihan manapun. Silakan buka bilik suara untuk mulai memilih.
                    </p>
                    <a href="{{ route('voter.index') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-xs transition-colors">
                        Pilih Agenda Sekarang
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Section 2: Log Jejak Audit Keamanan Pemilih -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 tracking-tight">Log Jejak Audit Keamanan Akun</h3>
                <p class="text-xs text-slate-500">Catatan aktivitas login, transaksi suara, dan keamanan yang terverifikasi sistem</p>
            </div>
            <span class="text-xs font-medium text-slate-600 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">
                {{ $auditLogs->count() }} Aktivitas
            </span>
        </div>

        @if($auditLogs->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                            <th class="px-6 py-3.5">Waktu Kejadian</th>
                            <th class="px-6 py-3.5">Aktivitas</th>
                            <th class="px-6 py-3.5">Keterangan</th>
                            <th class="px-6 py-3.5">Alamat IP</th>
                            <th class="px-6 py-3.5">Perangkat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($auditLogs as $log)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-3.5 font-mono text-slate-500 whitespace-nowrap">
                                    {{ $log->created_at->translatedFormat('d M Y, H:i:s') }} WIB
                                </td>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    @if($log->action === 'SUARA_DISALURKAN')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Suara Disalurkan
                                        </span>
                                    @elseif($log->action === 'LOGIN_PEMILIH')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Masuk Bilik
                                        </span>
                                    @elseif($log->action === 'LOGOUT_PEMILIH')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Keluar Sesi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $log->action }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 font-medium text-slate-900">
                                    {{ $log->description }}
                                </td>
                                <td class="px-6 py-3.5 font-mono text-slate-500 whitespace-nowrap">
                                    {{ $log->ip_address ?? '-' }}
                                </td>
                                <td class="px-6 py-3.5 text-slate-500 max-w-xs truncate" title="{{ $log->user_agent }}">
                                    {{ Str::limit($log->user_agent ?? '-', 45) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center text-slate-500 text-xs">
                Belum ada catatan log aktivitas untuk akun pemilih ini.
            </div>
        @endif
    </div>
</div>
@endsection
