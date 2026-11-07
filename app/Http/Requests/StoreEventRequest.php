<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    // Cek otorisasi request pembuatan agenda
    public function authorize(): bool
    {
        return true;
    }

    // Aturan validasi pembuatan agenda baru (form web dan REST API)
    public function rules(): array
    {
        $isApi = $this->is('api/*') || $this->expectsJson();

        // Validasi fleksibel untuk endpoint REST API
        if ($isApi) {
            return [
                'title' => 'required|string|max:255',
                'category' => 'nullable|string|max:100',
                'target_scope' => 'nullable|in:ALL,SISWA,GURU,KELAS',
                'target_class' => 'nullable|string|max:255|required_if:target_scope,KELAS',
                'description' => 'nullable|string',
                'room_code' => 'nullable|string|unique:events,room_code',
                'start_time' => 'nullable|date',
                'end_time' => 'nullable|date|after_or_equal:start_time',
            ];
        }

        // Validasi ketat untuk form web panel admin
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'target_scope' => ['required', 'in:ALL,SISWA,GURU,KELAS'],
            'target_class' => ['nullable', 'string', 'max:255', 'required_if:target_scope,KELAS'],
            'description' => ['nullable', 'string'],
            'room_code' => ['required', 'string', 'size:6', 'alpha_num', 'unique:events,room_code'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ];
    }

    // Pesan kustom kesalahan validasi form
    public function messages(): array
    {
        return [
            'title.required' => 'Judul agenda pemilihan wajib diisi.',
            'category.required' => 'Kategori pemilihan wajib dipilih.',
            'target_scope.required' => 'Target hak pilih wajib ditentukan.',
            'target_class.required_if' => 'Nama kelas target wajib diisi jika jangkauan pemilihan adalah spesifik kelas.',
            'start_time.required' => 'Jadwal mulai pemilihan wajib diisi.',
            'start_time.date' => 'Format jadwal mulai tidak valid.',
            'end_time.required' => 'Jadwal selesai pemilihan wajib diisi.',
            'end_time.date' => 'Format jadwal selesai tidak valid.',
            'end_time.after' => 'Jadwal selesai harus setelah jadwal mulai.',
            'end_time.after_or_equal' => 'Waktu selesai harus setelah atau sama dengan waktu mulai.',
            'room_code.required' => 'Kode bilik suara wajib diisi.',
            'room_code.size' => 'Kode bilik suara harus tepat 6 karakter.',
            'room_code.unique' => 'Kode ruangan telah digunakan oleh agenda lain.',
        ];
    }
}
