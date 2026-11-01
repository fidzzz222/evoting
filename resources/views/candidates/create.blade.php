@extends('layouts.app', ['title' => 'Tambah Paslon - ' . $event->title])

@php
    $candidateList = isset($existingCandidates) ? $existingCandidates : $event->candidates()->orderBy('candidate_number', 'asc')->get();
    $candidateCount = $candidateList->count();
@endphp

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{
    photoPreview: null,
    previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            this.photoPreview = URL.createObjectURL(file);
        }
    }
}">
    <div>
        <a href="{{ route('events.candidates.index', $event) }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors mb-2">
            Kelola Paslon
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Tambah Pasangan Calon Baru
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Agenda: <strong class="text-slate-700 font-semibold">{{ $event->title }}</strong>
                </p>
            </div>

            <div class="self-start sm:self-auto">
                @if($candidateCount < 2)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0" aria-hidden="true"></span>
                        <span>Wajib minimal 2 paslon</span>
                        <span class="text-slate-500 font-normal">(Terdaftar: <strong class="font-mono font-bold text-slate-900 tabular-nums">{{ $candidateCount }}/2</strong>)</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0" aria-hidden="true"></span>
                        <span>Syarat minimal terpenuhi</span>
                        <span class="text-slate-500 font-normal">(<strong class="font-mono font-bold text-slate-900 tabular-nums">{{ $candidateCount }}</strong> paslon)</span>
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if($candidateCount > 0)
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/90">
            <div class="flex items-center justify-between gap-2 mb-2.5">
                <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                    Paslon Terdaftar ({{ $candidateCount }})
                </span>
                <span class="text-[11px] text-slate-500">
                    @if($candidateCount < 2)
                        <span class="inline-flex items-center gap-1.5 text-slate-600 font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0" aria-hidden="true"></span>
                            <span>Kurang {{ 2 - $candidateCount }} paslon lagi untuk melengkapi syarat wajib</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-slate-600 font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0" aria-hidden="true"></span>
                            <span>Syarat minimal terpenuhi</span>
                        </span>
                    @endif
                </span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($candidateList as $c)
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs text-slate-800 shadow-2xs font-medium">
                        <span class="w-5 h-5 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold font-mono">
                            {{ $c->candidate_number }}
                        </span>
                        <span>{{ $c->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <x-card>
        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <div class="font-bold mb-1.5 flex items-center gap-1.5 text-rose-900">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Terdapat kesalahan pengisian formulir:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-rose-700 pl-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('events.candidates.store', $event) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Nomor Urut -->
            <div class="w-full sm:w-1/3">
                <label for="candidate_number" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                    Nomor Urut Paslon <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="number" 
                    id="candidate_number" 
                    name="candidate_number" 
                    min="1" 
                    max="99" 
                    value="{{ old('candidate_number', ($event->candidates()->max('candidate_number') ?? 0) + 1) }}"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-sm font-bold tabular-nums text-slate-900"
                />
                @error('candidate_number')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nama Ketua & Wakil -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="chairman_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Nama Calon Ketua <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="chairman_name" 
                        name="chairman_name" 
                        placeholder="Contoh: Muhammad Raihan Pratama" 
                        value="{{ old('chairman_name') }}"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-sm font-semibold text-slate-900"
                    />
                    @error('chairman_name')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="vice_chairman_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Nama Calon Wakil <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <input 
                        type="text" 
                        id="vice_chairman_name" 
                        name="vice_chairman_name" 
                        placeholder="Contoh: Siti Aisyah Azzahra" 
                        value="{{ old('vice_chairman_name') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-sm font-semibold text-slate-900"
                    />
                    @error('vice_chairman_name')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Upload Foto & Preview -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Foto Pasangan Calon <span class="text-slate-400 font-normal">(JPG, PNG, WebP max 2MB)</span>
                </label>

                <div class="flex flex-col sm:flex-row items-center gap-6 p-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50/50">
                    <!-- Preview Box -->
                    <div class="w-32 h-40 rounded-xl bg-slate-200 border border-slate-300 overflow-hidden shrink-0 flex items-center justify-center text-slate-400">
                        <template x-if="photoPreview">
                            <img :src="photoPreview" alt="Preview Foto" class="w-full h-full object-cover" />
                        </template>
                        <template x-if="!photoPreview">
                            <div class="text-center p-2">
                                <svg class="w-8 h-8 mx-auto text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-[10px] font-bold">Belum Ada Foto</span>
                            </div>
                        </template>
                    </div>

                    <div class="w-full space-y-2">
                        <input 
                            type="file" 
                            id="photo" 
                            name="photo" 
                            accept="image/*"
                            @change="previewImage($event)"
                            class="block w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800 file:cursor-pointer transition-colors"
                        />
                        <p class="text-[11px] text-slate-500">
                            Format JPG, PNG, atau WebP maksimum 2MB. Disarankan foto rasio 3:4.
                        </p>
                    </div>
                </div>
                @error('photo')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Visi -->
            <div>
                <label for="vision" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Visi Paslon <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <textarea 
                    id="vision" 
                    name="vision" 
                    rows="3" 
                    placeholder="Ringkasan visi pasangan calon..."
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-xs sm:text-sm text-slate-900 leading-relaxed"
                >{{ old('vision') }}</textarea>
                @error('vision')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Misi -->
            <div>
                <label for="mission" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Misi Paslon <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <textarea 
                    id="mission" 
                    name="mission" 
                    rows="6" 
                    placeholder="1. Meningkatkan kedisiplinan dan inovasi siswa&#10;2. Menghadirkan wadah aspirasi yang transparan"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-xs sm:text-sm text-slate-900 leading-relaxed font-mono"
                >{{ old('mission') }}</textarea>
                <p class="text-[11px] text-slate-400 mt-1">Gunakan nomor urut (1, 2, 3) untuk setiap butir misi.</p>
                @error('mission')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button Group -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-slate-100">
                <div>
                    @if($candidateCount >= 2)
                        <a 
                            href="{{ route('events.candidates.index', $event) }}" 
                            class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 transition-colors inline-block"
                        >
                            Selesai &amp; Lihat Daftar Paslon
                        </a>
                    @else
                        <a 
                            href="{{ route('events.show', $event) }}" 
                            class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors inline-block"
                        >
                            Lewati Sementara
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-2.5">
                    @if($candidateCount === 0)
                        <button 
                            type="submit" 
                            name="action"
                            value="save"
                            class="inline-flex items-center px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs sm:text-sm shadow-xs border border-slate-900 transition-colors cursor-pointer"
                        >
                            <span>Lanjut ke Paslon #2</span>
                        </button>
                    @elseif($candidateCount === 1)
                        <button 
                            type="submit" 
                            name="action"
                            value="add_another"
                            class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold text-xs shadow-xs transition-colors cursor-pointer"
                        >
                            Simpan &amp; Tambah Paslon #3
                        </button>
                        <button 
                            type="submit" 
                            name="action"
                            value="save"
                            class="inline-flex items-center px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs sm:text-sm shadow-xs border border-slate-900 transition-colors cursor-pointer"
                        >
                            <span>Simpan &amp; Selesaikan Agenda</span>
                        </button>
                    @else
                        <button 
                            type="submit" 
                            name="action"
                            value="add_another"
                            class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold text-xs shadow-xs transition-colors cursor-pointer"
                        >
                            Simpan &amp; Tambah Lagi
                        </button>
                        <button 
                            type="submit" 
                            name="action"
                            value="save"
                            class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs sm:text-sm shadow-xs border border-slate-900 transition-colors cursor-pointer"
                        >
                            Simpan Paslon
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection
