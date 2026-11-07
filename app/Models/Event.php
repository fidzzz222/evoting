<?php

namespace App\Models;

use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

    // cek apakah pemilihan belum dimulai
    public function isBeforeStartTime(): bool
    {
        return $this->start_time && Carbon::now()->lt($this->start_time);
    }

    // cek apakah jadwal pemilihan sudah lewat
    public function isAfterEndTime(): bool
    {
        return $this->end_time && Carbon::now()->gte($this->end_time);
    }

    // cek apakah waktu sekarang masuk dalam rentang jadwal aktif
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

    // sync status agenda otomatis sesuai jadwal wib dan syarat minimal 2 paslon
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

    // Sinkronisasi status seluruh agenda (tutup yang lewat jadwal, draft-kan jika < 2 paslon, buka jika jadwal tiba)
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

    // Helper sinkronisasi event kedaluwarsa
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

    // Cek apakah pemilih masuk dalam target hak pilih agenda ini
    public function isVoterEligible(?Voter $voter): bool
    {
        // Pemilih nonaktif atau null langsung ditolak
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
                // Jika filter kelas kosong, izinkan seluruh siswa
                if (empty($this->target_class)) {
                    return $voter->type === 'SISWA';
                }
                // Pisahkan daftar kelas target (misal: "X RPL 1, X RPL 2") dan bandingkan case-insensitive
                $targetClasses = array_map(function ($item) {
                    return strtolower(trim($item));
                }, explode(',', $this->target_class));

                $voterClass = strtolower(trim($voter->kelas ?? ''));
                return in_array($voterClass, $targetClasses, true);

            case 'ALL':
            default:
                // Seluruh warga sekolah (guru & siswa) berhak memilih
                return true;
        }
    }

    // Pesan penolakan jika pemilih tidak memenuhi target hak pilih
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

    // Query DPT yang memenuhi syarat untuk agenda ini
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

    // Hitung total DPT yang berhak memilih
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

    // Filter event yang dapat diakses user (super admin akses semua, petugas hanya miliknya/kolaborasi)
    public function scopeAccessibleBy($query, ?User $user = null)
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('created_by_user_id', $user->id)
              ->orWhereHas('officers', function ($officerQuery) use ($user) {
                  $officerQuery->where('users.id', $user->id);
              });
        });
    }

    // Cek apakah user berhak mengelola event ini
    public function canBeManagedBy(?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ((int) $this->created_by_user_id === (int) $user->id) {
            return true;
        }

        return $this->officers()->where('users.id', $user->id)->exists();
    }

    // Hitung statistik lengkap perolehan suara dan partisipasi pemilih
    public function getElectionStats(): array
    {
        $totalDPT = $this->eligibleVotersCount();
        $totalVotes = $this->votes()->count();
        $turnoutRate = $totalDPT > 0 ? round(($totalVotes / $totalDPT) * 100, 1) : 0;
        $turnoutPercentage = $totalDPT > 0 ? round(($totalVotes / $totalDPT) * 100, 2) : 0;
        $nonVoters = max(0, $totalDPT - $totalVotes);

        $this->load(['candidates' => function ($query) {
            $query->withCount(['votes' => function ($vq) {
                $vq->where('event_id', $this->id);
            }])->orderBy('candidate_number', 'asc');
        }]);

        $candidates = $this->candidates;
        $candidateStats = [];
        $leader = null;
        $maxVotes = -1;
        $isTie = false;

        foreach ($candidates as $c) {
            $votesCount = (int) ($c->votes_count ?? 0);
            $percentage = $totalVotes > 0 ? round(($votesCount / $totalVotes) * 100, 1) : 0;

            $candidateStats[] = [
                'id' => $c->id,
                'candidate_id' => $c->id,
                'number' => $c->candidate_number,
                'candidate_number' => $c->candidate_number,
                'chairman' => $c->chairman_name,
                'vice_chairman' => $c->vice_chairman_name,
                'name' => $c->name,
                'display_name' => $c->display_name,
                'photo' => $c->photo ? (str_starts_with($c->photo, 'http') ? $c->photo : asset($c->photo)) : null,
                'photo_url' => $c->photo_url,
                'description' => $c->description,
                'vision' => $c->vision,
                'mission' => $c->mission,
                'votes' => $votesCount,
                'vote_count' => $votesCount,
                'votes_count' => $votesCount,
                'percentage' => $percentage,
            ];

            if ($votesCount > $maxVotes) {
                $maxVotes = $votesCount;
                $leader = $c;
                $isTie = false;
            } elseif ($votesCount === $maxVotes && $maxVotes > 0) {
                $isTie = true;
            }
        }

        $winner = ($this->status === 'CLOSED' && !$isTie && $maxVotes > 0) ? $leader : null;

        return [
            'totalDPT' => $totalDPT,
            'totalVoters' => $totalDPT,
            'total_voters' => $totalDPT,
            'totalVotes' => $totalVotes,
            'totalVoted' => $totalVotes,
            'total_voted' => $totalVotes,
            'turnoutRate' => $turnoutRate,
            'turnoutPercentage' => $turnoutPercentage,
            'turnout_percentage' => $turnoutPercentage,
            'nonVoters' => $nonVoters,
            'candidateStats' => $candidateStats,
            'candidates' => $candidateStats,
            'leader' => $leader,
            'winner' => $winner,
            'isTie' => $isTie,
        ];
    }

    // buat kode bilik unik 6 karakter
    public static function generateUniqueRoomCode(int $length = 6): string
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (static::where('room_code', $code)->exists());

        return $code;
    }

    // buat token invite unik untuk tautan langsung
    public static function generateUniqueInviteToken(int $length = 32): string
    {
        do {
            $token = Str::random($length);
        } while (static::where('invite_token', $token)->exists());

        return $token;
    }
}
