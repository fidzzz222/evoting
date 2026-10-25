<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by_user_id',
        'title',
        'category',
        'target_scope',
        'target_class',
        'description',
        'room_code',
        'invite_token',
        'status',
        'start_time',
        'end_time',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function officers()
    {
        return $this->belongsToMany(User::class, 'event_officers', 'event_id', 'user_id')->withTimestamps();
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'event_id')->orderBy('candidate_number', 'asc');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class, 'event_id');
    }

    public function participations()
    {
        return $this->hasMany(EventParticipation::class, 'event_id');
    }

    /**
     * Memperbarui status event secara otomatis berdasarkan waktu sekarang
     */
    public function syncDynamicStatus(): bool
    {
        $now = Carbon::now();
        $updated = false;

        // Jika DRAFT dan start_time sudah tiba -> PUBLISHED
        if ($this->status === 'DRAFT' && $this->start_time && $now->greaterThanOrEqualTo($this->start_time)) {
            $this->status = 'PUBLISHED';
            $updated = true;
        }

        // Jika PUBLISHED dan end_time sudah lewat -> CLOSED
        if ($this->status === 'PUBLISHED' && $this->end_time && $now->greaterThan($this->end_time)) {
            $this->status = 'CLOSED';
            $updated = true;
        }

        if ($updated) {
            $this->save();
        }

        return $updated;
    }

    public function isOngoing(): bool
    {
        $now = Carbon::now();
        if ($this->status !== 'PUBLISHED') {
            return false;
        }
        if ($this->start_time && $now->lt($this->start_time)) {
            return false;
        }
        if ($this->end_time && $now->gt($this->end_time)) {
            return false;
        }
        return true;
    }

    public function isActive(): bool
    {
        return $this->isOngoing();
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'DRAFT' => 'Draft / Persiapan',
            'PUBLISHED' => 'Berlangsung (Aktif)',
            'CLOSED' => 'Selesai (Ditutup)',
            default => (string) $this->status,
        };
    }

    /**
     * Memeriksa apakah pemilih memenuhi kriteria target hak pilih event
     */
    public function isVoterEligible(?Voter $voter): bool
    {
        if (!$voter || !$voter->is_active) {
            return false;
        }

        $scope = $this->target_scope ?? 'ALL';

        switch ($scope) {
            case 'SISWA':
                return $voter->type === 'SISWA';

            case 'GURU':
                return $voter->type === 'GURU';

            case 'KELAS':
                if (empty($this->target_class)) {
                    return $voter->type === 'SISWA';
                }
                $targetClasses = array_map(function ($item) {
                    return strtolower(trim($item));
                }, explode(',', $this->target_class));

                $voterClass = strtolower(trim($voter->kelas ?? ''));
                return in_array($voterClass, $targetClasses, true);

            case 'ALL':
            default:
                return true;
        }
    }

    /**
     * Pesan penjelasan jika pemilih ditolak karena tidak memenuhi syarat scope
     */
    public function getEligibilityMessage(?Voter $voter): ?string
    {
        if (!$voter) {
            return 'Identitas pemilih tidak valid atau belum terdaftar.';
        }

        if ($this->isVoterEligible($voter)) {
            return null;
        }

        if (!$voter->is_active) {
            return 'Akun DPT Anda berstatus Nonaktif. Hubungi panitia pemilihan.';
        }

        $scope = $this->target_scope ?? 'ALL';

        switch ($scope) {
            case 'SISWA':
                return "Bilik suara '{$this->title}' (#{$this->room_code}) dikhususkan untuk Siswa. Identitas Anda ({$voter->identifier}) terdaftar sebagai Dewan Guru / Tenaga Pendidik.";

            case 'GURU':
                return "Bilik suara '{$this->title}' (#{$this->room_code}) dikhususkan untuk Dewan Guru & Staf. Identitas Anda ({$voter->identifier}) terdaftar sebagai Siswa.";

            case 'KELAS':
                $target = $this->target_class ?: 'kelas tertentu';
                $voterClass = $voter->kelas ?: 'Belum ditentukan';
                return "Bilik suara '{$this->title}' (#{$this->room_code}) dikhususkan untuk {$target}. Identitas Anda terdaftar di kelas {$voterClass}.";

            default:
                return "Anda tidak terdaftar dalam target hak pilih untuk agenda '{$this->title}'.";
        }
    }

    /**
     * Query pemilih yang memenuhi syarat untuk event ini
     */
    public function eligibleVotersQuery()
    {
        $query = Voter::where('is_active', true);
        $scope = $this->target_scope ?? 'ALL';

        switch ($scope) {
            case 'SISWA':
                return $query->where('type', 'SISWA');

            case 'GURU':
                return $query->where('type', 'GURU');

            case 'KELAS':
                if (!empty($this->target_class)) {
                    $targetClasses = array_map('trim', explode(',', $this->target_class));
                    return $query->whereIn('kelas', $targetClasses);
                }
                return $query->where('type', 'SISWA');

            case 'ALL':
            default:
                return $query;
        }
    }

    /**
     * Hitung total DPT yang berhak memilih pada event ini
     */
    public function eligibleVotersCount(): int
    {
        return $this->eligibleVotersQuery()->count();
    }

    public function getTargetScopeLabelAttribute(): string
    {
        return match($this->target_scope) {
            'SISWA' => 'Seluruh Siswa (Semua Tingkat/Kelas)',
            'GURU' => 'Dewan Guru & Tenaga Pendidik',
            'KELAS' => 'Khusus Kelas ' . ($this->target_class ?: 'Belum Dipilih'),
            default => 'Semua Warga Sekolah (Umum / OSIS)',
        };
    }

    public function getTargetScopeShortLabelAttribute(): string
    {
        return match($this->target_scope) {
            'SISWA' => 'Semua Siswa',
            'GURU' => 'Guru & Staf',
            'KELAS' => $this->target_class ?: 'Kelas Khusus',
            default => 'Umum',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match(strtoupper((string) $this->category)) {
            'OSIS', 'KETUA_OSIS' => 'Pemilihan OSIS',
            'MPK', 'KETUA_MPK' => 'Pemilihan MPK',
            'EKSTRAKURIKULER' => 'Ekstrakurikuler',
            'KOMITE' => 'Komite / Guru',
            'LAINNYA' => 'Umum',
            default => ucwords(strtolower(str_replace('_', ' ', (string) $this->category))),
        };
    }
}
