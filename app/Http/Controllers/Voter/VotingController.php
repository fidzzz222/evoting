<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Event;
use App\Models\EventParticipation;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VotingController extends Controller
{
    /**
     * Voter portal entry point: redirects directly to the active voting booth.
     * No intermediate agenda catalog or duplicate room prompt.
     */
    public function index()
    {
        $voter = Auth::guard('voter')->user();
        $roomCode = session('voter_room_code');

        if ($roomCode && ($event = Event::where('room_code', $roomCode)->where('status', 'PUBLISHED')->first())) {
            return redirect()->route('voter.booth', $event->room_code);
        }

        $activeEvent = Event::where('status', 'PUBLISHED')->latest()->first();
        if ($activeEvent) {
            return redirect()->route('voter.booth', $activeEvent->room_code);
        }

        return redirect()->route('voter.login')->with('warning', 'Belum ada agenda bilik suara yang aktif saat ini.');
    }

    /**
     * Join room by code or redirect.
     */
    public function joinRoom(Request $request)
    {
        $request->validate([
            'room_code' => ['required', 'string', 'max:20'],
        ]);

        $code = strtoupper(trim($request->room_code));
        $event = Event::where('room_code', $code)->first();

        if (!$event) {
            return back()->with('error', "Kode bilik suara '{$code}' tidak ditemukan. Mohon periksa kembali.");
        }

        if ($event->status !== 'PUBLISHED') {
            return back()->with('warning', "Bilik suara '{$event->title}' saat ini berstatus " . $event->status_label . ". Pemilihan hanya dapat diakses saat status Berlangsung (Aktif).");
        }

        // If voter is already logged in, go straight to booth
        if (Auth::guard('voter')->check()) {
            return redirect()->route('voter.booth', $event->room_code);
        }

        // Otherwise go to voter login with prefilled room code
        return redirect()->route('voter.login', ['room_code' => $event->room_code]);
    }

    /**
     * Show voting booth for an event.
     */
    public function showBooth(string $roomCode)
    {
        $voter = Auth::guard('voter')->user();
        $event = Event::where('room_code', strtoupper($roomCode))->firstOrFail();

        // Check if event is active
        if ($event->status !== 'PUBLISHED') {
            return redirect()->route('voter.login')->with('error', "Pemilihan '{$event->title}' saat ini tidak aktif (Status: {$event->status_label}).");
        }

        // Check if voter is eligible for this event's scope
        if (!$event->isVoterEligible($voter)) {
            $scopeMessage = $event->getEligibilityMessage($voter);
            return redirect()->route('voter.login')->with('error', $scopeMessage);
        }

        // Check if voter has already voted
        $participation = EventParticipation::where('event_id', $event->id)
            ->where('voter_id', $voter->id)
            ->first();

        if ($participation) {
            return redirect()->route('voter.voted', $event->room_code);
        }

        $candidates = $event->candidates()->orderBy('candidate_number', 'asc')->get();

        return view('voter.booth', compact('event', 'candidates', 'voter'));
    }

    /**
     * Cast vote with pessimistic locking and transaction safety.
     */
    public function submitVote(Request $request, string $roomCode)
    {
        $request->validate([
            'candidate_id' => ['required', 'exists:candidates,id'],
        ]);

        $voter = Auth::guard('voter')->user();
        $event = Event::where('room_code', strtoupper($roomCode))->firstOrFail();

        if ($event->status !== 'PUBLISHED') {
            return redirect()->route('voter.login')->with('error', 'Pemilihan telah ditutup atau belum aktif.');
        }

        if (!$event->isVoterEligible($voter)) {
            return redirect()->route('voter.login')->with('error', $event->getEligibilityMessage($voter));
        }

        try {
            DB::transaction(function () use ($event, $voter, $request) {
                // 1. Pessimistic Lock on voter participation to prevent double-voting race condition
                $alreadyVoted = EventParticipation::where('event_id', $event->id)
                    ->where('voter_id', $voter->id)
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyVoted) {
                    throw new \RuntimeException('Anda sudah menggunakan hak suara pada pemilihan ini.');
                }

                // 2. Pessimistic Lock on candidate
                $candidate = Candidate::where('event_id', $event->id)
                    ->where('id', $request->candidate_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 3. Record participation (Audit DPT who has voted)
                EventParticipation::create([
                    'event_id' => $event->id,
                    'voter_id' => $voter->id,
                    'has_voted' => true,
                    'voted_at' => now(),
                ]);

                // 4. Store completely ANONYMOUS ballot (no voter_id attached)
                Vote::create([
                    'event_id' => $event->id,
                    'candidate_id' => $candidate->id,
                    'voted_at' => now(),
                ]);
            });

            return redirect()->route('voter.success', $event->room_code);

        } catch (\RuntimeException $e) {
            return redirect()->route('voter.voted', $event->room_code)->with('warning', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Terjadi kesalahan sistem saat menyimpan suara Anda. Silakan coba kembali.');
        }
    }

    /**
     * Voting success screen.
     */
    public function success(string $roomCode)
    {
        $event = Event::where('room_code', strtoupper($roomCode))->firstOrFail();
        $voter = Auth::guard('voter')->user();

        return view('voter.success', compact('event', 'voter'));
    }

    /**
     * Screen shown when voter already voted in this event.
     */
    public function alreadyVoted(string $roomCode)
    {
        $event = Event::where('room_code', strtoupper($roomCode))->firstOrFail();
        $voter = Auth::guard('voter')->user();

        $participation = EventParticipation::where('event_id', $event->id)
            ->where('voter_id', $voter->id)
            ->first();

        return view('voter.already-voted', compact('event', 'voter', 'participation'));
    }
}
