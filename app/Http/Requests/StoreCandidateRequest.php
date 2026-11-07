<?php

namespace App\Http\Requests;

use App\Models\Candidate;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCandidateRequest extends FormRequest
{
    // Cek otorisasi request simpan paslon
    public function authorize(): bool
    {
        return true;
    }

    // Aturan validasi pendaftaran paslon baru dalam agenda pemilihan
    public function rules(): array
    {
        $isApi = $this->is('api/*') || $this->expectsJson();
        $eventId = $this->getEventId();

        return [
            // Nomor urut paslon unik per agenda
            'candidate_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('candidates')->where('event_id', $eventId),
            ],
            'chairman_name' => ['required_without:name', 'nullable', 'string', 'max:255'],
            'vice_chairman_name' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', $isApi ? 'max:65535' : 'max:500'],
            'vision' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'photo' => $isApi
                ? 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:20480'
                : 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    // Format nama tampilan paslon gabungan ketua & wakil
    public function getCandidatePairName(): string
    {
        $chairman = trim((string) $this->input('chairman_name', ''));
        $vice = trim((string) $this->input('vice_chairman_name', ''));

        // Gabungkan nama ketua dan wakil jika keduanya terisi
        if ($chairman !== '') {
            return $vice !== '' ? "{$chairman} & {$vice}" : $chairman;
        }

        $name = trim((string) $this->input('name', ''));
        if ($name !== '') {
            return $name;
        }

        return 'Pasangan Calon';
    }

    // Ambil ID agenda dari parameter route
    public function getEventId(): ?string
    {
        $event = $this->route('event') ?? $this->route('eventId');
        return $event instanceof Event ? $event->id : $event;
    }

    // Pesan kustom kesalahan validasi paslon
    public function messages(): array
    {
        return [
            'candidate_number.required' => 'Nomor urut pasangan calon wajib diisi.',
            'candidate_number.integer' => 'Nomor urut harus berupa angka bulat positif.',
            'candidate_number.min' => 'Nomor urut minimal bernilai 1.',
            'candidate_number.unique' => 'Nomor urut kandidat sudah digunakan dalam event ini.',
            'chairman_name.required' => 'Nama Calon Ketua wajib diisi.',
            'vision.required' => 'Visi pasangan calon wajib diisi.',
            'mission.required' => 'Misi pasangan calon wajib diisi.',
            'photo.image' => 'File pasfoto harus berupa berkas gambar.',
            'photo.mimes' => 'Format pasfoto yang didukung adalah JPEG, PNG, JPG, atau WebP.',
            'photo.max' => 'Ukuran berkas pasfoto maksimal 2MB.',
        ];
    }
}
