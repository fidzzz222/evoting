<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Event;
use App\Models\EventParticipation;
use App\Models\User;
use App\Models\Vote;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    // Ringkasan metrik statistik sistem untuk admin
    public function stats(Request $request)
    {
        $totalSuperAdmin = User::where('role', 'SUPER_ADMIN')->count();
        $totalPetugas = User::where('role', 'PETUGAS')->count();
        $totalVoters = Voter::count();
        $totalVotersActive = Voter::where('is_active', true)->count();
        $totalVotersSiswa = Voter::where('type', 'SISWA')->count();
        $totalVotersGuru = Voter::where('type', 'GURU')->count();

        $totalEvents = Event::count();
        $eventsDraft = Event::where('status', 'DRAFT')->count();
        $eventsPublished = Event::where('status', 'PUBLISHED')->count();
        $eventsClosed = Event::where('status', 'CLOSED')->count();

        $totalCandidates = Candidate::count();
        $totalVotesCast = Vote::count();
        $totalAuditLogs = AuditLog::count();

        $recentLogs = AuditLog::latest()->take(5)->get();

        $recentEvents = Event::with('creator')
            ->withCount(['candidates', 'votes'])
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'users' => [
                'total_super_admin' => $totalSuperAdmin,
                'total_petugas' => $totalPetugas,
                'total_users' => $totalSuperAdmin + $totalPetugas,
            ],
            'voters' => [
                'total' => $totalVoters,
                'active' => $totalVotersActive,
                'inactive' => $totalVoters - $totalVotersActive,
                'siswa' => $totalVotersSiswa,
                'guru' => $totalVotersGuru,
            ],
            'events' => [
                'total' => $totalEvents,
                'draft' => $eventsDraft,
                'published' => $eventsPublished,
                'closed' => $eventsClosed,
            ],
            'system' => [
                'total_candidates' => $totalCandidates,
                'total_votes_cast' => $totalVotesCast,
                'total_audit_logs' => $totalAuditLogs,
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_time' => Carbon::now()->toIso8601String(),
                'db_driver' => DB::connection()->getDriverName(),
            ],
            'recent_logs' => $recentLogs,
            'recent_events' => $recentEvents,
        ]);
    }

    // Reset total suara dan status partisipasi pemilih pada agenda tertentu
    public function resetEventVotes(Request $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);

        $votesCount = Vote::where('event_id', $event->id)->count();
        $participationsCount = EventParticipation::where('event_id', $event->id)->count();

        DB::transaction(function () use ($event) {
            Vote::where('event_id', $event->id)->delete();
            EventParticipation::where('event_id', $event->id)->delete();
        });

        AuditLog::log('RESET_EVENT_VOTES', "Super Admin me-reset seluruh suara ({$votesCount} suara) pada event '{$event->title}' (Kode: {$event->room_code})", [
            'event_id' => $event->id,
            'votes_deleted' => $votesCount,
            'participations_deleted' => $participationsCount,
        ]);

        return response()->json([
            'message' => "Berhasil me-reset {$votesCount} suara dan {$participationsCount} partisipasi pada event '{$event->title}'.",
            'data' => [
                'event_id' => $event->id,
                'deleted_votes' => $votesCount,
                'deleted_participations' => $participationsCount,
            ],
        ]);
    }

    // Bersihkan cache aplikasi, konfigurasi, dan rute
    public function clearCache(Request $request)
    {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');

        AuditLog::log('CLEAR_SYSTEM_CACHE', "Super Admin membersihkan cache sistem dan routing.");

        return response()->json([
            'message' => 'Cache aplikasi, konfigurasi, dan route berhasil dibersihkan.',
        ]);
    }

    // Ekspor seluruh data DPT pemilih ke format berkas CSV
    public function exportVoters(Request $request)
    {
        $voters = Voter::orderBy('id', 'asc')->get();

        $csvData = "identifier,nama,type,is_active,created_at\n";
        foreach ($voters as $v) {
            $csvData .= sprintf(
                "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $v->identifier,
                str_replace('"', '""', $v->nama),
                $v->type,
                $v->is_active ? '1' : '0',
                $v->created_at ? $v->created_at->toIso8601String() : ''
            );
        }

        AuditLog::log('EXPORT_DPT', "Super Admin mengunduh backup master DPT ({$voters->count()} data pemilih)");

        return response($csvData, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="master_dpt_voters_' . date('Ymd_His') . '.csv"',
        ]);
    }
}
