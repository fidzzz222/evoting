<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    private function authorizeEventAccess(Request $request, Event $event): void
    {
        if (! $event->canBeManagedBy($request->user())) {
            abort(403, 'Akses ditolak untuk event ini');
        }
    }

    public function monitor(Request $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);
        $this->authorizeEventAccess($request, $event);
        $event->syncDynamicStatus();

        $stats = $event->getElectionStats();

        $candidateResults = collect($stats['candidates'])->map(function ($c) {
            return [
                'id' => $c['id'],
                'candidate_number' => $c['candidate_number'],
                'name' => $c['name'],
                'photo_url' => $c['photo_url'],
                'description' => $c['description'],
                'vision' => $c['vision'],
                'mission' => $c['mission'],
                'vote_count' => $c['vote_count'],
                'percentage' => $c['percentage'],
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
                'total_voters' => $stats['total_voters'],
                'total_voted' => $stats['total_voted'],
                'turnout_percentage' => $stats['turnout_percentage'],
            ],
            'candidates' => $candidateResults,
        ]);
    }

    public function export(Request $request, string $eventId)
    {
        $event = Event::with('creator')->findOrFail($eventId);
        $this->authorizeEventAccess($request, $event);
        $event->syncDynamicStatus();

        $stats = $event->getElectionStats();

        $winner = null;
        if ($stats['total_voted'] > 0 && $stats['leader']) {
            $winner = [
                'id' => $stats['leader']->id,
                'candidate_number' => $stats['leader']->candidate_number,
                'name' => $stats['leader']->name,
                'vote_count' => (int) ($stats['leader']->votes_count ?? 0),
            ];
        }

        $candidateResults = collect($stats['candidates'])->map(function ($c) {
            return [
                'candidate_number' => $c['candidate_number'],
                'name' => $c['name'],
                'description' => $c['description'],
                'vision' => $c['vision'],
                'mission' => $c['mission'],
                'vote_count' => $c['vote_count'],
                'percentage' => $c['percentage'],
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
                'total_voters' => $stats['total_voters'],
                'total_voted' => $stats['total_voted'],
                'turnout_percentage' => $stats['turnout_percentage'],
            ],
            'candidates' => $candidateResults,
            'winner' => $winner,
        ]);
    }
}
