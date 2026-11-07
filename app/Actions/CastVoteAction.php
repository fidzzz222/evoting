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
    // proses simpan suara pemilih dengan database transaction
    public function execute(Event $event, Voter $voter, int $candidateId): Vote
    {
        return DB::transaction(function () use ($event, $voter, $candidateId) {
            // lock data partisipasi biar gak bisa double vote
            $participation = EventParticipation::where('event_id', $event->id)
                ->where('voter_id', $voter->id)
                ->lockForUpdate()
                ->first();

            // buat data partisipasi baru jika belum ada
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

            // pastikan belum pernah nyoblos
            if ($participation && $participation->has_voted) {
                throw new \RuntimeException('Anda sudah pernah memilih pada event ini!');
            }

            // pastikan kandidat sesuai dengan event
            $candidate = Candidate::where('event_id', $event->id)
                ->where('id', $candidateId)
                ->lockForUpdate()
                ->first();

            if (! $candidate) {
                throw new \RuntimeException('Kandidat tidak valid untuk event ini.');
            }

            $now = now();

            // simpan surat suara (anonim tanpa id pemilih)
            $vote = Vote::create([
                'event_id' => $event->id,
                'candidate_id' => $candidate->id,
                'voted_at' => $now,
            ]);

            // tandai pemilih sudah menggunakan hak suaranya
            $participation->update([
                'has_voted' => true,
                'voted_at' => $now,
            ]);

            return $vote;
        });
    }
}
