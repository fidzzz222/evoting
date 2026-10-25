@extends('layouts.app', ['title' => 'Detail Agenda: ' . $event->title])

@section('content')
<div class="space-y-8" x-data="{
    copy(text, msg) {
        navigator.clipboard.writeText(text);
        alert(msg || 'Disalin ke papan klip!');
    }
}">
    <!-- Top Action / Breadcrumbs Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('events.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Agenda</span>
        </a>

        <div class="flex flex-wrap items-center gap-2.5">
            <a 
                href="{{ route('events.monitoring', $event) }}" 
                class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs flex items-center gap-2 shadow-xs border border-slate-800 transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Live Quick Count</span>
            </a>

            <a 
                href="{{ route('events.report.pdf', $event) }}" 
                target="_blank"
                class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-300 flex items-center gap-2 shadow-xs transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Berita Acara (PDF)</span>
            </a>

            <a 
                href="{{ route('events.edit', $event) }}" 
                class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-300 shadow-xs transition-colors"
            >
                Edit Agenda
            </a>
        </div>
    </div>

    <!-- Event Header Hero Card -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-7 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-3 mb-2 flex-wrap">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        {{ $event->category_label }}
                    </span>

                    @if($event->status === 'PUBLISHED')
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Pemilihan Aktif
                        </span>
                    @elseif($event->status === 'CLOSED')
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 bg-slate-50 px-2.5 py-0.5 rounded-full border border-slate-200">
                            Pemilihan Ditutup
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            Draft / Persiapan
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ $event->title }}
                </h1>

                <div class="flex items-center gap-2 text-xs text-slate-500 mt-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 font-medium text-slate-600">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Target DPT: {{ $event->target_scope_label }}
                    </span>
                    <span class="text-slate-300">•</span>
                    <span class="font-mono text-[11px] font-semibold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/80">
                        #{{ $event->room_code }}
                    </span>
                    @if($event->creator)
                        <span class="text-slate-300">•</span>
                        <span class="text-slate-500">
                            Diselenggarakan oleh {{ $event->creator->nama }}
                        </span>
                    @endif
                </div>

                @if($event->description)
                    <p class="text-xs sm:text-sm text-slate-500 mt-2 max-w-2xl leading-relaxed font-medium">
                        {{ $event->description }}
                    </p>
                @endif
            </div>

            <!-- Fast Status Control Panel -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80 shrink-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-2">
                    Kontrol Status Bilik Suara:
                </span>
                <form action="{{ route('events.status.update', $event) }}" method="POST" class="flex flex-wrap items-center gap-2">
                    @csrf
                    @method('PATCH')
                    
                    @if($event->status !== 'PUBLISHED')
                        <button 
                            type="submit" 
                            name="status" 
                            value="PUBLISHED"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold text-xs shadow-xs transition-colors cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Buka Bilik Suara</span>
                        </button>
                    @endif

                    @if($event->status === 'PUBLISHED')
                        <button 
                            type="submit" 
                            name="status" 
                            value="DRAFT"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs shadow-xs transition-colors cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Jeda ke Draft</span>
                        </button>
                    @endif

                    @if($event->status !== 'CLOSED')
                        <button 
                            type="submit" 
                            name="status" 
                            value="CLOSED"
                            onclick="return confirm('Apakah Anda yakin ingin menutup pemilihan? Bilik suara pemilih tidak akan dapat diakses lagi.')"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition-colors cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                            </svg>
                            <span>Tutup Pemilihan</span>
                        </button>
                    @else
                        <button 
                            type="submit" 
                            name="status" 
                            value="DRAFT"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition-colors cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Buka Kembali</span>
                        </button>
                    @endif
                </form>
            </div>
        </div>

        <!-- 3 Quick Metrics Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6">
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80">
                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Total Paslon</span>
                <div class="text-2xl font-black text-slate-900 font-mono mt-1">
                    {{ $event->candidates->count() }}
                    <span class="text-sm font-sans font-semibold text-slate-500">Kandidat</span>
                </div>
                <a href="{{ route('events.candidates.index', $event) }}" class="text-xs font-semibold text-slate-700 hover:text-slate-900 hover:underline mt-1.5 inline-block">
                    Kelola Paslon &rarr;
                </a>
            </div>

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80">
                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Suara Masuk</span>
                <div class="text-2xl font-black text-slate-900 font-mono mt-1">
                    {{ $totalVotes }}
                    <span class="text-sm font-sans font-semibold text-slate-500">Suara</span>
                </div>
                <span class="text-xs text-slate-500 mt-1.5 inline-block font-medium">Dari {{ $totalDPT }} DPT berhak ({{ $event->target_scope_short_label }})</span>
            </div>

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80">
                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Turnout Partisipasi</span>
                <div class="text-2xl font-black text-slate-900 font-mono mt-1">
                    {{ $turnoutRate }}%
                </div>
                <div class="w-full bg-slate-200 h-1.5 rounded-full mt-2.5 overflow-hidden">
                    <div class="bg-emerald-500 h-full rounded-full transition-all duration-300" style="width: {{ min(100, $turnoutRate) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Room Code & Collaborator Link Sharing Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Room Code for Voters -->
        <x-card title="Kode Bilik Pemilih" subtitle="Bagikan kode ini kepada siswa/guru untuk akses bilik suara">
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/90 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-0.5">Kode Ruang Bilik:</span>
                    <span class="font-mono text-2xl font-black text-slate-900 tracking-widest">
                        #{{ $event->room_code }}
                    </span>
                </div>
                <button 
                    type="button" 
                    @click="copy('{{ $event->room_code }}', 'Kode bilik #{{ $event->room_code }} disalin!')"
                    class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-xs border border-slate-800 transition-colors cursor-pointer"
                >
                    Salin Kode
                </button>
            </div>
            <p class="text-xs text-slate-500 mt-3">
                Pemilih dapat langsung memasukkan kode ini di halaman utama portal e-voting atau saat login.
            </p>
        </x-card>

        <!-- Officer Collaborator Invite Link -->
        <x-card title="Undang Panitia Kolaborator" subtitle="Bagikan tautan ini ke rekan panitia untuk mengelola agenda ini bersama">
            @php
                $inviteUrl = route('events.collaborator.join', ['event' => $event, 'token' => $event->invite_token]);
            @endphp
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between gap-3">
                <input 
                    type="text" 
                    readonly 
                    value="{{ $inviteUrl }}" 
                    class="bg-transparent text-xs text-slate-600 font-mono w-full truncate focus:outline-none"
                />
                <button 
                    type="button" 
                    @click="copy('{{ $inviteUrl }}', 'Tautan kolaborator disalin!')"
                    class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors shrink-0 cursor-pointer"
                >
                    Salin Link
                </button>
            </div>

            <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                <span>Panitia Terdaftar: <strong>{{ $event->officers->count() + 1 }} Orang</strong></span>
                <form action="{{ route('events.invite.regenerate', $event) }}" method="POST">
                    @csrf
                    <button type="submit" class="text-slate-600 hover:text-slate-900 font-semibold underline underline-offset-2 cursor-pointer">
                        Perbarui Token
                    </button>
                </form>
            </div>
        </x-card>
    </div>

    <!-- Candidate List Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-900 tracking-tight">Daftar Pasangan Calon (Kandidat)</h3>
                <p class="text-xs text-slate-500">Pasangan calon yang terdaftar pada agenda pemilihan ini</p>
            </div>

            <a 
                href="{{ route('events.candidates.create', $event) }}" 
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-xs border border-slate-800 transition-colors"
            >
                <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Paslon</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($event->candidates as $candidate)
                <x-candidate-card :candidate="$candidate" :showActions="true">
                    <div class="w-full flex items-center justify-between pt-2">
                        <div class="text-xs">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Perolehan</span>
                            <span class="text-base font-black text-slate-900 font-mono">{{ $candidate->votes_count }}</span>
                            <span class="text-xs font-semibold text-slate-500">Suara</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a 
                                href="{{ route('events.candidates.edit', [$event, $candidate]) }}" 
                                class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors"
                            >
                                Edit
                            </a>
                            @if($event->votes()->count() === 0)
                                <form action="{{ route('events.candidates.destroy', [$event, $candidate]) }}" method="POST" onsubmit="return confirm('Hapus paslon ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs transition-colors cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </x-candidate-card>
            @empty
                <div class="col-span-3 text-center py-12 bg-white rounded-2xl border border-dashed border-slate-300">
                    <p class="text-sm font-semibold text-slate-500 mb-3">Belum ada pasangan calon yang ditambahkan.</p>
                    <a href="{{ route('events.candidates.create', $event) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-xs border border-slate-800 transition-colors">
                        <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tambah Paslon Sekarang</span>
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Danger Zone / Delete -->
    <x-card title="Zona Pengaturan Berisiko" subtitle="Tindakan penghapusan permanen agenda">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h4 class="text-sm font-bold text-slate-800">Hapus Agenda Pemilihan Ini</h4>
                <p class="text-xs text-slate-500">Seluruh data paslon dan perolehan suara pada agenda ini akan dihapus permanen.</p>
            </div>
            <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('YAKIN INGIN MENGHAPUS AGENDA INI? Tindakan ini tidak dapat dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-colors cursor-pointer shrink-0">
                    Hapus Agenda Permanen
                </button>
            </form>
        </div>
    </x-card>
</div>
@endsection
