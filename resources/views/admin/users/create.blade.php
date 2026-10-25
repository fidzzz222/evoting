@extends('layouts.app', ['title' => 'Tambah Akun Panitia'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors mb-2">
            &larr; Kembali ke Daftar Pengguna
        </a>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">
            Tambah Akun Panitia / Admin
        </h1>
        <p class="text-xs text-slate-500 mt-1">
            Buat kredensial login baru untuk panitia pemilihan atau administrator sistem.
        </p>
    </div>

    <x-card>
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Nama -->
            <div>
                <label for="nama" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nama" 
                    name="nama" 
                    value="{{ old('nama') }}" 
                    placeholder="Contoh: Pak Budi Santoso, S.Kom" 
                    required 
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm font-semibold text-slate-900"
                />
                @error('nama')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Alamat Email (Untuk Login) <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    placeholder="panitia@sekolah.sch.id" 
                    required 
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-900"
                />
                @error('email')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role Selection -->
            <div>
                <label for="role" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Peran Hak Akses (Role) <span class="text-rose-500">*</span>
                </label>
                <select 
                    id="role" 
                    name="role" 
                    required 
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm font-semibold text-slate-900"
                >
                    <option value="PETUGAS" {{ old('role') === 'PETUGAS' ? 'selected' : '' }}>PETUGAS (Dapat mengelola agenda pemilihan yang ditugaskan)</option>
                    <option value="SUPER_ADMIN" {{ old('role') === 'SUPER_ADMIN' ? 'selected' : '' }}>SUPER_ADMIN (Akses penuh ke seluruh sistem, master DPT & log)</option>
                </select>
                @error('role')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password & Confirmation -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder="Minimal 6 karakter"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-900"
                    />
                    @error('password')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required 
                        placeholder="Ulangi kata sandi"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-sm text-slate-900"
                    />
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a 
                    href="{{ route('admin.users.index') }}" 
                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer"
                >
                    Simpan Akun
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
