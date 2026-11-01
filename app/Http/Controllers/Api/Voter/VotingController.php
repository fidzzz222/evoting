<?php

namespace App\Http\Controllers\Api\Voter;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Event;
use App\Models\EventParticipation;
use App\Models\Vote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VotingController extends Controller
{
    public function join(Request $request)
    {
        $validated = $request->validate([
            'room_code' => 'required|string',
        ]);

        $event = Event::where('room_code', strtoupper($validated['room_code']))->first();

        if (! $event) {
            return response()->json([
                'message' => 'Kode room tidak valid',
            ], 404);
        }

        $now = Carbon::now();

        // Auto-update status jika waktu selesai sudah lewat atau waktu mulai tiba
        $event->syncDynamicStatus();

        // Cek status event
        if ($event->status === 'DRAFT') {
            if ($event->candidates()->count() < 2) {
                return response()->json([
                    'message' => 'Event belum dapat dibuka karena belum memiliki minimal 2 paslon terdaftar.',
                ], 422);
            }

            return response()->json([
                'message' => 'Event belum dipublikasikan',
            ], 422);
        }

        if ($event->status === 'CLOSED') {
            return response()->json([
                'message' => 'Event sudah ditutup atau selesai',
            ], 422);
        }

        // Cek waktu mulai - event harus sudah dimulai
        if ($event->start_time && $now->lt($event->start_time)) {
            return response()->json([
                'message' => 'Event belum dimulai. Silakan tunggu hingga waktu mulai.',
            ], 422);
        }

        // Cek waktu selesai - event tidak boleh sudah selesai
        if ($event->end_time && $now->gt($event->end_time)) {
            return response()->json([
                'message' => 'Event sudah selesai',
            ], 422);
        }

        $voter = $request->user();

        if (! $event->isVoterEligible($voter)) {
            return response()->json([
                'message' => $event->getEligibilityMessage($voter),
            ], 403);
        }

        $participation = EventParticipation::firstOrCreate(
            [
                'event_id' => $event->id,
                'voter_id' => $voter->id,
            ],
            [
                'has_voted' => false,
            ]
        );

        if ($participation->has_voted) {
            return response()->json([
                'message' => 'Anda sudah pernah memberikan suara pada event ini',
                'has_voted' => true,
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'category' => $event->category,
                    'status' => $event->status,
                ],
            ], 200);
        }

        $candidates = Candidate::where('event_id', $event->id)
            ->orderBy('candidate_number', 'asc')
            ->get();

        return response()->json([
            'message' => 'Berhasil masuk ke bilik suara',
            'has_voted' => false,
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'category' => $event->category,
                'description' => $event->description,
                'room_code' => $event->room_code,
                'candidates' => $candidates,
            ],
        ]);
    }

    public function vote(Request $request, string $eventId)
    {
        $validated = $request->validate([
            'candidate_id' => 'required|integer',
        ]);

        $voter = $request->user();

        // Cek event dan status
        $event = Event::findOrFail($eventId);

        if (! $event->isVoterEligible($voter)) {
            return response()->json([
                'message' => $event->getEligibilityMessage($voter),
            ], 403);
        }

        $now = Carbon::now();

        // Auto-update status jika waktu selesai sudah lewat atau waktu mulai tiba
        $event->syncDynamicStatus();

        // Validasi status event
        if ($event->status !== 'PUBLISHED') {
            return response()->json([
                'message' => 'Event tidak dapat diakses atau sudah ditutup',
            ], 422);
        }

        // Cek waktu mulai
        if ($event->start_time && $now->lt($event->start_time)) {
            return response()->json([
                'message' => 'Event belum dimulai',
            ], 422);
        }

        // Cek waktu selesai
        if ($event->end_time && $now->gt($event->end_time)) {
            return response()->json([
                'message' => 'Event sudah selesai',
            ], 422);
        }

        try {
            DB::transaction(function () use ($eventId, $voter, $validated) {
                // 1. Lock participation record
                $participation = EventParticipation::where('event_id', $eventId)
                    ->where('voter_id', $voter->id)
                    ->lockForUpdate()
                    ->first();

                if (! $participation) {
                    $participation = EventParticipation::create([
                        'event_id' => $eventId,
                        'voter_id' => $voter->id,
                        'has_voted' => false,
                    ]);
                    // Re-lock
                    $participation = EventParticipation::where('id', $participation->id)->lockForUpdate()->first();
                }

                // 2. Check double vote
                if ($participation->has_voted) {
                    throw new \Exception('Anda sudah pernah memilih pada event ini!');
                }

                // 3. Verify candidate belongs to event
                $candidateExists = Candidate::where('id', $validated['candidate_id'])
                    ->where('event_id', $eventId)
                    ->exists();

                if (! $candidateExists) {
                    throw new \Exception('Kandidat tidak valid untuk event ini.');
                }

                $now = Carbon::now();

                // 4. Insert vote (ANONYMOUS: NO voter_id)
                Vote::create([
                    'event_id' => $eventId,
                    'candidate_id' => $validated['candidate_id'],
                    'voted_at' => $now,
                ]);

                // 5. Update participation record
                $participation->has_voted = true;
                $participation->voted_at = $now;
                $participation->save();
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Pencoblosan suara berhasil',
        ], 201);
    }

    public function results(Request $request, string $eventId)
    {
        $voter = $request->user();
        $event = Event::findOrFail($eventId);

        $participation = EventParticipation::where('event_id', $eventId)
            ->where('voter_id', $voter->id)
            ->first();

        $hasVoted = $participation ? $participation->has_voted : false;

        if (! $hasVoted && $event->status !== 'CLOSED') {
            return response()->json([
                'message' => 'Anda harus memilih terlebih dahulu untuk melihat hasil',
            ], 403);
        }

        $candidates = Candidate::where('event_id', $eventId)
            ->orderBy('candidate_number', 'asc')
            ->get();

        $totalVotes = Vote::where('event_id', $eventId)->count();

        $results = $candidates->map(function ($c) use ($eventId, $totalVotes) {
            $votesCount = Vote::where('event_id', $eventId)
                ->where('candidate_id', $c->id)
                ->count();

            $percentage = $totalVotes > 0 ? round(($votesCount / $totalVotes) * 100, 2) : 0;

            return [
                'candidate_id' => $c->id,
                'candidate_number' => $c->candidate_number,
                'name' => $c->name,
                'photo_url' => $c->photo_url,
                'votes_count' => $votesCount,
                'percentage' => $percentage,
            ];
        });

        return response()->json([
            'event_id' => $event->id,
            'title' => $event->title,
            'total_votes' => $totalVotes,
            'results' => $results,
        ]);
    }

    public function show(Request $request, string $eventId)
    {
        $voter = $request->user();
        $event = Event::findOrFail($eventId);

        $candidates = Candidate::where('event_id', $event->id)
            ->orderBy('candidate_number', 'asc')
            ->get();

        $participation = EventParticipation::where('event_id', $event->id)
            ->where('voter_id', $voter->id)
            ->first();

        return response()->json([
            'has_voted' => $participation ? $participation->has_voted : false,
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'category' => $event->category,
                'description' => $event->description,
                'room_code' => $event->room_code,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'candidates' => $candidates,
            ],
        ]);
    }
}
