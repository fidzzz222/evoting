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
    // form login pemilih
    public function showLoginForm()
    {
        return view('auth.voter-login');
    }

    // proses login dan validasi masuk bilik suara
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

        // pastikan agenda ada dan cek statusnya
        $event = Event::where('room_code', $roomCode)->first();

        if (!$event) {
            return back()->withErrors([
                'room_code' => "Kode bilik suara '{$roomCode}' tidak ditemukan. Mohon periksa kembali kode pemilihan.",
            ])->withInput();
        }

        // update status agenda sesuai jadwal sekarang
        $event->syncDynamicStatus();

        if ($event->status === 'DRAFT') {
            if ($event->isBeforeStartTime()) {
                return back()->withErrors([
                    'room_code' => "Bilik suara '{$event->title}' belum dibuka. Pemilihan dijadwalkan mulai pada {$event->formatted_start_time}.",
                ])->withInput();
            }

            return back()->withErrors([
                'room_code' => "Bilik suara '{$event->title}' belum dibuka oleh panitia (Status: Draft).",
            ])->withInput();
        }

        if ($event->status === 'CLOSED' || $event->isAfterEndTime()) {
            return back()->withErrors([
                'room_code' => "Bilik suara '{$event->title}' telah resmi ditutup karena batas waktu jadwal pemilihan ({$event->formatted_end_time}) telah berakhir.",
            ])->withInput();
        }

        if ($event->isBeforeStartTime()) {
            return back()->withErrors([
                'room_code' => "Bilik suara '{$event->title}' belum dimulai. Pemilihan dijadwalkan mulai pada {$event->formatted_start_time}.",
            ])->withInput();
        }

        // cek pemilih di dpt
        $voter = Voter::where('identifier', $identifier)->first();

        if (!$voter) {
            return back()->withErrors([
                'identifier' => 'NISN / NIP tidak terdaftar dalam DPT.',
            ])->withInput();
        }

        if (!$voter->is_active) {
            return back()->withErrors([
                'identifier' => 'Akun DPT Anda berstatus Nonaktif. Hubungi panitia pemilihan.',
            ])->withInput();
        }

        // cek apakah pemilih punya hak suara di agenda ini
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

        // login sesi pemilih
        Auth::guard('voter')->login($voter, $request->boolean('remember'));

        $request->session()->regenerate();
        session(['voter_room_code' => $event->room_code]);

        AuditLog::log('LOGIN_PEMILIH', "Pemilih {$voter->nama} ({$voter->identifier} - {$voter->type}) masuk ke bilik suara '{$event->title}' (#{$event->room_code})", [
            'voter_id' => $voter->id,
            'event_id' => $event->id,
            'room_code' => $event->room_code,
            'kelas' => $voter->kelas,
        ], $voter);

        // cek apakah sudah pernah mencoblos
        $hasVoted = EventParticipation::where('event_id', $event->id)
            ->where('voter_id', $voter->id)
            ->where('has_voted', true)
            ->exists();

        if ($hasVoted) {
            return redirect()->route('voter.voted', $event->room_code);
        }

        // langsung arahkan ke bilik suara
        return redirect()->route('voter.booth', $event->room_code)
            ->with('success', "Selamat datang di Bilik Suara, {$voter->nama}!");
    }

    // logout pemilih dari bilik suara
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

        return redirect()->route('voter.login')->with('success', 'Terima kasih telah menggunakan hak suara Anda.');
    }
}
