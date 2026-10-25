<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Event;
use App\Models\EventParticipation;
use App\Models\Vote;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    private function authorizeEventAccess(Request $request, Event $event): void
    {
        $user = $request->user();
        if ($user->role === 'SUPER_ADMIN') {
            return;
        }

        $isCreator = (int) $event->created_by_user_id === (int) $user->id;
        $isOfficer = $event->officers()->where('users.id', $user->id)->exists();

        if (! $isCreator && ! $isOfficer) {
            abort(403, 'Akses ditolak untuk event ini');
        }
    }

    public function monitor(Request $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);
        $this->authorizeEventAccess($request, $event);

        $totalVoters = Voter::where('is_active', true)->count();
        $totalVoted = Vote::where('event_id', $event->id)->count();
        $turnoutPercentage = $totalVoters > 0 ? round(($totalVoted / $totalVoters) * 100, 2) : 0;

        $candidates = Candidate::where('event_id', $event->id)
            ->orderBy('candidate_number', 'asc')
            ->get();

        $candidateResults = $candidates->map(function ($c) use ($event, $totalVoted) {
            $voteCount = Vote::where('event_id', $event->id)
                ->where('candidate_id', $c->id)
                ->count();

            $percentage = $totalVoted > 0 ? round(($voteCount / $totalVoted) * 100, 2) : 0;

            return [
                'id' => $c->id,
                'candidate_number' => $c->candidate_number,
                'name' => $c->name,
                'photo_url' => $c->photo_url,
                'description' => $c->description,
                'vision' => $c->vision,
                'mission' => $c->mission,
                'vote_count' => $voteCount,
                'percentage' => $percentage,
            ];
        });

        return response()->json([
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'room_code' => $event->room_code,
                'status' => $event->status,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
            ],
            'participation' => [
                'total_voters' => $totalVoters,
                'total_voted' => $totalVoted,
                'turnout_percentage' => $turnoutPercentage,
            ],
            'candidates' => $candidateResults,
        ]);
    }

    public function export(Request $request, string $eventId)
    {
        $event = Event::with('creator')->findOrFail($eventId);
        $this->authorizeEventAccess($request, $event);

        $totalVoters = Voter::where('is_active', true)->count();
        $totalVoted = Vote::where('event_id', $event->id)->count();
        $turnoutPercentage = $totalVoters > 0 ? round(($totalVoted / $totalVoters) * 100, 2) : 0;

        $candidates = Candidate::where('event_id', $event->id)
            ->orderBy('candidate_number', 'asc')
            ->get();

        $maxVotes = -1;
        $winner = null;

        $candidateResults = $candidates->map(function ($c) use ($event, $totalVoted, &$maxVotes, &$winner) {
            $voteCount = Vote::where('event_id', $event->id)
                ->where('candidate_id', $c->id)
                ->count();

            $percentage = $totalVoted > 0 ? round(($voteCount / $totalVoted) * 100, 2) : 0;

            if ($voteCount > $maxVotes) {
                $maxVotes = $voteCount;
                $winner = [
                    'id' => $c->id,
                    'candidate_number' => $c->candidate_number,
                    'name' => $c->name,
                    'vote_count' => $voteCount,
                ];
            }

            return [
                'candidate_number' => $c->candidate_number,
                'name' => $c->name,
                'description' => $c->description,
                'vision' => $c->vision,
                'mission' => $c->mission,
                'vote_count' => $voteCount,
                'percentage' => $percentage,
            ];
        });

        return response()->json([
            'report_title' => 'REKAPITULASI HASIL PERHITUNGAN SUARA E-VOTING',
            'exported_at' => Carbon::now()->toIso8601String(),
            'person_in_charge' => [
                'nama' => $event->creator->nama ?? 'System',
                'email' => $event->creator->email ?? '',
            ],
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'room_code' => $event->room_code,
                'status' => $event->status,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
            ],
            'report_meta' => [
                'generated_at' => Carbon::now()->toIso8601String(),
                'generated_by' => $event->creator->nama ?? 'System',
            ],
            'participation' => [
                'total_voters' => $totalVoters,
                'total_voted' => $totalVoted,
                'turnout_percentage' => $turnoutPercentage,
            ],
            'candidates' => $candidateResults,
            'winner' => $totalVoted > 0 && $winner ? array_merge(['id' => $winner['id'] ?? null], $winner) : null,
        ]);
    }
}
