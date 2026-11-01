@extends('layouts.app', ['title' => 'Audit Trail & Log Aktivitas Sistem'])

@section('content')
<div class="space-y-6">
    <!-- Header & Maintenance Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Audit Trail & Log Keamanan
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Clear Cache Tool -->
            <form action="{{ route('admin.logs.clear-cache') }}" method="POST" onsubmit="return confirm('Bersihkan cache aplikasi?')">
                @csrf
                <button
                    type="submit"
                    class="inline-flex items-center px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold transition-colors cursor-pointer shadow-xs"
                    title="Pembersihan cache Laravel optimize:clear"
                >
                    Bersihkan Cache
                </button>
            </form>

            <!-- Prune Old Logs Button -->
            <form action="{{ route('admin.logs.prune') }}" method="POST" onsubmit="return confirm('Hapus audit log yang lebih lama dari 30 hari?')">
                @csrf
                <input type="hidden" name="days" value="30">
                <button
                    type="submit"
                    class="inline-flex items-center px-3.5 py-2 rounded-xl bg-white hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 border border-slate-200 text-slate-700 text-xs font-semibold transition-colors cursor-pointer shadow-xs"
                    title="Hapus riwayat log lama untuk efisiensi penyimpanan database"
                >
                    Prune Log (>30 Hari)
                </button>
            </form>
        </div>
    </div>

    <!-- Quick Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-stat-card
            title="Total Audit Trail"
            :value="number_format($totalLogs)"
            color="slate"
            icon='<svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><polyline points="14 2 14 8 20 8" /><line x1="16" y1="13" x2="8" y2="13" /><line x1="16" y1="17" x2="8" y2="17" /><line x1="10" y1="9" x2="8" y2="9" /></svg>'
        />

        <x-stat-card
            title="Aktivitas Hari Ini"
            :value="number_format($todayLogs)"
            color="slate"
            icon='<svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><polyline points="12 6 12 12 16 14" /></svg>'
        />

        <x-stat-card
            title="Keamanan"
            :value="number_format($securityEvents)"
            color="slate"
            icon='<svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg>'
        />
    </div>

    <!-- Filter & Search Toolbar -->
    <x-card>
        <form method="GET" action="{{ route('admin.logs.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6">
                <label for="search" class="block text-xs font-medium text-slate-700 mb-1.5">
                    Cari Catatan Log
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
                        value="{{ $search ?? '' }}"
                        placeholder="Cari deskripsi aktivitas atau alamat IP..."
                        class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-colors"
                    />
                </div>
            </div>

            <div class="sm:col-span-4">
                <label for="action" class="block text-xs font-medium text-slate-700 mb-1.5">
                    Kategori Aksi
                </label>
                <select
                    id="action"
                    name="action"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-colors bg-white"
                >
                    <option value="">Semua Kategori Aksi</option>
                    @foreach($actionTypes as $type)
                        <option value="{{ $type }}" {{ ($action ?? '') === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex items-end gap-2">
                <button
                    type="submit"
                    class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white text-xs font-semibold transition-colors text-center shadow-xs cursor-pointer border border-slate-900"
                >
                    Filter
                </button>
                @if(!empty($search) || !empty($action))
                    <a
                        href="{{ route('admin.logs.index') }}"
                        class="px-2.5 py-2 text-xs text-slate-500 hover:text-slate-800 font-medium transition-colors"
                    >
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Logs Table -->
    <x-card :noPadding="true">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-500 font-medium text-xs">
                        <th class="py-3 px-6">Waktu Kejadian</th>
                        <th class="py-3 px-6">Aksi & Kategori</th>
                        <th class="py-3 px-6">Aktor / Pengguna</th>
                        <th class="py-3 px-6">Rincian Aktivitas</th>
                        <th class="py-3 px-6">Alamat IP & Jaringan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        @php
                            $actionStyle = match(true) {
                                str_contains($log->action, 'DELETE') || str_contains($log->action, 'PRUNE') => [
                                    'badge' => 'bg-rose-50 text-rose-700 border-rose-200/80',
                                    'dot' => 'bg-rose-500',
                                ],
                                str_contains($log->action, 'VOTE') => [
                                    'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                    'dot' => 'bg-emerald-500',
                                ],
                                str_contains($log->action, 'UPDATE') || str_contains($log->action, 'STATUS') => [
                                    'badge' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                    'dot' => 'bg-amber-500',
                                ],
                                default => [
                                    'badge' => 'bg-slate-100 text-slate-700 border-slate-200/80',
                                    'dot' => 'bg-slate-400',
                                ]
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Waktu -->
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <div class="text-xs font-medium text-slate-800 tabular-nums">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </div>
                                <div class="text-xs text-slate-500 font-normal">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md border text-xs font-mono font-medium {{ $actionStyle['badge'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $actionStyle['dot'] }}"></span>
                                    {{ $log->action }}
                                </span>
                            </td>

                            <!-- Aktor -->
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                @if($log->user_type === 'USER' && $log->user)
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg {{ $log->user->role === 'SUPER_ADMIN' ? 'bg-slate-900 text-white border border-slate-800' : 'bg-slate-100 text-slate-700 border border-slate-200' }} flex items-center justify-center font-bold text-xs uppercase shrink-0 shadow-2xs">
                                            {{ substr($log->user->nama ?? $log->actor_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-slate-900 leading-snug">
                                                {{ $log->user->nama ?? $log->actor_name }}
                                            </div>
                                            <div class="text-xs text-slate-500 font-normal">
                                                {{ $log->user->role === 'SUPER_ADMIN' ? 'Administrator Sistem' : 'Panitia Pelaksana' }}
                                            </div>
                                        </div>
                                    </div>
                                @elseif($log->user_type === 'VOTER' && $log->voter)
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 border border-slate-200 flex items-center justify-center font-bold text-xs uppercase shrink-0 shadow-2xs">
                                            {{ substr($log->voter->nama ?? $log->actor_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-slate-900 leading-snug">
                                                {{ $log->voter->nama ?? $log->actor_name }}
                                            </div>
                                            <div class="text-xs text-slate-500 font-normal">
                                                NISN: <span class="font-mono tabular-nums">{{ $log->voter->identifier }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center font-bold text-xs uppercase shrink-0 shadow-2xs">
                                            S
                                        </div>
                                        <div>
                                            <div class="font-semibold text-slate-700 leading-snug">
                                                {{ $log->actor_name }}
                                            </div>
                                            <div class="text-xs text-slate-500 font-normal">
                                                {{ $log->actor_role }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>

                            <!-- Deskripsi -->
                            <td class="py-3.5 px-6">
                                <div class="text-slate-700 font-normal max-w-md wrap-break-word leading-relaxed text-xs">
                                    {{ $log->description }}
                                </div>
                            </td>

                            <!-- IP & Metadata -->
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <div>
                                    <span class="font-mono text-xs text-slate-600 bg-slate-100/80 px-2 py-0.5 rounded border border-slate-200/60 tabular-nums">{{ $log->ip_address ?? '127.0.0.1' }}</span>
                                </div>
                                @if($log->user_agent)
                                    <div class="text-xs text-slate-400 truncate max-w-40 mt-1" title="{{ $log->user_agent }}">
                                        {{ Str::limit($log->user_agent, 28) }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="16" y1="13" x2="8" y2="13" />
                                        <line x1="16" y1="17" x2="8" y2="17" />
                                        <line x1="10" y1="9" x2="8" y2="9" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-600">Belum ada riwayat audit log ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Aktivitas sistem dan event keamanan akan dicatat secara otomatis di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 sm:px-6 border-t border-slate-200 bg-slate-50/40">
                {{ $logs->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
