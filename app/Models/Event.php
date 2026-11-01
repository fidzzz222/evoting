<?php

namespace App\Models;

use App\Models\AuditLog;
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
     * Mengecek apakah jadwal mulai pemilihan belum tiba
     */
    public function isBeforeStartTime(): bool
    {
        return $this->start_time && Carbon::now()->lt($this->start_time);
    }

    /**
     * Mengecek apakah jadwal selesai pemilihan sudah berakhir
     */
    public function isAfterEndTime(): bool
    {
        return $this->end_time && Carbon::now()->gte($this->end_time);
    }

    /**
     * Mengecek apakah waktu saat ini berada di dalam rentang jadwal mulai s/d selesai
     */
    public function isInScheduledWindow(): bool
    {
        $now = Carbon::now();
        if ($this->start_time && $now->lt($this->start_time)) {
            return false;
        }
        if ($this->end_time && $now->gte($this->end_time)) {
            return false;
        }
        return true;
    }

    /**
     * Memperbarui status event secara otomatis berdasarkan waktu sekarang (WIB) dan syarat minimal 2 kandidat
     */
    public function syncDynamicStatus(): bool
    {
        $now = Carbon::now();
        $updated = false;

        // 1. Jika event belum CLOSED dan end_time sudah tercapai/terlewati -> otomatis CLOSED
        if ($this->status !== 'CLOSED' && $this->end_time && $now->greaterThanOrEqualTo($this->end_time)) {
            $this->status = 'CLOSED';
            $updated = true;

            AuditLog::log(
                'EVENT_AUTO_CLOSED',
                "Agenda '{$this->title}' (#{$this->room_code}) ditutup otomatis oleh sistem karena jadwal pemilihan telah selesai ({$this->end_time->format('d/m/Y H:i')} WIB).",
                ['event_id' => $this->id, 'end_time' => $this->end_time->toIso8601String()]
            );
        }
        // 2. Jika status PUBLISHED tetapi jumlah pasangan calon kurang dari 2 -> otomatis kembalikan ke DRAFT
        elseif ($this->status === 'PUBLISHED' && $this->candidates()->count() < 2) {
            $this->status = 'DRAFT';
            $updated = true;

            AuditLog::log(
                'EVENT_REVERTED_DRAFT',
                "Agenda '{$this->title}' (#{$this->room_code}) dikembalikan ke status DRAFT oleh sistem karena jumlah pasangan calon kurang dari 2 (syarat minimal belum terpenuhi).",
                ['event_id' => $this->id, 'candidates_count' => $this->candidates()->count()]
            );
        }
        // 3. Jika status DRAFT dan waktu saat ini telah mencapai jadwal mulai (start_time <= now <= end_time)
        // HANYA dapat otomatis aktif (PUBLISHED) jika memiliki minimal 2 pasangan calon terdaftar
        elseif ($this->status === 'DRAFT' && $this->start_time 
            && $now->greaterThanOrEqualTo($this->start_time) 
            && (!$this->end_time || $now->lessThanOrEqualTo($this->end_time))
            && $this->candidates()->count() >= 2) {
            
            $this->status = 'PUBLISHED';
            $updated = true;

            AuditLog::log(
                'EVENT_AUTO_STARTED',
                "Agenda '{$this->title}' (#{$this->room_code}) dimulai otomatis oleh sistem sesuai jadwal ({$this->start_time->format('d/m/Y H:i')} WIB) dengan {$this->candidates()->count()} paslon terdaftar.",
                ['event_id' => $this->id, 'start_time' => $this->start_time->toIso8601String()]
            );
        }

        if ($updated) {
            $this->save();
        }

        return $updated;
    }

    /**
     * Sinkronisasi seluruh agenda pemilihan secara otomatis:
     * 1. Menutup agenda yang telah melewati jadwal selesai (end_time <= now) menjadi CLOSED
     * 2. Mengembalikan agenda PUBLISHED yang belum memiliki minimal 2 kandidat menjadi DRAFT
     * 3. Membuka (publish) agenda DRAFT yang telah memasuki jadwal mulai (start_time <= now <= end_time) dan memiliki minimal 2 kandidat menjadi PUBLISHED
     *
     * @return array{closed: int, reverted: int, started: int, total: int}
     */
    public static function syncAllEventStatuses(): array
    {
        $now = Carbon::now();
        $closedCount = 0;
        $revertedCount = 0;
        $startedCount = 0;

        // 1. Tutup event yang telah melewati jadwal selesai
        $expiredEvents = static::where('status', '!=', 'CLOSED')
            ->whereNotNull('end_time')
            ->where('end_time', '<=', $now)
            ->get();

        foreach ($expiredEvents as $event) {
            $prevStatus = $event->status;
            if ($event->syncDynamicStatus() && $event->status === 'CLOSED' && $prevStatus !== 'CLOSED') {
                $closedCount++;
            }
        }

        // 2. Kembalikan event PUBLISHED yang memiliki kandidat < 2 ke status DRAFT
        $invalidPublishedEvents = static::where('status', 'PUBLISHED')
            ->has('candidates', '<', 2)
            ->get();

        foreach ($invalidPublishedEvents as $event) {
            $prevStatus = $event->status;
            if ($event->syncDynamicStatus() && $event->status === 'DRAFT' && $prevStatus === 'PUBLISHED') {
                $revertedCount++;
            }
        }

        // 3. Buka event berstatus DRAFT yang telah memasuki jadwal mulai DAN memiliki minimal 2 kandidat
        $startingEvents = static::where('status', 'DRAFT')
            ->whereNotNull('start_time')
            ->where('start_time', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('end_time')
                    ->orWhere('end_time', '>', $now);
            })
            ->has('candidates', '>=', 2)
            ->get();

        foreach ($startingEvents as $event) {
            $prevStatus = $event->status;
            if ($event->syncDynamicStatus() && $event->status === 'PUBLISHED' && $prevStatus !== 'PUBLISHED') {
                $startedCount++;
            }
        }

        return [
            'closed' => $closedCount,
            'reverted' => $revertedCount,
            'started' => $startedCount,
            'total' => $closedCount + $revertedCount + $startedCount,
        ];
    }

    /**
     * Sinkronisasi seluruh event aktif/draft yang waktu selesainya telah terlewati menjadi CLOSED.
     * Juga menjalankan sinkronisasi pembukaan agenda yang telah memasuki jadwal mulai.
     */
    public static function syncAllExpiredEvents(): int
    {
        $result = static::syncAllEventStatuses();
        return $result['closed'];
    }

    public function isOngoing(): bool
    {
        $this->syncDynamicStatus();

        if ($this->status !== 'PUBLISHED' || $this->candidates()->count() < 2) {
            return false;
        }

        return $this->isInScheduledWindow();
    }

    public function isActive(): bool
    {
        return $this->isOngoing();
    }

    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_time ? $this->start_time->isoFormat('dddd, D MMMM Y • HH:mm') . ' WIB' : 'Belum diatur';
    }

    public function getFormattedEndTimeAttribute(): string
    {
        return $this->end_time ? $this->end_time->isoFormat('dddd, D MMMM Y • HH:mm') . ' WIB' : 'Belum diatur';
    }

    public function getScheduleStatusLabelAttribute(): string
    {
        $this->syncDynamicStatus();

        if ($this->status === 'CLOSED' || $this->isAfterEndTime()) {
            return 'Ditutup';
        }

        if ($this->status === 'DRAFT') {
            return 'Draft';
        }

        if ($this->isBeforeStartTime()) {
            return 'Menunggu Jadwal Mulai';
        }

        return 'Sedang Berlangsung';
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'CLOSED' || $this->isAfterEndTime()) {
            return 'Ditutup';
        }

        return match($this->status) {
            'DRAFT' => 'Draft',
            'PUBLISHED' => 'Berlangsung (Aktif)',
            'CLOSED' => 'Ditutup',
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
