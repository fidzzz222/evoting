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
            <span>Daftar Agenda</span>
        </a>

        <div class="flex flex-wrap items-center gap-2.5">
            <a
                href="{{ route('events.edit', $event) }}"
                class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-300 shadow-xs transition-colors"
            >
                Edit Agenda
            </a>
        </div>
    </div>

    @if($event->candidates->count() < 2)
        <div class="rounded-2xl border border-amber-200 bg-amber-50/90 p-5 text-xs text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
            <div class="flex items-start gap-3">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-200 text-amber-800 font-bold text-xs shrink-0 mt-0.5">!</span>
                <div>
                    <h3 class="font-semibold text-sm text-amber-950">Langkah Berikutnya: Pendaftaran Pasangan Calon</h3>
                    <p class="text-amber-800 mt-0.5 leading-relaxed">
                        Agenda ini baru memiliki <strong>{{ $event->candidates->count() }} pasangan calon</strong>. Minimal <strong>2 paslon</strong> wajib terdaftar sebelum agenda pemilihan dapat dibuka untuk pemungutan suara.
                    </p>
                </div>
            </div>
            <a 
                href="{{ route('events.candidates.create', $event) }}" 
                class="shrink-0 inline-flex items-center px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs shadow-xs transition-colors"
            >
                <span>Daftarkan Paslon #{{ $event->candidates->count() + 1 }}</span>
            </a>
        </div>
    @endif

    <!-- Event Header Hero Card -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-7 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-3 mb-2 flex-wrap">
                    <span class="text-xs font-medium text-slate-500">
                        {{ $event->category_label }}
                    </span>

                    @if($event->status === 'PUBLISHED' && !$event->isAfterEndTime())
                        @if($event->isBeforeStartTime())
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Menunggu Jadwal
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Aktif
                            </span>
                        @endif
                    @elseif($event->status === 'CLOSED' || $event->isAfterEndTime())
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 bg-slate-50 px-2.5 py-0.5 rounded-full border border-slate-200/80">
                            Ditutup
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200/80">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Draft
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-semibold text-slate-900 tracking-tight">
                    {{ $event->title }}
                </h1>

                <div class="flex items-center gap-2 text-xs text-slate-500 mt-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 font-medium text-slate-600">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Target: {{ $event->target_scope_label }}
                    </span>
                    <span class="text-slate-300">•</span>
                    <span class="font-mono text-xs font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                        #{{ $event->room_code }}
                    </span>
                    @if($event->creator)
                        <span class="text-slate-300">•</span>
                        <span class="text-slate-500">
                            Dibuat oleh {{ $event->creator->nama }}
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
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 block mb-2">
                    Status Bilik Suara:
                </span>
                <form action="{{ route('events.status.update', $event) }}" method="POST" class="flex flex-wrap items-center gap-2">
                    @csrf
                    @method('PATCH')

                    @if($event->status !== 'PUBLISHED')
                        @if($event->isAfterEndTime())
                            <span class="text-[11px] font-medium text-rose-600 bg-rose-50 px-2.5 py-1.5 rounded-lg border border-rose-200">
                                Jadwal telah berakhir
                            </span>
                        @elseif($event->candidates->count() < 2)
                            <a
                                href="{{ route('events.candidates.create', $event) }}"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-800 font-semibold text-xs transition-colors cursor-pointer"
                                title="Wajib minimal 2 paslon untuk mengaktifkan bilik suara"
                            >
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Tambah Paslon (Min. 2)</span>
                            </a>
                        @else
                            <button
                                type="submit"
                                name="status"
                                value="PUBLISHED"
                                class="inline-flex items-center px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold text-xs shadow-xs transition-colors cursor-pointer"
                            >
                                <span>Buka Bilik Suara</span>
                            </button>
                        @endif
                    @endif

                    @if($event->status === 'PUBLISHED')
                        <button
                            type="submit"
                            name="status"
                            value="DRAFT"
                            class="inline-flex items-center px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 active:bg-slate-100 text-slate-700 hover:text-slate-900 font-semibold text-xs border border-slate-300 hover:border-slate-400 shadow-2xs transition-colors cursor-pointer"
                        >
                            <span>Ubah ke Draft</span>
                        </button>
                    @endif

                    @if($event->status === 'CLOSED')
                        <button
                            type="submit"
                            name="status"
                            value="DRAFT"
                            class="inline-flex items-center px-3.5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs border border-slate-900 transition-colors cursor-pointer"
                        >
                            <span>Buka Kembali</span>
                        </button>
                    @endif
                </form>
            </div>
        </div>

        <!-- 3 Quick Metrics Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6">
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80">
                <span class="text-[11px] text-slate-500 font-semibold uppercase tracking-wider">Total Paslon</span>
                <div class="text-2xl font-bold tabular-nums text-slate-900 font-mono mt-1">
                    {{ $event->candidates->count() }}
                    <span class="text-sm font-sans font-semibold text-slate-500">Paslon</span>
                </div>
                <a href="{{ route('events.candidates.index', $event) }}" class="text-xs font-semibold text-slate-700 hover:text-slate-900 hover:underline mt-1.5 inline-block">
                    Kelola Paslon
                </a>
            </div>

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80">
                <span class="text-[11px] text-slate-500 font-semibold uppercase tracking-wider">Suara Masuk</span>
                <div class="text-2xl font-bold tabular-nums text-slate-900 font-mono mt-1">
                    {{ $totalVotes }}
                    <span class="text-sm font-sans font-semibold text-slate-500">Suara</span>
                </div>
                <span class="text-xs text-slate-500 mt-1.5 inline-block font-medium">Dari total {{ $totalDPT }} pemilih ({{ $event->target_scope_short_label }})</span>
            </div>

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80">
                <span class="text-[11px] text-slate-500 font-semibold uppercase tracking-wider">Partisipasi Pemilih</span>
                <div class="text-2xl font-bold tabular-nums text-slate-900 font-mono mt-1">
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
        <x-card title="Kode Bilik">
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/90 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 block mb-0.5">Kode Bilik:</span>
                    <span class="font-mono text-2xl font-bold tabular-nums text-slate-900 tracking-widest">
                        #{{ $event->room_code }}
                    </span>
                </div>
                <button
                    type="button"
                    @click="copy('{{ $event->room_code }}', 'Kode bilik #{{ $event->room_code }} disalin!')"
                    class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs shadow-xs border border-slate-900 transition-colors cursor-pointer"
                >
                    Salin Kode
                </button>
            </div>
            <p class="text-xs text-slate-500 mt-3">
                Gunakan kode ini untuk akses bilik suara pada portal e-voting.
            </p>
        </x-card>

        <!-- Officer Collaborator Invite Link -->
        <x-card title="Undang Panitia">
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
                    @click="copy('{{ $inviteUrl }}', 'Tautan panitia disalin!')"
                    class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs border border-slate-900 transition-colors shrink-0 cursor-pointer"
                >
                    Salin Tautan
                </button>
            </div>

            <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                <span>Panitia: <strong>{{ $event->officers->count() + 1 }} orang</strong></span>
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
                <h3 class="text-lg font-bold text-slate-900 tracking-tight">Daftar Pasangan Calon</h3>
            </div>

            <a
                href="{{ route('events.candidates.create', $event) }}"
                class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs shadow-xs border border-slate-900 transition-colors"
            >
                <span>Tambah Paslon</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($event->candidates as $candidate)
                <x-candidate-card :candidate="$candidate" :showActions="true">
                    <div class="w-full flex items-center justify-between pt-2">
                        <div class="text-xs">
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold tracking-wider">Perolehan</span>
                            <span class="text-base font-bold tabular-nums text-slate-900 font-mono">{{ $candidate->votes_count }}</span>
                            <span class="text-xs font-semibold text-slate-500">Suara</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a
                                href="{{ route('events.candidates.edit', [$event, $candidate]) }}"
                                class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors"
                            >
                                Edit
                            </a>
                            @if($event->votes()->count() === 0)
                                <form action="{{ route('events.candidates.destroy', [$event, $candidate]) }}" method="POST" onsubmit="return confirm('Hapus paslon ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-xs transition-colors cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </x-candidate-card>
            @empty
                <div class="col-span-3 text-center py-12 bg-white rounded-2xl border border-dashed border-slate-300">
                    <p class="text-sm font-semibold text-slate-500 mb-3">Belum ada pasangan calon.</p>
                    <a href="{{ route('events.candidates.create', $event) }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs shadow-xs border border-slate-900 transition-colors">
                        <span>Tambah Paslon</span>
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Danger Zone / Delete -->
    <x-card>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h4 class="text-sm font-bold text-slate-800">Hapus Agenda Pemilihan</h4>
                <p class="text-xs text-slate-500">Seluruh data kandidat dan perolehan suara pada agenda ini akan dihapus permanen.</p>
            </div>
            <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('YAKIN INGIN MENGHAPUS AGENDA INI? Tindakan ini tidak dapat dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs transition-colors cursor-pointer shrink-0">
                    Hapus Agenda
                </button>
            </form>
        </div>
    </x-card>
</div>
@endsection
