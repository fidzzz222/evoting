@extends('layouts.app', ['title' => Auth::user()->isSuperAdmin() ? 'Dashboard Utama Administrator' : 'Dashboard Utama Panitia'])

@section('content')
<div class="space-y-8">
    <!-- Welcome Header Banner - Clean Surface -->
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/90 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                    Selamat Datang, {{ $user->nama }}!
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1.5 max-w-2xl leading-relaxed">
                    @if($user->isSuperAdmin())
                        Anda memiliki otoritas penuh untuk memantau seluruh agenda pemilihan, mengelola master DPT sekolah, dan manajemen akun panitia.
                    @else
                        Akun Anda memiliki akses mandiri untuk menyelenggarakan dan mengelola agenda pemilihan yang Anda buat secara aman, independen, dan terintegrasi.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- KPI Metric Cards Grid - Focused on Election Lifecycle -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-stat-card 
            title="Total Agenda" 
            :value="$totalEvents" 
            subtitle="Agenda terdaftar"
            color="blue"
            icon='<svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
        />

        <x-stat-card 
            title="Bilik Suara Aktif" 
            :value="$activeEventsCount" 
            subtitle="Pemungutan suara terbuka"
            color="emerald"
            icon='<svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
        />

        <x-stat-card 
            title="Draft & Persiapan" 
            :value="$draftEventsCount" 
            subtitle="Menunggu aktivasi"
            color="amber"
            icon='<svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
        />

        <x-stat-card 
            title="Pemilihan Selesai" 
            :value="$closedEventsCount" 
            subtitle="Hasil telah direkap"
            color="slate"
            icon='<svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
    </div>

    <!-- Active & Managed Elections Grid -->
    <div>
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    {{ $user->isSuperAdmin() ? 'Semua Agenda Pemilihan Sekolah' : 'Agenda Pemilihan Anda' }}
                </h2>
                <p class="text-xs text-slate-500">
                    {{ $user->isSuperAdmin() ? 'Pantau dan kelola seluruh pemilihan yang sedang berlangsung di sekolah' : 'Kelola pasangan calon, pantau perolehan suara live, dan unduh berita acara resmi' }}
                </p>
            </div>
            <a href="{{ route('events.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors inline-flex items-center gap-1">
                <span>Lihat Semua Agenda</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($events as $event)
                @php
                    $targetDpt = $event->eligibleVotersCount();
                    $turnoutRate = $targetDpt > 0 ? min(100, round(($event->votes_count / $targetDpt) * 100, 1)) : 0;
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-xs hover:border-slate-300 transition-colors flex flex-col justify-between group">
                    <div>
                        <!-- Header: Eyebrow Category & Single Status Badge -->
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                {{ $event->category_label }}
                            </span>
                            
                            @if($event->status === 'PUBLISHED')
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Aktif
                                </span>
                            @elseif($event->status === 'DRAFT')
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Draft
                                </span>
                            @elseif($event->status === 'CLOSED')
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 bg-slate-50 px-2.5 py-0.5 rounded-full border border-slate-200">
                                    Ditutup
                                </span>
                            @else
                                <span class="inline-flex items-center text-xs font-semibold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200">
                                    {{ $event->status }}
                                </span>
                            @endif
                        </div>

                        <!-- Title -->
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors mb-2.5 line-clamp-2 leading-snug">
                            {{ $event->title }}
                        </h3>

                        <!-- Horizontal Metadata Byline (Target Scope, Room Code, Creator) -->
                        <div class="flex items-center gap-2 text-xs text-slate-500 mb-5 flex-wrap">
                            <span class="inline-flex items-center gap-1 font-medium text-slate-600">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ $event->target_scope_short_label }}</span>
                            </span>
                            <span class="text-slate-300">•</span>
                            <span class="font-mono text-[11px] font-semibold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/80">
                                #{{ $event->room_code }}
                            </span>
                            @if($user->isSuperAdmin() && $event->creator)
                                <span class="text-slate-300">•</span>
                                <span class="truncate max-w-[130px] text-slate-500" title="Diselenggarakan oleh: {{ $event->creator->nama }}">
                                    {{ $event->creator->nama }}
                                </span>
                            @endif
                        </div>

                        <!-- Turnout Stats Bar -->
                        <div class="space-y-1.5 mb-5 text-xs">
                            <div class="flex items-center justify-between text-slate-500 font-medium">
                                <span>Suara Masuk</span>
                                <span class="font-bold text-slate-800 font-mono">{{ $event->votes_count }} dari {{ $targetDpt }} Hak Pilih</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden border border-slate-200/50">
                                <div class="bg-blue-600 h-full rounded-full transition-all duration-500" style="width: {{ $turnoutRate }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span>{{ $event->candidates_count }} Paslon Terdaftar</span>
                                <span class="font-bold text-slate-600">{{ $turnoutRate }}% Partisipasi</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-4 border-t border-slate-100 grid grid-cols-2 gap-2">
                        <a 
                            href="{{ route('events.monitoring', $event) }}" 
                            class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 hover:text-slate-900 font-semibold text-xs flex items-center justify-center gap-1.5 transition-colors shadow-2xs"
                        >
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span>Monitoring</span>
                        </a>
                        <a 
                            href="{{ route('events.show', $event) }}" 
                            class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs flex items-center justify-center gap-1.5 transition-colors shadow-xs"
                        >
                            <span>Kelola &rarr;</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 px-6 bg-white rounded-3xl border border-dashed border-slate-300">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-500 border border-slate-200 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Belum Ada Agenda Pemilihan</h3>
                    <p class="text-xs text-slate-500 mt-1 mb-4 max-w-sm mx-auto">
                        Anda belum memiliki agenda pemilihan yang dibuat. Buat agenda baru untuk memulai pemungutan suara elektronik.
                    </p>
                    <a href="{{ route('events.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs border border-slate-800 transition-colors">
                        <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Buat Agenda Pemilihan Pertama</span>
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Agency-Grade Operational Workflow Guide (Panitia Onboarding & Operations) -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-xs">
        <div class="max-w-2xl mb-6">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-1.5">
                Alur Kerja Penyelenggara
            </span>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Panduan Operasional Pemilihan Mandiri</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Tiga tahapan ringkas menyelenggarakan pemungutan suara elektronik yang tertib, transparan, dan akuntabel.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Step 1 -->
            <div class="p-5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="w-7 h-7 rounded-lg bg-slate-900 text-white font-bold text-xs flex items-center justify-center">01</span>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tahap Persiapan</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Buat Agenda & Lingkup DPT</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Tentukan nama pemilihan, kategori, dan target hak pilih (Semua DPT, Khusus Siswa, Dewan Guru, atau Per Kelas). Kode bilik suara 6 digit dibuat secara otomatis.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="p-5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="w-7 h-7 rounded-lg bg-slate-900 text-white font-bold text-xs flex items-center justify-center">02</span>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Data Paslon</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Daftarkan Calon & Visi Misi</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Tambahkan pasangan calon minimal 2 paslon. Masukkan nomor urut, foto resmi, deskripsi, serta visi dan misi agar pemilih dapat mempertimbangkan pilihannya.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="p-5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="w-7 h-7 rounded-lg bg-slate-900 text-white font-bold text-xs flex items-center justify-center">03</span>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pelaksanaan</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Buka Bilik Suara & Rekapitulasi</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Ubah status agenda menjadi Aktif, bagikan Kode Bilik Suara kepada pemilih yang berhak, pantau hasil live chart, dan unduh Berita Acara PDF resmi berintegritas digital.
                </p>
            </div>
        </div>

        <div class="mt-6 pt-5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Dilindungi proteksi suara ganda (anti-double voting) dan verifikasi identitas terenkripsi.</span>
            </div>
            @if($user->isSuperAdmin())
                <a href="{{ route('admin.logs.index') }}" class="font-semibold text-slate-600 hover:text-blue-600 transition-colors">
                    Lihat Audit Trail Lengkap &rarr;
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
