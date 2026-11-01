@extends('layouts.app', ['title' => 'Master Data Pemilih'])

@section('content')
<div class="space-y-6" x-data="{
    importModalOpen: false
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Master Data Pemilih
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Download Template CSV -->
            <a
                href="{{ route('admin.voters.template') }}"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-xs transition-colors cursor-pointer"
            >
                Unduh Format CSV
            </a>

            <!-- Export DPT CSV -->
            <a
                href="{{ route('admin.voters.export') }}"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-xs transition-colors cursor-pointer"
            >
                Ekspor Kartu Pemilih
            </a>

            <!-- Import CSV Button -->
            <button
                type="button"
                @click="$dispatch('open-modal', 'importModalOpen')"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 hover:text-slate-900 font-semibold text-xs transition-colors cursor-pointer shadow-xs"
            >
                Import Massal (CSV)
            </button>

            <!-- Manual Add Button -->
            <a
                href="{{ route('admin.voters.create') }}"
                class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs shadow-xs border border-slate-900 transition-colors cursor-pointer"
            >
                Tambah Pemilih
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-stat-card
            title="Total Terdaftar"
            :value="number_format($totalDPT)"
            color="slate"
            icon='<svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M16 11l2 2 4-4" /></svg>'
        />

        <x-stat-card
            title="Pemilih Siswa"
            :value="number_format($totalSiswa)"
            color="slate"
            icon='<svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z" /><path d="M6 12v5c0 2 2.5 3.5 6 3.5s6-1.5 6-3.5v-5" /></svg>'
        />

        <x-stat-card
            title="Pemilih Guru"
            :value="number_format($totalGuru)"
            color="slate"
            icon='<svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" /><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" /></svg>'
        />
    </div>

    <!-- Filter Card -->
    <x-card>
        <form method="GET" action="{{ route('admin.voters.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label for="search" class="block text-xs font-medium text-slate-700 mb-1.5">
                    Cari Nama / NISN
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                    </span>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Nama atau NISN..."
                        class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 transition-colors"
                    />
                </div>
            </div>

            <div>
                <label for="type" class="block text-xs font-medium text-slate-700 mb-1.5">
                    Kategori
                </label>
                <select
                    id="type"
                    name="type"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-xs sm:text-sm text-slate-900 transition-colors"
                >
                    <option value="">Semua Kategori</option>
                    <option value="SISWA" {{ $type === 'SISWA' ? 'selected' : '' }}>SISWA</option>
                    <option value="GURU" {{ $type === 'GURU' ? 'selected' : '' }}>GURU</option>
                </select>
            </div>

            <div>
                <label for="kelas" class="block text-xs font-medium text-slate-700 mb-1.5">
                    Kelas / Rombel
                </label>
                <div class="flex items-center gap-2">
                    <select
                        id="kelas"
                        name="kelas"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-xs sm:text-sm text-slate-900 transition-colors"
                    >
                        <option value="">Semua Kelas</option>
                        @foreach($classList as $c)
                            <option value="{{ $c }}" {{ $kelas === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>

                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl bg-slate-900 text-white font-medium text-xs hover:bg-slate-800 active:bg-slate-950 transition-colors cursor-pointer shrink-0 shadow-xs border border-slate-900"
                    >
                        Cari
                    </button>
                    @if($search || $type || $kelas)
                        <a href="{{ route('admin.voters.index') }}" class="px-2.5 py-2 text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl font-medium transition-colors shrink-0">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </x-card>

    <!-- Table DPT -->
    <x-card :noPadding="true">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-xs font-medium text-slate-500">
                        <th class="py-3 px-6">Nama Lengkap</th>
                        <th class="py-3 px-6">NISN / NIP</th>
                        <th class="py-3 px-6">Kategori</th>
                        <th class="py-3 px-6">Kelas</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @forelse($voters as $voter)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl {{ $voter->voter_type === 'GURU' ? 'bg-slate-900 text-white border border-slate-800' : 'bg-slate-100 text-slate-700 border border-slate-200' }} flex items-center justify-center font-bold text-xs uppercase shrink-0 shadow-2xs">
                                        {{ substr($voter->nama, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900 leading-snug">{{ $voter->nama }}</div>
                                        <div class="text-xs text-slate-500 font-normal">{{ $voter->voter_type === 'GURU' ? 'Tenaga Pendidik' : 'Peserta Didik' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="font-mono text-xs text-slate-700 bg-slate-100/80 px-2 py-0.5 rounded border border-slate-200/60 tabular-nums">{{ $voter->nisn }}</span>
                            </td>
                            <td class="py-3.5 px-6">
                                @if($voter->voter_type === 'GURU')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-900 text-white">
                                        GURU
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200/80">
                                        SISWA
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-slate-600 font-medium text-xs">
                                {{ $voter->kelas ?? '-' }}
                            </td>
                            <td class="py-3.5 px-6 text-center">
                                <form action="{{ route('admin.voters.toggle', $voter) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        class="cursor-pointer transition-opacity hover:opacity-80"
                                        title="Klik untuk mengubah status hak pilih"
                                    >
                                        @if($voter->is_active)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-50 text-slate-500 border border-slate-200/80">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Nonaktif
                                            </span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Edit -->
                                    <a
                                        href="{{ route('admin.voters.edit', $voter) }}"
                                        class="px-2.5 py-1 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 hover:text-slate-900 font-medium text-xs transition-colors shadow-2xs"
                                        title="Edit Data Pemilih"
                                    >
                                        Edit
                                    </a>

                                    <!-- Delete -->
                                    <form action="{{ route('admin.voters.destroy', $voter) }}" method="POST" onsubmit="return confirm('Hapus data pemilih {{ $voter->nama }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="px-2.5 py-1 rounded-lg bg-white hover:bg-rose-50 border border-slate-200 hover:border-rose-200 text-slate-600 hover:text-rose-700 font-medium text-xs transition-colors cursor-pointer shadow-2xs"
                                            title="Hapus Pemilih"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="10" cy="8" r="4" />
                                        <path d="M4 20c0-3.3 2.7-6 6-6h2" />
                                        <circle cx="17" cy="17" r="3" />
                                        <path d="m19.5 19.5 2.5 2.5" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-600">Belum ada data DPT yang terdaftar</p>
                                <p class="text-xs text-slate-400 mt-1">Tambahkan pemilih secara manual atau gunakan fitur import massal CSV.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($voters->hasPages())
            <div class="p-4 sm:px-6 border-t border-slate-200 bg-slate-50/40">
                {{ $voters->links() }}
            </div>
        @endif
    </x-card>

    <!-- Modal Import CSV -->
    <x-modal name="importModalOpen" title="Import Massal Daftar Pemilih (CSV)">
        <form action="{{ route('admin.voters.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs text-slate-600 space-y-2">
                <div class="flex items-center gap-1.5 font-semibold text-slate-800">
                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4" />
                        <path d="M12 8h.01" />
                    </svg>
                    <span>Panduan Format File:</span>
                </div>
                <p>
                    File harus berekstensi <strong>.csv</strong> dengan susunan kolom:
                    <br><code class="bg-white px-2 py-0.5 rounded border border-slate-200 font-mono text-[11px] text-slate-800 inline-block mt-1">nama,nisn,voter_type,kelas</code>
                </p>
                <p class="text-xs text-slate-500">
                    &bull; Kolom <code class="font-mono text-xs">voter_type</code> dapat diisi: <span class="font-semibold text-slate-700">SISWA</span> atau <span class="font-semibold text-slate-700">GURU</span>.<br>
                    &bull; Pemilih login cukup menggunakan NISN/NIP dan Kode Bilik Suara.<br>
                    &bull; Jika NISN sudah ada, data nama dan kelas akan diperbarui otomatis.
                </p>
            </div>

            <div>
                <label for="csv_file" class="block text-xs font-medium text-slate-700 mb-1.5">
                    Pilih File CSV <span class="text-rose-500">*</span>
                </label>
                <input
                    type="file"
                    id="csv_file"
                    name="csv_file"
                    accept=".csv,text/csv,text/plain"
                    required
                    class="block w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800 file:cursor-pointer transition-colors"
                />
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button
                    type="button"
                    @click="$dispatch('close-modal', 'importModalOpen')"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs shadow-xs border border-slate-900 transition-colors cursor-pointer"
                >
                    Mulai Upload & Proses
                </button>
            </div>
        </form>
    </x-modal>
</div>
@endsection
