@extends('layouts.guest', ['title' => 'Login Pemilih'])

@section('content')
<div class="max-w-lg w-full mx-auto px-4 py-6 sm:py-10" style="max-width: 520px;" x-data="{ fillDemo(nisn) { $refs.identifier.value = nisn; } }">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- header login pemilih -->
        <div class="bg-slate-900 px-6 py-6 sm:px-8 sm:py-7 text-white text-center border-b border-slate-800">
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Masuk Pemilih</h2>
        </div>

        <!-- form login -->
        <div class="p-6 sm:p-8">
            <!-- pesan notifikasi dan error -->
            @if($errors->any())
                <div class="mb-5 p-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs flex items-center gap-2.5">
                    <div class="w-5 h-5 rounded-md bg-rose-500/10 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                    </div>
                    <span class="font-medium text-slate-800 flex-1">{{ $errors->first() }}</span>
                </div>
            @endif

            @if(session('status'))
                <div class="mb-5 p-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs flex items-center gap-2.5">
                    <div class="w-5 h-5 rounded-md bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                    </div>
                    <span class="font-medium text-slate-800 flex-1">{{ session('status') }}</span>
                </div>
            @endif

            <form action="{{ route('voter.login.submit') }}" method="POST" class="space-y-6">
                @csrf

                <!-- input nisn atau nip -->
                <div>
                    <label for="identifier" class="block text-xs font-semibold text-slate-700 mb-2">
                        NISN atau NIP
                    </label>
                    <input 
                        type="text" 
                        name="identifier" 
                        id="identifier"
                        x-ref="identifier"
                        value="{{ old('identifier') }}" 
                        required 
                        autofocus
                        placeholder="Masukkan NISN atau NIP"
                        class="w-full px-4 py-3.5 rounded-xl border {{ $errors->has('identifier') ? 'border-rose-400 ring-2 ring-rose-200' : 'border-slate-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900' }} text-slate-900 placeholder-slate-400 focus:outline-none text-sm font-semibold tracking-wide transition-colors"
                    />
                </div>

                <!-- input kode akses pemilihan -->
                <div>
                    <label for="room_code" class="block text-xs font-semibold text-slate-700 mb-2">
                        Kode Akses Pemilihan
                    </label>
                    <input 
                        type="text" 
                        name="room_code" 
                        id="room_code"
                        x-ref="room_code"
                        value="{{ old('room_code', request('room_code', '')) }}" 
                        required 
                        placeholder="Contoh: OSIS26"
                        class="w-full px-4 py-3.5 rounded-xl border {{ $errors->has('room_code') ? 'border-rose-400 ring-2 ring-rose-200' : 'border-slate-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900' }} text-slate-900 placeholder-slate-400 focus:outline-none text-sm font-mono font-bold uppercase tracking-wider transition-colors"
                    />
                </div>

                <!-- tombol masuk -->
                <button 
                    type="submit" 
                    class="w-full py-3.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-sm border border-slate-900 shadow-xs transition-colors cursor-pointer flex items-center justify-center"
                >
                    Masuk
                </button>
            </form>

            {{-- akun demo untuk pengujian (di-hide sementara, jangan dihapus)
            <div class="mt-8 pt-6 border-t border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-600">
                        Akun Demo Pemilih
                    </span>
                    <span class="text-[10px] font-medium text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">
                        Klik untuk mengisi
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    <button 
                        type="button" 
                        @click="fillDemo('102407')"
                        class="p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 hover:border-slate-300 text-slate-700 font-medium text-left transition-colors cursor-pointer group"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-semibold text-slate-900 group-hover:text-slate-700">Gita Gutawa P.</span>
                            <span class="text-[10px] text-slate-600 bg-slate-200/80 px-1.5 py-0.5 rounded">Siswa (X-2)</span>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono">NISN: 102407</div>
                    </button>
                    <button 
                        type="button" 
                        @click="fillDemo('19850101')"
                        class="p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 hover:border-slate-300 text-slate-700 font-medium text-left transition-colors cursor-pointer group"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-semibold text-slate-900 group-hover:text-slate-700">Drs. H. Mulyono</span>
                            <span class="text-[10px] text-slate-600 bg-slate-200/80 px-1.5 py-0.5 rounded">Dewan Guru</span>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono">NIP: 19850101</div>
                    </button>
                </div>
            </div>
            --}}
        </div>
    </div>

    <!-- tautan ke login panitia -->
    <div class="text-center mt-6">
        <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-slate-800 transition-colors">
            <span>Petugas atau panitia?</span>
            <span class="font-semibold text-slate-900 hover:underline">Masuk di sini</span>
        </a>
    </div>
</div>
@endsection
