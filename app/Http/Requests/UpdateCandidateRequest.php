<?php

namespace App\Http\Requests;

use App\Models\Candidate;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCandidateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isApi = $this->is('api/*') || $this->expectsJson();
        $eventId = $this->getEventId();
        $candidateId = $this->getCandidateId();

        return [
            'candidate_number' => [
                $isApi ? 'sometimes' : 'required',
                'integer',
                'min:1',
                Rule::unique('candidates')->where('event_id', $eventId)->ignore($candidateId),
            ],
            'chairman_name' => ['nullable', 'string', 'max:255'],
            'vice_chairman_name' => ['nullable', 'string', 'max:255'],
            'name' => [$isApi ? 'sometimes' : 'nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', $isApi ? 'max:65535' : 'max:500'],
            'vision' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'photo' => $isApi
                ? 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:20480'
                : 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    /**
     * Resolve candidate pair display name.
     */
    public function getCandidatePairName(?Candidate $existing = null): string
    {
        $chairman = trim((string) $this->input('chairman_name', ''));
        $vice = trim((string) $this->input('vice_chairman_name', ''));

        if ($chairman !== '') {
            return $vice !== '' ? "{$chairman} & {$vice}" : $chairman;
        }

        $name = trim((string) $this->input('name', ''));
        if ($name !== '') {
            return $name;
        }

        return $existing?->name ?? 'Pasangan Calon';
    }

    /**
     * Get route event ID.
     */
    public function getEventId(): ?string
    {
        $event = $this->route('event') ?? $this->route('eventId');
        if ($event instanceof Event) {
            return $event->id;
        }
        if ($event) {
            return (string) $event;
        }

        $candidateId = $this->getCandidateId();
        if ($candidateId) {
            $candidate = $this->route('candidate');
            if ($candidate instanceof Candidate) {
                return $candidate->event_id;
            }
            return Candidate::where('id', $candidateId)->value('event_id');
        }

        return null;
    }

    /**
     * Get route candidate ID.
     */
    public function getCandidateId(): ?string
    {
        $candidate = $this->route('candidate') ?? $this->route('candidateId') ?? $this->route('id');
        return $candidate instanceof Candidate ? $candidate->id : ($candidate ? (string) $candidate : null);
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
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
