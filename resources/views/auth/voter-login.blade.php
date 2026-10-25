@extends('layouts.guest', ['title' => 'Bilik Suara - Masuk Pemilih'])

@section('content')
<div class="max-w-md mx-auto px-4 py-8 sm:py-12" x-data="{ fillDemo(nisn) { $refs.identifier.value = nisn; } }">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Header Banner Solid Agency-Grade -->
        <div class="bg-slate-900 p-8 text-white text-center border-b border-slate-800">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                Bilik Suara Terenkripsi
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-white">Otentikasi Pemilih</h2>
            <p class="text-xs text-slate-400 mt-1.5 max-w-xs mx-auto leading-relaxed">
                Masukkan NISN/NIP dan kode bilik suara untuk langsung menuju bilik pemilihan.
            </p>
        </div>

        <!-- Form Body -->
        <div class="p-6 sm:p-8">
            <!-- Alert & Error Notifications -->
            @if($errors->any())
                <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-3 shadow-xs">
                    <div class="w-6 h-6 rounded-lg bg-rose-100 flex items-center justify-center shrink-0 text-rose-600 mt-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-bold text-rose-900 block mb-0.5">Akses Ditolak</span>
                        <span>{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            @if(session('status'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium">{{ session('status') }}</span>
                </div>
            @endif

            <form action="{{ route('voter.login.submit') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Identifier (NISN / NIP) Input -->
                <div>
                    <label for="identifier" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nomor Identitas DPT (NISN / NIP)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                            </svg>
                        </span>
                        <input 
                            type="text" 
                            name="identifier" 
                            id="identifier"
                            x-ref="identifier"
                            value="{{ old('identifier') }}" 
                            required 
                            autofocus
                            placeholder="Masukkan NISN atau NIP Anda"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border {{ $errors->has('identifier') ? 'border-rose-400 ring-2 ring-rose-200' : 'border-slate-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900' }} text-slate-900 placeholder-slate-400 focus:outline-none text-sm font-semibold tracking-wide transition-colors"
                        />
                    </div>
                </div>

                <!-- Room Code / Token Pemilihan Input -->
                <div>
                    <label for="room_code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kode Room / Token Bilik Suara
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-mono font-bold text-sm">
                            #
                        </span>
                        <input 
                            type="text" 
                            name="room_code" 
                            id="room_code"
                            x-ref="room_code"
                            value="{{ old('room_code', request('room_code', '')) }}" 
                            required 
                            placeholder="Contoh: OSIS26"
                            class="w-full pl-9 pr-4 py-3 rounded-xl border {{ $errors->has('room_code') ? 'border-rose-400 ring-2 ring-rose-200' : 'border-slate-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900' }} text-slate-900 placeholder-slate-400 focus:outline-none text-sm font-mono font-bold uppercase tracking-wider transition-colors"
                        />
                    </div>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full py-3.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-bold text-sm border border-slate-800 shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-2 group"
                >
                    <span>Masuk ke Bilik Suara</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <!-- Quick Demo Helper for Presentation (UKOM) -->
            <div class="mt-8 pt-6 border-t border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        Demo Akun DPT (Siap Memilih)
                    </span>
                    <span class="text-[10px] font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">
                        1-Klik Isi
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    <button 
                        type="button" 
                        @click="fillDemo('102407')"
                        class="p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 hover:border-slate-300 text-slate-700 font-medium text-left transition-colors cursor-pointer group"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-slate-900 group-hover:text-blue-600">Gita Gutawa P.</span>
                            <span class="text-[9px] font-bold bg-slate-200 text-slate-800 px-1.5 py-0.5 rounded">Siswa (X-2)</span>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono">NISN: 102407</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Status: Terdaftar DPT</div>
                    </button>
                    <button 
                        type="button" 
                        @click="fillDemo('19850101')"
                        class="p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 hover:border-slate-300 text-slate-700 font-medium text-left transition-colors cursor-pointer group"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-slate-900 group-hover:text-blue-600">Drs. H. Mulyono</span>
                            <span class="text-[9px] font-bold bg-slate-200 text-slate-800 px-1.5 py-0.5 rounded">Dewan Guru</span>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono">NIP: 19850101</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Status: Terdaftar DPT</div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Switcher Clean Footer -->
    <div class="text-center mt-6">
        <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
            <span>Petugas atau Panitia Pemilihan?</span>
            <span class="font-bold text-blue-600 hover:underline">Masuk ke Panel Panitia &rarr;</span>
        </a>
    </div>
</div>
@endsection
