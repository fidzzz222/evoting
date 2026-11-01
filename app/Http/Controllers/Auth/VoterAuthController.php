<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\EventParticipation;
use App\Models\Voter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoterAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.voter-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'identifier' => ['required', 'string'],
            'room_code' => ['required', 'string', 'max:50'],
        ], [
            'identifier.required' => 'Nomor Identitas (NISN / NIP) wajib diisi.',
            'room_code.required' => 'Kode bilik suara / token pemilihan wajib diisi.',
        ]);

        $identifier = trim($credentials['identifier']);
        $roomCode = strtoupper(trim($request->input('room_code')));

        // 1. Verify Event exists and check its status
        $event = Event::where('room_code', $roomCode)->first();

        if (!$event) {
            return back()->withErrors([
                'room_code' => "Kode bilik suara '{$roomCode}' tidak ditemukan.",
            ])->withInput();
        }

        // Sinkronisasi status otomatis berdasarkan waktu saat ini (WIB)
        $event->syncDynamicStatus();

        if ($event->status === 'DRAFT') {
            if ($event->isBeforeStartTime()) {
                return back()->withErrors([
                    'room_code' => "Bilik suara belum dibuka. Jadwal mulai: {$event->formatted_start_time}.",
                ])->withInput();
            }

            return back()->withErrors([
                'room_code' => "Bilik suara belum dibuka oleh panitia.",
            ])->withInput();
        }

        if ($event->status === 'CLOSED' || $event->isAfterEndTime()) {
            return back()->withErrors([
                'room_code' => "Bilik suara telah ditutup ({$event->formatted_end_time}).",
            ])->withInput();
        }

        if ($event->isBeforeStartTime()) {
            return back()->withErrors([
                'room_code' => "Bilik suara belum dimulai. Jadwal mulai: {$event->formatted_start_time}.",
            ])->withInput();
        }

        // 2. Verify Voter in DPT
        $voter = Voter::where('identifier', $identifier)->first();

        if (!$voter) {
            return back()->withErrors([
                'identifier' => 'NISN / NIP tidak terdaftar dalam DPT.',
            ])->withInput();
        }

        if (!$voter->is_active) {
            return back()->withErrors([
                'identifier' => 'Akun pemilih nonaktif. Hubungi panitia.',
            ])->withInput();
        }

        // 3. Verify Voter Eligibility according to Event Target Scope
        if (!$event->isVoterEligible($voter)) {
            $scopeMessage = $event->getEligibilityMessage($voter);

            AuditLog::log('LOGIN_DITOLAK_SCOPE', "Pemilih {$voter->nama} ({$voter->identifier}) ditolak masuk bilik suara '{$event->title}' (#{$event->room_code}): tidak memenuhi target hak pilih ({$event->target_scope_label})", [
                'voter_id' => $voter->id,
                'event_id' => $event->id,
                'room_code' => $event->room_code,
                'kelas' => $voter->kelas,
                'target_scope' => $event->target_scope,
                'target_class' => $event->target_class,
            ], $voter);

            return back()->withErrors([
                'identifier' => $scopeMessage,
            ])->withInput();
        }

        // Authenticate directly via Voter instance (no password/token needed)
        Auth::guard('voter')->login($voter, $request->boolean('remember'));

        $request->session()->regenerate();
        session(['voter_room_code' => $event->room_code]);

        AuditLog::log('LOGIN_PEMILIH', "Pemilih {$voter->nama} ({$voter->identifier} - {$voter->type}) masuk ke bilik suara '{$event->title}' (#{$event->room_code})", [
            'voter_id' => $voter->id,
            'event_id' => $event->id,
            'room_code' => $event->room_code,
            'kelas' => $voter->kelas,
        ], $voter);

        // 3. Check if voter has already voted in this event
        $hasVoted = EventParticipation::where('event_id', $event->id)
            ->where('voter_id', $voter->id)
            ->where('has_voted', true)
            ->exists();

        if ($hasVoted) {
            return redirect()->route('voter.voted', $event->room_code);
        }

        // 4. Direct entry to the voting booth! No agenda catalog, no second room code prompt!
        return redirect()->route('voter.booth', $event->room_code)
            ->with('success', "Bilik suara aktif. Silakan gunakan hak pilih.");
    }

    public function logout(Request $request)
    {
        $voter = Auth::guard('voter')->user();
        if ($voter) {
            AuditLog::log('LOGOUT_PEMILIH', "Pemilih {$voter->nama} ({$voter->identifier}) selesai / keluar bilik suara", null, $voter);
        }

        Auth::guard('voter')->logout();
        $request->session()->forget('voter_room_code');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('voter.login')->with('success', 'Sesi bilik suara selesai.');
    }
}
