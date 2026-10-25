@extends('layouts.app', ['title' => 'Manajemen Akun Panitia & Admin'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Kelola Panitia & Administrator
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Daftar akun pengelola pemilihan, hak akses Super Admin, dan Petugas Kesiswaan sekolah.
            </p>
        </div>

        <a 
            href="{{ route('admin.users.create') }}" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-xs border border-slate-800 transition-colors cursor-pointer"
        >
            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>+ Tambah Akun Baru</span>
        </a>
    </div>

    <!-- Quick Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-stat-card 
            title="Total Akun Pengelola" 
            :value="number_format($totalUsers)" 
            subtitle="Seluruh operator & administrator terdaftar"
            color="blue"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'
        />

        <x-stat-card 
            title="Super Administrator" 
            :value="number_format($totalSuperAdmin)" 
            subtitle="Akses penuh ke master DPT & audit trail"
            color="slate"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'
        />

        <x-stat-card 
            title="Petugas / Panitia" 
            :value="number_format($totalPetugas)" 
            subtitle="Pengelola agenda pemilihan mandiri"
            color="amber"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>'
        />
    </div>

    <!-- Filter Card -->
    <x-card>
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label for="search" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Cari Nama atau Alamat Email
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        value="{{ $search }}"
                        placeholder="Ketik nama operator atau alamat email..." 
                        class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-xs sm:text-sm transition-colors"
                    />
                </div>
            </div>

            <div>
                <label for="role" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Filter Peran (Role)
                </label>
                <div class="flex items-center gap-2">
                    <select 
                        id="role" 
                        name="role" 
                        class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 text-xs sm:text-sm bg-white transition-colors"
                    >
                        <option value="">Semua Peran</option>
                        <option value="SUPER_ADMIN" {{ $role === 'SUPER_ADMIN' ? 'selected' : '' }}>SUPER ADMIN</option>
                        <option value="PETUGAS" {{ $role === 'PETUGAS' ? 'selected' : '' }}>PETUGAS / PANITIA</option>
                    </select>

                    <button 
                        type="submit" 
                        class="px-4 py-2 rounded-xl bg-slate-900 text-white font-semibold text-xs hover:bg-slate-800 transition-colors cursor-pointer shrink-0 shadow-xs"
                    >
                        Filter
                    </button>
                    @if($search || $role)
                        <a href="{{ route('admin.users.index') }}" class="px-2.5 py-2 text-xs text-slate-500 hover:text-slate-800 font-semibold">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </x-card>

    <!-- Table Users -->
    <x-card :noPadding="true">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-6">Nama Pengguna</th>
                        <th class="py-3 px-6">Email Login</th>
                        <th class="py-3 px-6">Peran Akses</th>
                        <th class="py-3 px-6">Terdaftar Sejak</th>
                        <th class="py-3 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl {{ $user->role === 'SUPER_ADMIN' ? 'bg-slate-900 text-white border border-slate-800' : 'bg-slate-100 text-slate-700 border border-slate-200' }} flex items-center justify-center font-bold text-xs uppercase shrink-0 shadow-2xs">
                                        {{ substr($user->nama, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 flex items-center gap-1.5 leading-snug">
                                            <span>{{ $user->nama }}</span>
                                            @if($user->id === auth()->id())
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                    Anda
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-medium">
                                            {{ $user->role === 'SUPER_ADMIN' ? 'Administrator Sistem' : 'Panitia Pelaksana' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 font-mono text-slate-700 text-xs">
                                <span class="bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">{{ $user->email }}</span>
                            </td>
                            <td class="py-3.5 px-6">
                                @if($user->role === 'SUPER_ADMIN')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-900 text-white border border-slate-800">
                                        <svg class="w-3 h-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                        SUPER ADMIN
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                        <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        PETUGAS
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-slate-500 text-xs font-mono">
                                {{ $user->created_at->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a 
                                        href="{{ route('admin.users.edit', $user) }}" 
                                        class="px-2.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-semibold text-xs transition-colors shadow-2xs inline-flex items-center gap-1"
                                        title="Edit Akun"
                                    >
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>Edit</span>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus akun {{ $user->nama }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                class="px-2.5 py-1.5 rounded-xl bg-white hover:bg-rose-50 border border-slate-200 hover:border-rose-200 text-slate-600 hover:text-rose-700 font-semibold text-xs transition-colors cursor-pointer shadow-2xs inline-flex items-center gap-1"
                                                title="Hapus Akun"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-600">Tidak ada data pengguna ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Gunakan kata kunci lain atau tambahkan akun panitia baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
