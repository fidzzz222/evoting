@extends('layouts.guest', ['title' => 'Login Petugas & Panitia'])

@section('content')
<div class="max-w-lg w-full mx-auto px-4 py-6 sm:py-10" style="max-width: 520px;" x-data="{ showPass: false, fillDemo(email, pass) { $refs.email.value = email; $refs.password.value = pass; } }">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- header login panitia -->
        <div class="bg-slate-900 px-6 py-6 sm:px-8 sm:py-7 text-white text-center border-b border-slate-800">
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Masuk Panitia & Admin</h2>
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

        <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-6">
            @csrf

            <!-- input email -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 mb-2">
                    Email
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="email"
                    x-ref="email"
                    value="{{ old('email') }}" 
                    required 
                    autofocus
                    placeholder="nama@evoting.sch.id"
                    class="w-full px-4 py-3.5 rounded-xl border {{ $errors->has('email') ? 'border-rose-400 ring-2 ring-rose-200' : 'border-slate-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900' }} text-slate-900 placeholder-slate-400 focus:outline-none text-sm font-medium transition-colors"
                />
            </div>

            <!-- input password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-2">
                    Kata Sandi
                </label>
                <div class="relative">
                    <input 
                        :type="showPass ? 'text' : 'password'" 
                        name="password" 
                        id="password"
                        x-ref="password"
                        required
                        placeholder="••••••••"
                        class="w-full pl-4 pr-11 py-3.5 rounded-xl border {{ $errors->has('password') ? 'border-rose-400 ring-2 ring-rose-200' : 'border-slate-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900' }} text-slate-900 placeholder-slate-400 focus:outline-none text-sm font-medium transition-colors"
                    />
                    <button 
                        type="button" 
                        @click="showPass = !showPass"
                        tabindex="-1"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer"
                    >
                        <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg x-show="showPass" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                            <line x1="2" x2="22" y1="2" y2="22" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- ingat saya -->
            <div class="flex items-center justify-between text-xs pt-0.5">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-slate-900 focus:ring-slate-900 border-slate-300">
                    <span class="font-medium text-slate-600">Ingat saya</span>
                </label>
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
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        Akun Demo Panitia
                    </span>
                    <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">
                        Klik untuk Mengisi
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    <button 
                        type="button" 
                        @click="fillDemo('admin@evoting.sch.id', 'password123')"
                        class="p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 hover:border-slate-300 text-slate-700 font-medium text-left transition-colors cursor-pointer group"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-slate-900 group-hover:text-slate-700">Super Admin</span>
                            <span class="text-[9px] font-semibold bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded">Admin</span>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono truncate">admin@evoting.sch.id</div>
                        <div class="text-[10px] text-slate-400 mt-0.5 font-mono">Sandi: password123</div>
                    </button>
                    <button 
                        type="button" 
                        @click="fillDemo('petugas@evoting.sch.id', 'password123')"
                        class="p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 hover:border-slate-300 text-slate-700 font-medium text-left transition-colors cursor-pointer group"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-slate-900 group-hover:text-slate-700">Panitia OSIS</span>
                            <span class="text-[9px] font-semibold bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded">Petugas</span>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono truncate">petugas@evoting.sch.id</div>
                        <div class="text-[10px] text-slate-400 mt-0.5 font-mono">Sandi: password123</div>
                    </button>
                </div>
            </div>
            --}}
        </div>
    </div>

    <!-- tautan ke login pemilih -->
    <div class="text-center mt-6">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-slate-800 transition-colors">
            <span>Pemilih (siswa atau guru)?</span>
            <span class="font-semibold text-slate-900 hover:underline">Masuk di sini</span>
        </a>
    </div>
</div>
@endsection
