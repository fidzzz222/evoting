<?php

namespace App\Actions;

use App\Models\Candidate;
use App\Models\Event;
use App\Models\EventParticipation;
use App\Models\Vote;
use App\Models\Voter;
use Illuminate\Support\Facades\DB;

class CastVoteAction
{
    /**
     * Execute cast vote transaction with pessimistic locking.
     *
     * @throws \RuntimeException
     */
    public function execute(Event $event, Voter $voter, int $candidateId): Vote
    {
        return DB::transaction(function () use ($event, $voter, $candidateId) {
            // 1. Pessimistic lock on voter participation to prevent double-voting race condition
            $participation = EventParticipation::where('event_id', $event->id)
                ->where('voter_id', $voter->id)
                ->lockForUpdate()
                ->first();

            if (! $participation) {
                $participation = EventParticipation::create([
                    'event_id' => $event->id,
                    'voter_id' => $voter->id,
                    'has_voted' => false,
                ]);
                $participation = EventParticipation::where('id', $participation->id)
                    ->lockForUpdate()
                    ->first();
            }

            if ($participation && $participation->has_voted) {
                throw new \RuntimeException('Anda sudah pernah memilih pada event ini!');
            }

            // 2. Pessimistic lock on candidate belonging to this event
            $candidate = Candidate::where('event_id', $event->id)
                ->where('id', $candidateId)
                ->lockForUpdate()
                ->first();

            if (! $candidate) {
                throw new \RuntimeException('Kandidat tidak valid untuk event ini.');
            }

            $now = now();

            // 3. Store completely ANONYMOUS ballot (no voter_id attached)
            $vote = Vote::create([
                'event_id' => $event->id,
                'candidate_id' => $candidate->id,
                'voted_at' => $now,
            ]);

            // 4. Update voter participation record
            $participation->update([
                'has_voted' => true,
                'voted_at' => $now,
            ]);

            return $vote;
        });
    }
}
