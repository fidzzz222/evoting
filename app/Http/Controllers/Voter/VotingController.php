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
    // arahkan pemilih ke bilik suara aktif
    public function index()
    {
        $voter = Auth::guard('voter')->user();
        $roomCode = session('voter_room_code');

        // cek apakah ada sesi bilik suara yang masih jalan
        if ($roomCode && ($event = Event::where('room_code', $roomCode)->first())) {
            if ($event->isOngoing()) {
                return redirect()->route('voter.booth', $event->room_code);
            }
        }

        // cari agenda lain yang sedang berlangsung
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

    // masuk ke bilik pakai kode akses
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

        // kalau sudah login, langsung ke bilik suara
        if (Auth::guard('voter')->check()) {
            return redirect()->route('voter.booth', $event->room_code);
        }

        // kalau belum login, arahkan ke login pemilih
        return redirect()->route('voter.login', ['room_code' => $event->room_code]);
    }

    // tampilkan bilik suara pemilihan
    public function showBooth(string $roomCode)
    {
        $voter = Auth::guard('voter')->user();
        $event = Event::where('room_code', strtoupper($roomCode))->firstOrFail();

        $event->syncDynamicStatus();

        // cek batas waktu bilik suara
        if ($event->status === 'CLOSED' || $event->isAfterEndTime()) {
            return redirect()->route('voter.login')->with('error', "Bilik suara telah ditutup ({$event->formatted_end_time}).");
        }

        if ($event->isBeforeStartTime()) {
            return redirect()->route('voter.login')->with('error', "Bilik suara belum dibuka. Jadwal mulai: {$event->formatted_start_time}.");
        }

        if ($event->status !== 'PUBLISHED') {
            return redirect()->route('voter.login')->with('error', "Pemilihan tidak aktif (Status: {$event->status_label}).");
        }

        // cek hak pilih pemilih
        if (!$event->isVoterEligible($voter)) {
            $scopeMessage = $event->getEligibilityMessage($voter);
            return redirect()->route('voter.login')->with('error', $scopeMessage);
        }

        // cek apakah pemilih sudah pernah nyoblos
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

    // proses submit surat suara
    public function submitVote(Request $request, string $roomCode, CastVoteAction $castVoteAction)
    {
        $request->validate([
            'candidate_id' => ['required', 'exists:candidates,id'],
        ]);

        $voter = Auth::guard('voter')->user();
        $event = Event::where('room_code', strtoupper($roomCode))->firstOrFail();

        $event->syncDynamicStatus();

        // validasi ulang jadwal dan hak pilih sebelum simpan suara
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
            // simpan suara lewat action
            $castVoteAction->execute($event, $voter, (int) $request->candidate_id);

            return redirect()->route('voter.success', $event->room_code);

        } catch (\RuntimeException $e) {
            // tangani jika terdeteksi sudah memilih di tab lain
            return redirect()->route('voter.voted', $event->room_code)->with('warning', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal menyimpan suara. Silakan coba kembali.');
        }
    }

    // halaman sukses setelah mencoblos
    public function success(string $roomCode)
    {
        $event = Event::where('room_code', strtoupper($roomCode))->firstOrFail();
        $voter = Auth::guard('voter')->user();

        return view('voter.success', compact('event', 'voter'));
    }

    // halaman jika pemilih sudah pernah memilih
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
