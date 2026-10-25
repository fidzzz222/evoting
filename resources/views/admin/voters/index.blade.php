@extends('layouts.app', ['title' => 'Master Data DPT (Daftar Pemilih Tetap)'])

@section('content')
<div class="space-y-6" x-data="{
    importModalOpen: false
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Master Data DPT (Daftar Pemilih Tetap)
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Kelola data seluruh siswa dan guru yang berhak memberikan suara dalam sistem e-voting.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Download Template CSV -->
            <a 
                href="{{ route('admin.voters.template') }}" 
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Format CSV</span>
            </a>

            <!-- Export DPT CSV -->
            <a 
                href="{{ route('admin.voters.export') }}" 
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-emerald-50 text-emerald-700 font-semibold text-xs border border-emerald-200 shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Ekspor Kartu DPT</span>
            </a>

            <!-- Import CSV Button -->
            <button 
                type="button" 
                @click="$dispatch('open-modal', 'importModalOpen')"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 hover:text-slate-900 font-semibold text-xs flex items-center gap-1.5 transition-colors cursor-pointer shadow-2xs"
            >
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <span>Import Massal (CSV)</span>
            </button>

            <!-- Manual Add Button -->
            <a 
                href="{{ route('admin.voters.create') }}" 
                class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-xs border border-slate-800 flex items-center gap-1.5 transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Tambah Pemilih</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card 
            title="Total Terdaftar" 
            :value="number_format($totalDPT)" 
            subtitle="Total pemilih terdata"
            color="blue"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'
        />

        <x-stat-card 
            title="DPT Siswa" 
            :value="number_format($totalSiswa)" 
            subtitle="Siswa dari seluruh rombel"
            color="sky"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>'
        />

        <x-stat-card 
            title="DPT Guru & Staf" 
            :value="number_format($totalGuru)" 
            subtitle="Tenaga pendidik terverifikasi"
            color="slate"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'
        />

        <x-stat-card 
            title="Hak Pilih Aktif" 
            :value="number_format($activeDPT)" 
            :subtitle="($totalDPT > 0 ? round(($activeDPT / $totalDPT) * 100) : 0) . '% DPT dapat memilih'"
            color="emerald"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
    </div>

    <!-- Filter Card -->
    <x-card>
        <form method="GET" action="{{ route('admin.voters.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label for="search" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                    Cari Nama / NISN
                </label>
                <input 
                    type="text" 
                    id="search" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Nama atau NISN..." 
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-xs"
                />
            </div>

            <div>
                <label for="type" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                    Kategori
                </label>
                <select 
                    id="type" 
                    name="type" 
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-xs"
                >
                    <option value="">Semua Kategori</option>
                    <option value="SISWA" {{ $type === 'SISWA' ? 'selected' : '' }}>SISWA</option>
                    <option value="GURU" {{ $type === 'GURU' ? 'selected' : '' }}>GURU</option>
                </select>
            </div>

            <div>
                <label for="kelas" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                    Kelas / Rombel
                </label>
                <select 
                    id="kelas" 
                    name="kelas" 
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-xs"
                >
                    <option value="">Semua Kelas</option>
                    @foreach($classList as $c)
                        <option value="{{ $c }}" {{ $kelas === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                    Status Hak Pilih
                </label>
                <div class="flex items-center gap-2">
                    <select 
                        id="status" 
                        name="status" 
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-xs"
                    >
                        <option value="">Semua Status</option>
                        <option value="1" {{ $status === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ $status === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>

                    <button 
                        type="submit" 
                        class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-colors cursor-pointer shrink-0"
                    >
                        Cari
                    </button>
                    @if($search || $type || $kelas || $status !== null && $status !== '')
                        <a href="{{ route('admin.voters.index') }}" class="px-2 py-2 text-xs text-slate-500 hover:text-slate-800 font-semibold">
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
                    <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
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
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl {{ $voter->voter_type === 'GURU' ? 'bg-slate-900 text-white border border-slate-800' : 'bg-slate-100 text-slate-700 border border-slate-200' }} flex items-center justify-center font-bold text-xs uppercase shrink-0 shadow-2xs">
                                        {{ substr($voter->nama, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 leading-snug">{{ $voter->nama }}</div>
                                        <div class="text-[11px] text-slate-400 font-medium">{{ $voter->voter_type === 'GURU' ? 'Tenaga Pendidik' : 'Peserta Didik' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 font-mono text-slate-700 text-xs font-semibold">
                                <span class="bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">{{ $voter->nisn }}</span>
                            </td>
                            <td class="py-3.5 px-6">
                                @if($voter->voter_type === 'GURU')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-900 text-white border border-slate-800">
                                        <svg class="w-3 h-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        GURU
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                        <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                        </svg>
                                        SISWA
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-slate-700 font-semibold text-xs">
                                {{ $voter->kelas ?? '-' }}
                            </td>
                            <td class="py-3.5 px-6 text-center">
                                <form action="{{ route('admin.voters.toggle', $voter) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button 
                                        type="submit" 
                                        class="cursor-pointer transition-transform hover:scale-105 active:scale-95"
                                        title="Klik untuk mengubah status hak pilih"
                                    >
                                        @if($voter->is_active)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                AKTIF
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                NONAKTIF
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
                                        class="px-2.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-semibold text-xs transition-colors shadow-2xs inline-flex items-center gap-1"
                                        title="Edit Data Pemilih"
                                    >
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>Edit</span>
                                    </a>

                                    <!-- Delete -->
                                    <form action="{{ route('admin.voters.destroy', $voter) }}" method="POST" onsubmit="return confirm('Hapus data pemilih {{ $voter->nama }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="px-2.5 py-1.5 rounded-xl bg-white hover:bg-rose-50 border border-slate-200 hover:border-rose-200 text-slate-600 hover:text-rose-700 font-semibold text-xs transition-colors cursor-pointer shadow-2xs inline-flex items-center gap-1"
                                            title="Hapus Pemilih"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
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
            <div class="p-4 border-t border-slate-100">
                {{ $voters->links() }}
            </div>
        @endif
    </x-card>

    <!-- Modal Import CSV -->
    <x-modal name="importModalOpen" title="Import Massal Daftar Pemilih (CSV)">
        <form action="{{ route('admin.voters.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs text-slate-600 space-y-2">
                <span class="font-bold text-slate-800 block">Panduan Format File:</span>
                <p>
                    File harus berekstensi <strong>.csv</strong> dengan susunan kolom:
                    <br><code class="bg-white px-2 py-0.5 rounded border border-slate-300 font-mono text-[11px]">nama,nisn,voter_type,kelas</code>
                </p>
                <p class="text-[11px] text-slate-500">
                    &bull; Kolom <code class="font-mono">voter_type</code> dapat diisi: <span class="font-bold">SISWA</span> atau <span class="font-bold">GURU</span>.<br>
                    &bull; Pemilih login cukup menggunakan NISN/NIP dan Kode Bilik Suara.<br>
                    &bull; Jika NISN sudah ada, data nama dan kelas akan diperbarui otomatis.
                </p>
            </div>

            <div>
                <label for="csv_file" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Pilih File CSV <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="file" 
                    id="csv_file" 
                    name="csv_file" 
                    accept=".csv,text/csv,text/plain" 
                    required 
                    class="block w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 file:cursor-pointer"
                />
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button 
                    type="button" 
                    @click="$dispatch('close-modal', 'importModalOpen')" 
                    class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs border border-slate-800 transition-colors cursor-pointer"
                >
                    Mulai Upload & Proses
                </button>
            </div>
        </form>
    </x-modal>
</div>
@endsection
