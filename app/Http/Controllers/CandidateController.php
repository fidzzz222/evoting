<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCandidateRequest;
use App\Http\Requests\UpdateCandidateRequest;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class CandidateController extends Controller
{
    // daftar paslon dalam satu agenda
    public function index(Event $event)
    {
        $this->authorizeEventAccess($event);

        $candidates = $event->candidates()
            ->withCount('votes')
            ->orderBy('candidate_number', 'asc')
            ->get();

        return view('candidates.index', compact('event', 'candidates'));
    }

    // form tambah paslon baru
    public function create(Event $event)
    {
        $this->authorizeEventAccess($event);

        // nomor urut paslon berikutnya
        $nextNumber = ($event->candidates()->max('candidate_number') ?? 0) + 1;
        $existingCandidates = $event->candidates()->orderBy('candidate_number', 'asc')->get();

        return view('candidates.create', compact('event', 'nextNumber', 'existingCandidates'));
    }

    // simpan data paslon baru
    public function store(StoreCandidateRequest $request, Event $event)
    {
        $this->authorizeEventAccess($event);

        $name = $request->getCandidatePairName();

        if (empty($name)) {
            return back()->withInput()->withErrors(['chairman_name' => 'Nama calon ketua wajib diisi.']);
        }

        $candidate = $event->candidates()->create([
            'candidate_number' => $request->candidate_number,
            'name' => $name,
            'description' => $request->description,
            'vision' => $request->vision,
            'mission' => $request->mission,
        ]);

        if ($request->hasFile('photo')) {
            $candidate->savePhoto($request->file('photo'));
        }

        AuditLog::log(
            'CANDIDATE_CREATED',
            "Menambahkan Paslon #{$candidate->candidate_number}: {$candidate->name} pada '{$event->title}'",
            ['event_id' => $event->id, 'candidate_id' => $candidate->id]
        );

        $totalCandidates = $event->candidates()->count();

        // aktifkan agenda otomatis kalau paslon sudah cukup (min 2)
        $previousStatus = $event->status;
        $event->syncDynamicStatus();
        $event->refresh();

        // kalau belum 2 paslon, arahkan tambah paslon berikutnya
        if ($request->input('action') === 'add_another' || $totalCandidates < 2) {
            $nextNumber = ($event->candidates()->max('candidate_number') ?? 0) + 1;
            return redirect()->route('events.candidates.create', $event)
                ->with('success', "Paslon #{$candidate->candidate_number} ({$candidate->name}) disimpan. Lanjutkan pendaftaran Paslon #{$nextNumber}.");
        }

        $statusMsg = '';
        if ($previousStatus === 'DRAFT' && $event->status === 'PUBLISHED') {
            $statusMsg = ' Agenda aktif otomatis sesuai jadwal.';
        }

        return redirect()->route('events.candidates.index', $event)
            ->with('success', "Paslon #{$candidate->candidate_number} ({$candidate->name}) berhasil didaftarkan.{$statusMsg}");
    }

    // form edit paslon
    public function edit(Event $event, Candidate $candidate)
    {
        $this->authorizeEventAccess($event);

        if ($candidate->event_id !== $event->id) {
            abort(404);
        }

        return view('candidates.edit', compact('event', 'candidate'));
    }

    // update data paslon
    public function update(UpdateCandidateRequest $request, Event $event, Candidate $candidate)
    {
        $this->authorizeEventAccess($event);

        if ($candidate->event_id !== $event->id) {
            abort(404);
        }

        $name = $request->getCandidatePairName();

        if (empty($name)) {
            return back()->withInput()->withErrors(['chairman_name' => 'Nama calon ketua wajib diisi.']);
        }

        if ($request->hasFile('photo')) {
            $candidate->savePhoto($request->file('photo'));
        }

        $candidate->update([
            'candidate_number' => $request->candidate_number,
            'name' => $name,
            'description' => $request->description ?? $candidate->description,
            'vision' => $request->vision,
            'mission' => $request->mission,
        ]);

        AuditLog::log(
            'CANDIDATE_UPDATED',
            "Memperbarui Paslon #{$candidate->candidate_number}: {$candidate->name} pada '{$event->title}'",
            ['event_id' => $event->id, 'candidate_id' => $candidate->id]
        );

        return redirect()->route('events.candidates.index', $event)
            ->with('success', "Data Paslon #{$candidate->candidate_number} berhasil diperbarui.");
    }

    // hapus paslon
    public function destroy(Event $event, Candidate $candidate)
    {
        $this->authorizeEventAccess($event);

        if ($candidate->event_id !== $event->id) {
            abort(404);
        }

        // jangan hapus kalau sudah ada suara masuk
        if ($candidate->votes()->count() > 0 || $event->votes()->count() > 0) {
            return back()->with('error', 'Paslon tidak dapat dihapus karena sudah memiliki suara masuk.');
        }

        $num = $candidate->candidate_number;
        $name = $candidate->name;
        $candidate->delete();

        AuditLog::log(
            'CANDIDATE_DELETED',
            "Menghapus Paslon #{$num}: {$name} dari '{$event->title}'",
            ['event_id' => $event->id]
        );

        // balik ke DRAFT kalau paslon kurang dari 2
        $previousStatus = $event->status;
        $event->syncDynamicStatus();
        $event->refresh();

        $msg = "Paslon #{$num} berhasil dihapus.";
        if ($previousStatus === 'PUBLISHED' && $event->status === 'DRAFT') {
            $msg .= " Status agenda kembali ke DRAFT (minimal 2 paslon).";
        }

        return back()->with('success', $msg);
    }

    // validasi hak akses kelola agenda
    protected function authorizeEventAccess(Event $event): void
    {
        if (!$event->canBeManagedBy(Auth::user())) {
            abort(403, 'Akses ditolak.');
        }
    }
}
