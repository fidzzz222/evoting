@extends('layouts.app', ['title' => 'Edit Agenda: ' . $event->title])

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="{
    scope: '{{ old('target_scope', $event->target_scope ?? 'ALL') }}',
    targetClass: '{{ old('target_class', $event->target_class ?? '') }}',
    setClass(cls) {
        if (!this.targetClass) {
            this.targetClass = cls;
        } else {
            const arr = this.targetClass.split(',').map(s => s.trim()).filter(Boolean);
            if (!arr.includes(cls)) {
                arr.push(cls);
                this.targetClass = arr.join(', ');
            }
        }
    }
}">
    <!-- Breadcrumb / Back button -->
    <a href="{{ route('events.show', $event) }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
        Detail Agenda
    </a>

    <x-card title="Edit Agenda Pemilihan">
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Terdapat kesalahan pada isian form:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-rose-700 pl-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('events.update', $event) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Judul Pemilihan <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    value="{{ old('title', $event->title) }}"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-colors font-medium"
                />
            </div>

            <!-- Target Scope Selection (Flexible for OSIS, Classes, Clubs) -->
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 space-y-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-800 mb-1">
                        Target Pemilih <span class="text-rose-500">*</span>
                    </label>
                    <p class="text-xs text-slate-500">
                        Pilih cakupan pemilih yang berhak memberikan suara.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <!-- ALL -->
                    <label
                        class="relative flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition-all"
                        :class="scope === 'ALL' ? 'bg-slate-50 border-slate-900 ring-1 ring-slate-900/10' : 'bg-white border-slate-200 hover:border-slate-300'"
                    >
                        <input type="radio" name="target_scope" value="ALL" x-model="scope" class="mt-0.5 text-slate-900 focus:ring-slate-900 focus:ring-offset-0">
                        <div>
                            <span class="block text-xs font-bold text-slate-900">Semua Warga Sekolah</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">Siswa dan dewan guru</span>
                        </div>
                    </label>

                    <!-- SISWA -->
                    <label
                        class="relative flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition-all"
                        :class="scope === 'SISWA' ? 'bg-slate-50 border-slate-900 ring-1 ring-slate-900/10' : 'bg-white border-slate-200 hover:border-slate-300'"
                    >
                        <input type="radio" name="target_scope" value="SISWA" x-model="scope" class="mt-0.5 text-slate-900 focus:ring-slate-900 focus:ring-offset-0">
                        <div>
                            <span class="block text-xs font-bold text-slate-900">Seluruh Siswa</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">Semua siswa terdaftar</span>
                        </div>
                    </label>

                    <!-- KELAS -->
                    <label
                        class="relative flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition-all"
                        :class="scope === 'KELAS' ? 'bg-slate-50 border-slate-900 ring-1 ring-slate-900/10' : 'bg-white border-slate-200 hover:border-slate-300'"
                    >
                        <input type="radio" name="target_scope" value="KELAS" x-model="scope" class="mt-0.5 text-slate-900 focus:ring-slate-900 focus:ring-offset-0">
                        <div>
                            <span class="block text-xs font-bold text-slate-900">Kelas Tertentu</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">Satu atau beberapa kelas</span>
                        </div>
                    </label>

                    <!-- GURU -->
                    <label
                        class="relative flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition-all"
                        :class="scope === 'GURU' ? 'bg-slate-50 border-slate-900 ring-1 ring-slate-900/10' : 'bg-white border-slate-200 hover:border-slate-300'"
                    >
                        <input type="radio" name="target_scope" value="GURU" x-model="scope" class="mt-0.5 text-slate-900 focus:ring-slate-900 focus:ring-offset-0">
                        <div>
                            <span class="block text-xs font-bold text-slate-900">Dewan Guru</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">Khusus guru dan staf</span>
                        </div>
                    </label>
                </div>

                <!-- Input Kelas ketika KELAS dipilih -->
                <div x-show="scope === 'KELAS'" x-cloak class="pt-2 space-y-2">
                    <label for="target_class" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Nama Kelas Target <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="target_class"
                        id="target_class"
                        x-model="targetClass"
                        placeholder="Contoh: XII-RPL-1 atau X-1, X-2"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-colors bg-white font-medium"
                    />

                    @if(!empty($availableClasses) && $availableClasses->count() > 0)
                        <div class="pt-1">
                            <span class="text-[11px] text-slate-500 font-semibold block mb-1.5">Pilih kelas:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($availableClasses as $cls)
                                    <button
                                        type="button"
                                        @click="setClass('{{ $cls }}')"
                                        class="px-2.5 py-1 text-xs font-medium rounded-lg bg-white border border-slate-200 hover:border-slate-800 hover:text-slate-900 hover:bg-slate-50 transition-colors cursor-pointer"
                                    >
                                        + {{ $cls }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Category & Room Code Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="category" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Kategori <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="category"
                        id="category"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-colors bg-white font-medium"
                    >
                        <option value="OSIS" {{ old('category', $event->category) === 'OSIS' ? 'selected' : '' }}>OSIS</option>
                        <option value="MPK" {{ old('category', $event->category) === 'MPK' ? 'selected' : '' }}>MPK</option>
                        <option value="EKSTRAKURIKULER" {{ old('category', $event->category) === 'EKSTRAKURIKULER' ? 'selected' : '' }}>Ekstrakurikuler</option>
                        <option value="KOMITE" {{ old('category', $event->category) === 'KOMITE' ? 'selected' : '' }}>Dewan Guru</option>
                        <option value="LAINNYA" {{ old('category', $event->category) === 'LAINNYA' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                <div>
                    <label for="room_code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Kode Bilik <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="room_code"
                        id="room_code"
                        value="{{ old('room_code', $event->room_code) }}"
                        required
                        maxlength="6"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono font-bold uppercase tracking-widest text-center focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-colors"
                    />
                </div>
            </div>

            <!-- Start & End Time (Mandatory & Automated Schedule) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Jadwal Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="datetime-local"
                        name="start_time"
                        id="start_time"
                        x-ref="startTime"
                        value="{{ old('start_time', $event->start_time ? $event->start_time->format('Y-m-d\TH:i') : '') }}"
                        required
                        @change="if ($refs.startTime.value) $refs.endTime.min = $refs.startTime.value"
                        class="w-full px-4 py-2.5 rounded-xl border @error('start_time') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-colors font-medium"
                    />
                    @error('start_time')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Jadwal Selesai <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="datetime-local"
                        name="end_time"
                        id="end_time"
                        x-ref="endTime"
                        value="{{ old('end_time', $event->end_time ? $event->end_time->format('Y-m-d\TH:i') : '') }}"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border @error('end_time') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-colors font-medium"
                    />
                    @error('end_time')
                        <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Deskripsi <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <textarea
                    name="description"
                    id="description"
                    rows="4"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-colors"
                >{{ old('description', $event->description) }}</textarea>
            </div>

            <!-- Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a
                    href="{{ route('events.show', $event) }}"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs sm:text-sm border border-slate-900 shadow-xs transition-colors cursor-pointer"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
