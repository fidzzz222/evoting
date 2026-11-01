<?php

namespace App\Http\Controllers\Voter;

use App\Actions\CastVoteAction;
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

        if ($roomCode && ($event = Event::where('room_code', $roomCode)->first())) {
            if ($event->isOngoing()) {
                return redirect()->route('voter.booth', $event->room_code);
            }
        }

        Event::syncAllExpiredEvents();
        $activeEvent = Event::where('status', 'PUBLISHED')->latest()->get()->first(function ($e) {
            return $e->isOngoing();
        });

        if ($activeEvent) {
            return redirect()->route('voter.booth', $activeEvent->room_code);
        }

        $events = Event::withCount('candidates')->latest()->get();
        $participations = EventParticipation::where('voter_id', $voter->id)
            ->where('has_voted', true)
            ->get()
            ->keyBy('event_id');

        return view('voter.index', compact('voter', 'events', 'participations'));
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
            return back()->with('error', "Kode bilik suara '{$code}' tidak ditemukan.");
        }

        $event->syncDynamicStatus();

        if ($event->status === 'CLOSED' || $event->isAfterEndTime()) {
            return back()->with('error', "Bilik suara telah ditutup ({$event->formatted_end_time}).");
        }

        if ($event->isBeforeStartTime()) {
            return back()->with('warning', "Bilik suara belum dibuka. Jadwal mulai: {$event->formatted_start_time}.");
        }

        if ($event->status !== 'PUBLISHED') {
            return back()->with('warning', "Bilik suara tidak aktif (Status: {$event->status_label}).");
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

        $event->syncDynamicStatus();

        // Check if event is active and within schedule window
        if ($event->status === 'CLOSED' || $event->isAfterEndTime()) {
            return redirect()->route('voter.login')->with('error', "Bilik suara telah ditutup ({$event->formatted_end_time}).");
        }

        if ($event->isBeforeStartTime()) {
            return redirect()->route('voter.login')->with('error', "Bilik suara belum dibuka. Jadwal mulai: {$event->formatted_start_time}.");
        }

        if ($event->status !== 'PUBLISHED') {
            return redirect()->route('voter.login')->with('error', "Pemilihan tidak aktif (Status: {$event->status_label}).");
        }

        // Check if voter is eligible for this event's scope
        if (!$event->isVoterEligible($voter)) {
            $scopeMessage = $event->getEligibilityMessage($voter);
            return redirect()->route('voter.login')->with('error', $scopeMessage);
        }

        // Check if voter has already voted
        $participation = EventParticipation::where('event_id', $event->id)
            ->where('voter_id', $voter->id)
            ->where('has_voted', true)
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
    public function submitVote(Request $request, string $roomCode, CastVoteAction $castVoteAction)
    {
        $request->validate([
            'candidate_id' => ['required', 'exists:candidates,id'],
        ]);

        $voter = Auth::guard('voter')->user();
        $event = Event::where('room_code', strtoupper($roomCode))->firstOrFail();

        $event->syncDynamicStatus();

        if ($event->status === 'CLOSED' || $event->isAfterEndTime()) {
            return redirect()->route('voter.login')->with('error', "Batas waktu pemilihan telah berakhir ({$event->formatted_end_time}).");
        }

        if ($event->isBeforeStartTime()) {
            return redirect()->route('voter.login')->with('error', "Bilik suara belum dibuka. Jadwal mulai: {$event->formatted_start_time}.");
        }

        if ($event->status !== 'PUBLISHED') {
            return redirect()->route('voter.login')->with('error', 'Pemilihan ditutup atau belum aktif.');
        }

        if (!$event->isVoterEligible($voter)) {
            return redirect()->route('voter.login')->with('error', $event->getEligibilityMessage($voter));
        }

        try {
            $castVoteAction->execute($event, $voter, (int) $request->candidate_id);

            return redirect()->route('voter.success', $event->room_code);

        } catch (\RuntimeException $e) {
            return redirect()->route('voter.voted', $event->room_code)->with('warning', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal menyimpan suara. Silakan coba kembali.');
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
            ->where('has_voted', true)
            ->first();

        return view('voter.already-voted', compact('event', 'voter', 'participation'));
    }
}
