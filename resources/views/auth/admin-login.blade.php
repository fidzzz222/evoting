@extends('layouts.guest', ['title' => 'Login Petugas & Panitia'])

@section('content')
<div class="max-w-md mx-auto px-4 py-8 sm:py-12" x-data="{ showPass: false, fillDemo(email, pass) { $refs.email.value = email; $refs.password.value = pass; } }">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Header Banner Solid Agency-Grade -->
        <div class="bg-slate-900 p-8 text-white text-center border-b border-slate-800">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-3">
                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Panel Administrasi
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-white">Portal Panitia & Admin</h2>
            <p class="text-xs text-slate-400 mt-1.5 max-w-xs mx-auto leading-relaxed">
                Masuk untuk mengelola agenda pemilihan, verifikasi DPT, dan memantau perolehan suara.
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
                        <span class="font-bold text-rose-900 block mb-0.5">Gagal Masuk</span>
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

            <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat Email Resmi
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                            </svg>
                        </span>
                        <input 
                            type="email" 
                            name="email" 
                            id="email"
                            x-ref="email"
                            value="{{ old('email') }}" 
                            required 
                            autofocus
                            placeholder="nama@evoting.sch.id"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border {{ $errors->has('email') ? 'border-rose-400 ring-2 ring-rose-200' : 'border-slate-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900' }} text-slate-900 placeholder-slate-400 focus:outline-none text-sm font-medium transition-colors"
                        />
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kata Sandi Akun
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </span>
                        <input 
                            :type="showPass ? 'text' : 'password'" 
                            name="password" 
                            id="password"
                            x-ref="password"
                            required
                            placeholder="••••••••"
                            class="w-full pl-11 pr-11 py-3 rounded-xl border {{ $errors->has('password') ? 'border-rose-400 ring-2 ring-rose-200' : 'border-slate-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900' }} text-slate-900 placeholder-slate-400 focus:outline-none text-sm font-medium transition-colors"
                        />
                        <button 
                            type="button" 
                            @click="showPass = !showPass"
                            tabindex="-1"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer"
                        >
                            <svg x-show="!showPass" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPass" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-slate-900 focus:ring-slate-900 border-slate-300">
                        <span class="font-medium text-slate-600">Ingat Sesi Saya</span>
                    </label>
                    <span class="text-slate-400 font-medium">Hak Akses Terproteksi</span>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full py-3.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-bold text-sm border border-slate-800 shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-2 group"
                >
                    <span>Masuk ke Dashboard</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <!-- Quick Demo Helper for Presentation (UKOM) -->
            <div class="mt-8 pt-6 border-t border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        Demo Akun Panitia (UKOM)
                    </span>
                    <span class="text-[10px] font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">
                        1-Klik Isi
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    <button 
                        type="button" 
                        @click="fillDemo('admin@evoting.sch.id', 'password123')"
                        class="p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 hover:border-slate-300 text-slate-700 font-medium text-left transition-colors cursor-pointer group"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-slate-900 group-hover:text-blue-600">Super Admin</span>
                            <span class="text-[9px] font-bold bg-slate-200 text-slate-800 px-1.5 py-0.5 rounded">Admin</span>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono truncate">admin@evoting.sch.id</div>
                        <div class="text-[10px] text-slate-400 mt-0.5 font-mono">Pass: password123</div>
                    </button>
                    <button 
                        type="button" 
                        @click="fillDemo('petugas@evoting.sch.id', 'password123')"
                        class="p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 hover:border-slate-300 text-slate-700 font-medium text-left transition-colors cursor-pointer group"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-slate-900 group-hover:text-blue-600">Panitia OSIS</span>
                            <span class="text-[9px] font-bold bg-slate-200 text-slate-800 px-1.5 py-0.5 rounded">Petugas</span>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono truncate">petugas@evoting.sch.id</div>
                        <div class="text-[10px] text-slate-400 mt-0.5 font-mono">Pass: password123</div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Voter Link Switcher Clean Footer -->
    <div class="text-center mt-6">
        <a href="{{ route('voter.login') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
            <span>Anda seorang pemilih (Siswa / Guru)?</span>
            <span class="font-bold text-blue-600 hover:underline">Masuk ke Bilik Suara &rarr;</span>
        </a>
    </div>
</div>
@endsection
