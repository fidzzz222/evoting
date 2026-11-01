@extends('layouts.app', ['title' => 'Manajemen Akun Panitia & Admin'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Kelola Panitia & Administrator
            </h1>
        </div>

        <a
            href="{{ route('admin.users.create') }}"
            class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs shadow-xs border border-slate-900 transition-colors cursor-pointer"
        >
            Tambah Akun Baru
        </a>
    </div>

    <!-- Quick Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-stat-card
            title="Total Akun Pengelola"
            :value="number_format($totalUsers)"
            color="slate"
            icon='<svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /></svg>'
        />

        <x-stat-card
            title="Super Administrator"
            :value="number_format($totalSuperAdmin)"
            color="slate"
            icon='<svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /><path d="m9 12 2 2 4-4" /></svg>'
        />

        <x-stat-card
            title="Petugas / Panitia"
            :value="number_format($totalPetugas)"
            color="slate"
            icon='<svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><polyline points="16 11 18 13 22 9" /></svg>'
        />
    </div>

    <!-- Filter Card -->
    <x-card>
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label for="search" class="block text-xs font-medium text-slate-700 mb-1.5">
                    Cari Nama atau Alamat Email
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
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
                        placeholder="Ketik nama operator atau alamat email..."
                        class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-xs sm:text-sm text-slate-900 transition-colors"
                    />
                </div>
            </div>

            <div>
                <label for="role" class="block text-xs font-medium text-slate-700 mb-1.5">
                    Filter Peran (Role)
                </label>
                <div class="flex items-center gap-2">
                    <select
                        id="role"
                        name="role"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-xs sm:text-sm text-slate-900 bg-white transition-colors"
                    >
                        <option value="">Semua Peran</option>
                        <option value="SUPER_ADMIN" {{ $role === 'SUPER_ADMIN' ? 'selected' : '' }}>SUPER ADMIN</option>
                        <option value="PETUGAS" {{ $role === 'PETUGAS' ? 'selected' : '' }}>PETUGAS / PANITIA</option>
                    </select>

                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl bg-slate-900 text-white font-medium text-xs hover:bg-slate-800 active:bg-slate-950 transition-colors cursor-pointer shrink-0 shadow-xs border border-slate-900"
                    >
                        Filter
                    </button>
                    @if($search || $role)
                        <a href="{{ route('admin.users.index') }}" class="px-2.5 py-2 text-xs text-slate-500 hover:text-slate-800 font-medium transition-colors">
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
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-xs font-medium text-slate-500">
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
                                        <div class="font-semibold text-slate-900 flex items-center gap-1.5 leading-snug">
                                            <span>{{ $user->nama }}</span>
                                            @if($user->id === auth()->id())
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200/80">
                                                    Anda
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500 font-normal">
                                            {{ $user->role === 'SUPER_ADMIN' ? 'Administrator Sistem' : 'Panitia Pelaksana' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 text-slate-600 text-xs font-normal">
                                {{ $user->email }}
                            </td>
                            <td class="py-3.5 px-6">
                                @if($user->role === 'SUPER_ADMIN')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-900 text-white">
                                        SUPER ADMIN
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200/80">
                                        PETUGAS
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-slate-600 text-xs font-normal tabular-nums">
                                {{ $user->created_at->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a
                                        href="{{ route('admin.users.edit', $user) }}"
                                        class="px-2.5 py-1 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 hover:text-slate-900 font-medium text-xs transition-colors shadow-2xs"
                                        title="Edit Akun"
                                    >
                                        Edit
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus akun {{ $user->nama }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="px-2.5 py-1 rounded-lg bg-white hover:bg-rose-50 border border-slate-200 hover:border-rose-200 text-slate-600 hover:text-rose-700 font-medium text-xs transition-colors cursor-pointer shadow-2xs"
                                                title="Hapus Akun"
                                            >
                                                Hapus
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
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="10" cy="8" r="4" />
                                        <path d="M4 20c0-3.3 2.7-6 6-6h2" />
                                        <circle cx="17" cy="17" r="3" />
                                        <path d="m19.5 19.5 2.5 2.5" />
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
            <div class="p-4 sm:px-6 border-t border-slate-200 bg-slate-50/40">
                {{ $users->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
