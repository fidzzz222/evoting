@extends('layouts.app', ['title' => 'Edit Data DPT: ' . $voter->nama])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.voters.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors mb-2">
            Kembali ke Master DPT
        </a>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
            Edit Data Pemilih: {{ $voter->nama }}
        </h1>
        <p class="text-xs text-slate-500 mt-1">
            Perbarui data identitas pemilih atau status hak pilih.
        </p>
    </div>

    <x-card>
        <form action="{{ route('admin.voters.update', $voter) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Nama Lengkap -->
            <div>
                <label for="nama" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                    Nama Lengkap Pemilih <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nama" 
                    name="nama" 
                    value="{{ old('nama', $voter->nama) }}" 
                    required 
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-sm font-semibold text-slate-900 transition-colors"
                />
                @error('nama')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- NISN / NIP -->
            <div>
                <label for="nisn" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                    NISN (Siswa) / NIP (Guru) <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nisn" 
                    name="nisn" 
                    value="{{ old('nisn', $voter->nisn) }}" 
                    required 
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-sm font-mono text-slate-900 transition-colors"
                />
                @error('nisn')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tipe Pemilih & Kelas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="voter_type" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        Kategori Pemilih <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        id="voter_type" 
                        name="voter_type" 
                        required 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-sm font-semibold text-slate-900 transition-colors"
                    >
                        <option value="SISWA" {{ old('voter_type', $voter->voter_type) === 'SISWA' ? 'selected' : '' }}>SISWA</option>
                        <option value="GURU" {{ old('voter_type', $voter->voter_type) === 'GURU' ? 'selected' : '' }}>GURU / TENAGA KEPENDIDIKAN</option>
                    </select>
                    @error('voter_type')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kelas" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        Kelas / Rombel
                    </label>
                    <input 
                        type="text" 
                        id="kelas" 
                        name="kelas" 
                        value="{{ old('kelas', $voter->kelas) }}" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-sm text-slate-900 transition-colors"
                    />
                    @error('kelas')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                <input 
                    type="checkbox" 
                    id="is_active" 
                    name="is_active" 
                    value="1" 
                    {{ old('is_active', $voter->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 rounded text-slate-900 focus:ring-slate-900/10 focus:ring-2 border-slate-300"
                />
                <label for="is_active" class="text-xs font-semibold text-slate-700 cursor-pointer">
                    Hak Pilih Aktif (Pemilih dapat login ke bilik suara)
                </label>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a 
                    href="{{ route('admin.voters.index') }}" 
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs shadow-xs border border-slate-900 transition-colors cursor-pointer"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
