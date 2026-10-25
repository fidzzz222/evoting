@extends('layouts.guest', ['title' => 'Portal Pemilihan Suara Elektronik'])

@section('content')
<div class="max-w-6xl w-full mx-auto px-4 py-10 sm:py-16 space-y-16">
    <!-- Hero Section -->
    <div class="text-center max-w-3xl mx-auto space-y-5">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-50 border border-blue-200/80 text-blue-700 text-xs font-bold uppercase tracking-wider shadow-2xs">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Demokrasi Digital Sekolah &bull; LUBER JURDIL
        </div>

        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-[1.15]">
            Suara Anda, <br class="hidden sm:inline">
            <span class="text-blue-600">Masa Depan Sekolah</span> Kita.
        </h1>
        
        <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-2xl mx-auto">
            Platform pemungutan suara elektronik modern, transparan, dan akuntabel. Dirancang fleksibel untuk 
            <span class="font-semibold text-slate-800">Siswa, Guru, dan Seluruh Warga Sekolah</span> dengan jaminan kerahasiaan dan integritas suara mutlak.
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 pt-3">
            <a href="{{ route('voter.login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-bold text-sm shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 9V5a2 2 0 012-2h6a2 2 0 012 2v4" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 5.5l1.5 1.5 3-3" />
                    <rect x="3" y="9" width="18" height="12" rx="2.5" stroke-width="1.8" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14h6" />
                </svg>
                Masuk Bilik Suara (Pemilih)
            </a>

            <a href="{{ route('admin.login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl bg-white hover:bg-slate-50 active:scale-[0.98] text-slate-700 font-bold text-sm border border-slate-200 shadow-2xs transition-all">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Portal Panitia & Admin
            </a>
        </div>
    </div>

    <!-- Quick Room Code Box -->
    <div class="max-w-xl mx-auto bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3.5 mb-4">
            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 tracking-tight">Punya Kode Bilik Suara?</h3>
                <p class="text-xs text-slate-500">Masukkan 6 digit kode bilik yang dibagikan panitia untuk langsung menuju surat suara</p>
            </div>
        </div>

        <form action="{{ route('voter.room.join') }}" method="POST" class="flex flex-col sm:flex-row gap-2.5">
            @csrf
            <div class="relative flex-1">
                <input 
                    type="text" 
                    name="room_code" 
                    maxlength="6" 
                    placeholder="CONTOH: OSIS26" 
                    required
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 text-center uppercase tracking-widest font-mono text-base font-bold text-slate-800 placeholder-slate-300 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-600 transition-all"
                />
            </div>
            <button type="submit" class="px-5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 active:scale-[0.98] text-white font-bold text-xs tracking-wide shadow-xs transition-all shrink-0 cursor-pointer">
                Buka Bilik &rarr;
            </button>
        </form>
    </div>

    <!-- Multi-Role Flexibility Section -->
    <div class="space-y-6">
        <div class="text-center max-w-xl mx-auto">
            <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Sistem Pemilihan Terbuka</span>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Fleksibel untuk Semua Warga Sekolah</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Didesain khusus untuk mengakomodasi berbagai skenario pemilihan di lingkungan sekolah</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Siswa Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs hover:shadow-sm transition-all">
                <div class="w-10 h-10 rounded-xl bg-slate-100/90 border border-slate-200/80 text-slate-600 flex items-center justify-center font-bold mb-3.5 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    </svg>
                </div>
                <h4 class="text-base font-bold text-slate-900">Siswa & Peserta Didik</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Menggunakan nomor identitas NISN/NIS untuk pemilihan Ketua OSIS, MPK, Pramuka, dan ketua ekstrakurikuler.
                </p>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center text-[11px] font-medium text-slate-600">
                    Otentikasi: NISN + Token Rahasia
                </div>
            </div>

            <!-- Guru Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs hover:shadow-sm transition-all">
                <div class="w-10 h-10 rounded-xl bg-slate-100/90 border border-slate-200/80 text-slate-600 flex items-center justify-center font-bold mb-3.5 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h4 class="text-base font-bold text-slate-900">Guru & Tenaga Pendidik</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Menggunakan NIP/NIK untuk pemilihan pengurus paguyuban guru, dewan kehormatan, dan komite perwakilan guru.
                </p>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center text-[11px] font-medium text-slate-600">
                    Otentikasi: NIP/NIK + Token Khusus
                </div>
            </div>

            <!-- Panitia Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs hover:shadow-sm transition-all">
                <div class="w-10 h-10 rounded-xl bg-slate-100/90 border border-slate-200/80 text-slate-600 flex items-center justify-center font-bold mb-3.5 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h4 class="text-base font-bold text-slate-900">Panitia & Pengawas</h4>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Akses kontrol penuh manajemen agenda, paslon, DPT massal, live quick count, dan ekspor Berita Acara resmi.
                </p>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center text-[11px] font-medium text-slate-600">
                    Akses: Super Admin & Petugas
                </div>
            </div>
        </div>
    </div>

    <!-- Active Events Showcase -->
    @if(isset($activeEvents) && $activeEvents->count() > 0)
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Agenda Pemilihan Aktif</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pemilihan yang sedang berlangsung saat ini di sekolah</p>
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ $activeEvents->count() }} Sedang Berlangsung
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($activeEvents as $event)
                    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:border-slate-300 transition-colors flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    {{ $event->category_label }}
                                </span>
                                <span class="font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200/80">
                                    #{{ $event->room_code }}
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors mb-2">
                                {{ $event->title }}
                            </h3>
                            <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed mb-4">
                                {{ $event->description ?? 'Pemilihan resmi diselenggarakan oleh panitia pemilihan sekolah.' }}
                            </p>
                        </div>

                        <div class="pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                            <span class="flex items-center gap-1.5 font-medium">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                {{ $event->candidates()->count() }} Paslon
                            </span>

                            <a href="{{ route('voter.login', ['room_code' => $event->room_code]) }}" class="font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                Masuk Bilik &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 3 Core Principles Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="w-10 h-10 rounded-xl bg-slate-100/90 border border-slate-200/80 text-slate-600 flex items-center justify-center font-bold mb-3.5 shadow-2xs">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h4 class="text-base font-bold text-slate-900 mb-1">Kerahasiaan Mutlak</h4>
            <p class="text-xs text-slate-500 leading-relaxed">
                Suara Anda dienkripsi tanpa relasi identitas pemilih pada tabel suara untuk menjamin asas bebas dan rahasia.
            </p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="w-10 h-10 rounded-xl bg-slate-100/90 border border-slate-200/80 text-slate-600 flex items-center justify-center font-bold mb-3.5 shadow-2xs">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h4 class="text-base font-bold text-slate-900 mb-1">Proteksi Anti-Ganda</h4>
            <p class="text-xs text-slate-500 leading-relaxed">
                Dilengkapi pessimistic lock database transaksi sehingga setiap DPT hanya memiliki hak 1 kali voting valid.
            </p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="w-10 h-10 rounded-xl bg-slate-100/90 border border-slate-200/80 text-slate-600 flex items-center justify-center font-bold mb-3.5 shadow-2xs">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <h4 class="text-base font-bold text-slate-900 mb-1">Audit & Rekapitulasi Realtime</h4>
            <p class="text-xs text-slate-500 leading-relaxed">
                Hasil penghitungan suara terpantau secara transparan melalui grafik interaktif serta Berita Acara siap cetak.
            </p>
        </div>
    </div>
</div>
@endsection
