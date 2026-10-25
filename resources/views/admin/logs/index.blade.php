@extends('layouts.app', ['title' => 'Audit Trail & Log Aktivitas Sistem'])

@section('content')
<div class="space-y-6">
    <!-- Header & Maintenance Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Audit Trail & Log Keamanan
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Catatan immutable seluruh aktivitas sistem, autentikasi, mutasi data, dan penegakan integritas bilik suara.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Clear Cache Tool -->
            <form action="{{ route('admin.logs.clear-cache') }}" method="POST" onsubmit="return confirm('Bersihkan seluruh cache aplikasi (config, route, views)?')">
                @csrf
                <button 
                    type="submit" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-amber-50 hover:border-amber-200 hover:text-amber-800 border border-slate-200 text-slate-700 text-xs font-semibold transition-colors cursor-pointer shadow-xs"
                    title="Pembersihan cache Laravel optimize:clear"
                >
                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Bersihkan Cache</span>
                </button>
            </form>

            <!-- Prune Old Logs Button -->
            <form action="{{ route('admin.logs.prune') }}" method="POST" onsubmit="return confirm('Hapus audit log yang lebih lama dari 30 hari?')">
                @csrf
                <input type="hidden" name="days" value="30">
                <button 
                    type="submit" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 border border-slate-200 text-slate-700 text-xs font-semibold transition-colors cursor-pointer shadow-xs"
                    title="Hapus riwayat log lama untuk efisiensi penyimpanan database"
                >
                    <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Prune Log (>30 Hari)</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Quick Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-stat-card 
            title="Total Audit Trail" 
            :value="number_format($totalLogs)" 
            subtitle="Seluruh rekaman aktivitas sistem"
            color="blue"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
        />

        <x-stat-card 
            title="Aktivitas Hari Ini" 
            :value="number_format($todayLogs)" 
            subtitle="Interaksi tercatat hari ini"
            color="emerald"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />

        <x-stat-card 
            title="Keamanan & Mutasi" 
            :value="number_format($securityEvents)" 
            subtitle="Autentikasi, modifikasi & event kritis"
            color="rose"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'
        />
    </div>

    <!-- Filter & Search Toolbar -->
    <x-card class="p-4">
        <form method="GET" action="{{ route('admin.logs.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search ?? '' }}" 
                        placeholder="Cari deskripsi aktivitas atau alamat IP..." 
                        class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-colors"
                    />
                </div>
            </div>

            <div class="sm:col-span-4">
                <select 
                    name="action" 
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-colors bg-white"
                >
                    <option value="">Semua Kategori Aksi</option>
                    @foreach($actionTypes as $type)
                        <option value="{{ $type }}" {{ ($action ?? '') === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button 
                    type="submit" 
                    class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-colors text-center shadow-xs cursor-pointer"
                >
                    Filter
                </button>
                @if(!empty($search) || !empty($action))
                    <a 
                        href="{{ route('admin.logs.index') }}" 
                        class="px-2.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 text-xs font-semibold transition-colors inline-flex items-center gap-1 shadow-xs"
                        title="Reset Filter"
                        aria-label="Reset Filter"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Reset</span>
                    </a>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Logs Table -->
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
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
                                    'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'dot' => 'bg-rose-500',
                                ],
                                str_contains($log->action, 'VOTE') => [
                                    'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'dot' => 'bg-emerald-500',
                                ],
                                str_contains($log->action, 'LOGIN') || str_contains($log->action, 'AUTH') => [
                                    'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'dot' => 'bg-blue-600',
                                ],
                                str_contains($log->action, 'UPDATE') || str_contains($log->action, 'STATUS') => [
                                    'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'dot' => 'bg-amber-500',
                                ],
                                default => [
                                    'badge' => 'bg-slate-50 text-slate-700 border-slate-200',
                                    'dot' => 'bg-slate-400',
                                ]
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Waktu -->
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <div class="font-mono text-xs font-semibold text-slate-800">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </div>
                                <div class="text-[11px] text-slate-400 font-medium">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[10px] font-mono font-bold uppercase {{ $actionStyle['badge'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $actionStyle['dot'] }}"></span>
                                    {{ $log->action }}
                                </span>
                            </td>

                            <!-- Aktor -->
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                @if($log->user_type === 'USER' && $log->user)
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 border border-blue-200 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                            {{ substr($log->user->nama ?? $log->actor_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 leading-snug">
                                                {{ $log->user->nama ?? $log->actor_name }}
                                            </div>
                                            <div class="text-[10px] text-slate-500 font-mono">
                                                {{ $log->user->role ?? $log->actor_role }}
                                            </div>
                                        </div>
                                    </div>
                                @elseif($log->user_type === 'VOTER' && $log->voter)
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                            {{ substr($log->voter->nama ?? $log->actor_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 leading-snug">
                                                {{ $log->voter->nama ?? $log->actor_name }}
                                            </div>
                                            <div class="text-[10px] text-slate-500 font-mono">
                                                NISN: {{ $log->voter->identifier }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                            S
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-700 leading-snug">
                                                {{ $log->actor_name }}
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-mono">
                                                {{ $log->actor_role }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>

                            <!-- Deskripsi -->
                            <td class="py-3.5 px-6">
                                <div class="text-slate-800 font-medium max-w-md break-words leading-relaxed text-xs">
                                    {{ $log->description }}
                                </div>
                            </td>

                            <!-- IP & Metadata -->
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <div class="font-mono text-xs text-slate-700">
                                    <span class="bg-slate-100 px-2 py-0.5 rounded border border-slate-200">{{ $log->ip_address ?? '127.0.0.1' }}</span>
                                </div>
                                @if($log->user_agent)
                                    <div class="text-[10px] text-slate-400 truncate max-w-[160px] mt-1" title="{{ $log->user_agent }}">
                                        {{ Str::limit($log->user_agent, 28) }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
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
            <div class="p-4 border-t border-slate-200">
                {{ $logs->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
