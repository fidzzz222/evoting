<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CandidateController extends Controller
{
    /**
     * Display a listing of candidates for an event.
     */
    public function index(Event $event)
    {
        $this->authorizeEventAccess($event);

        $candidates = $event->candidates()
            ->withCount('votes')
            ->orderBy('candidate_number', 'asc')
            ->get();

        return view('candidates.index', compact('event', 'candidates'));
    }

    /**
     * Show the form for creating a new candidate.
     */
    public function create(Event $event)
    {
        $this->authorizeEventAccess($event);

        // Calculate next candidate number automatically
        $nextNumber = ($event->candidates()->max('candidate_number') ?? 0) + 1;
        $existingCandidates = $event->candidates()->orderBy('candidate_number', 'asc')->get();

        return view('candidates.create', compact('event', 'nextNumber', 'existingCandidates'));
    }

    /**
     * Store a newly created candidate in storage.
     */
    public function store(Request $request, Event $event)
    {
        $this->authorizeEventAccess($event);

        $request->validate([
            'candidate_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('candidates', 'candidate_number')->where('event_id', $event->id),
            ],
            'chairman_name' => ['required_without:name', 'nullable', 'string', 'max:255'],
            'vice_chairman_name' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'vision' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'candidate_number.unique' => "Nomor urut {$request->candidate_number} sudah digunakan oleh pasangan calon lain.",
            'chairman_name.required_without' => 'Nama calon ketua wajib diisi.',
        ]);

        // Construct candidate pair name from chairman & vice chairman
        $name = $request->input('name');
        if ($request->filled('chairman_name')) {
            $chairman = trim($request->input('chairman_name'));
            $viceChairman = trim((string) $request->input('vice_chairman_name'));
            $name = $viceChairman !== '' ? "{$chairman} & {$viceChairman}" : $chairman;
        }

        if (empty($name)) {
            return back()->withInput()->withErrors(['chairman_name' => 'Nama calon ketua wajib diisi.']);
        }

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('candidates', 'public');
            $photoUrl = 'storage/' . $path;
        }

        $candidate = $event->candidates()->create([
            'candidate_number' => $request->candidate_number,
            'name' => $name,
            'description' => $request->description,
            'vision' => $request->vision,
            'mission' => $request->mission,
            'photo_url' => $photoUrl,
        ]);

        AuditLog::log(
            'CANDIDATE_CREATED',
            "Menambahkan Paslon #{$candidate->candidate_number}: {$candidate->name} pada '{$event->title}'",
            ['event_id' => $event->id, 'candidate_id' => $candidate->id]
        );

        $totalCandidates = $event->candidates()->count();

        // Cek sinkronisasi status event (jika waktu jadwal aktif dan paslon >= 2, otomatis aktif)
        $previousStatus = $event->status;
        $event->syncDynamicStatus();
        $event->refresh();

        // Alur bertahap & wajib: jika paslon masih kurang dari 2 (syarat wajib belum terpenuhi),
        // atau jika user secara eksplisit memilih simpan & tambah paslon berikutnya
        if ($request->input('action') === 'add_another' || $totalCandidates < 2) {
            $nextNumber = ($event->candidates()->max('candidate_number') ?? 0) + 1;
            return redirect()->route('events.candidates.create', $event)
                ->with('success', "Pasangan calon #{$candidate->candidate_number} ({$candidate->name}) berhasil disimpan. Silakan lanjutkan mendaftarkan Pasangan Calon #{$nextNumber} (wajib minimal 2 paslon).");
        }

        $statusMsg = "Syarat minimal 2 paslon telah terpenuhi!";
        if ($previousStatus === 'DRAFT' && $event->status === 'PUBLISHED') {
            $statusMsg .= " Agenda pemilihan telah otomatis dibuka dan aktif sesuai jadwal yang sedang berjalan.";
        }

        return redirect()->route('events.candidates.index', $event)
            ->with('success', "Pasangan calon #{$candidate->candidate_number} ({$candidate->name}) berhasil didaftarkan. {$statusMsg}");
    }

    /**
     * Show the form for editing the specified candidate.
     */
    public function edit(Event $event, Candidate $candidate)
    {
        $this->authorizeEventAccess($event);

        if ($candidate->event_id !== $event->id) {
            abort(404);
        }

        return view('candidates.edit', compact('event', 'candidate'));
    }

    /**
     * Update the specified candidate in storage.
     */
    public function update(Request $request, Event $event, Candidate $candidate)
    {
        $this->authorizeEventAccess($event);

        if ($candidate->event_id !== $event->id) {
            abort(404);
        }

        $request->validate([
            'candidate_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('candidates', 'candidate_number')
                    ->where('event_id', $event->id)
                    ->ignore($candidate->id),
            ],
            'chairman_name' => ['required_without:name', 'nullable', 'string', 'max:255'],
            'vice_chairman_name' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'vision' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'candidate_number.unique' => "Nomor urut {$request->candidate_number} sudah digunakan oleh pasangan calon lain.",
            'chairman_name.required_without' => 'Nama calon ketua wajib diisi.',
        ]);

        // Construct candidate pair name from chairman & vice chairman
        $name = $request->input('name');
        if ($request->filled('chairman_name')) {
            $chairman = trim($request->input('chairman_name'));
            $viceChairman = trim((string) $request->input('vice_chairman_name'));
            $name = $viceChairman !== '' ? "{$chairman} & {$viceChairman}" : $chairman;
        }

        if (empty($name)) {
            return back()->withInput()->withErrors(['chairman_name' => 'Nama calon ketua wajib diisi.']);
        }

        $photoUrl = $candidate->photo_url;
        if ($request->hasFile('photo')) {
            // Delete old photo if exists in storage
            if ($photoUrl && str_starts_with($photoUrl, 'storage/')) {
                $oldPath = str_replace('storage/', '', $photoUrl);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('photo')->store('candidates', 'public');
            $photoUrl = 'storage/' . $path;
        }

        $candidate->update([
            'candidate_number' => $request->candidate_number,
            'name' => $name,
            'description' => $request->description ?? $candidate->description,
            'vision' => $request->vision,
            'mission' => $request->mission,
            'photo_url' => $photoUrl,
        ]);

        AuditLog::log(
            'CANDIDATE_UPDATED',
            "Memperbarui Paslon #{$candidate->candidate_number}: {$candidate->name} pada '{$event->title}'",
            ['event_id' => $event->id, 'candidate_id' => $candidate->id]
        );

        return redirect()->route('events.candidates.index', $event)
            ->with('success', "Informasi Paslon #{$candidate->candidate_number} berhasil diperbarui.");
    }

    /**
     * Remove the specified candidate from storage.
     */
    public function destroy(Event $event, Candidate $candidate)
    {
        $this->authorizeEventAccess($event);

        if ($candidate->event_id !== $event->id) {
            abort(404);
        }

        if ($candidate->votes()->count() > 0 || $event->votes()->count() > 0) {
            return back()->with('error', 'Kandidat tidak dapat dihapus karena pemilihan telah menerima suara sah.');
        }

        if ($candidate->photo_url && str_starts_with($candidate->photo_url, 'storage/')) {
            $oldPath = str_replace('storage/', '', $candidate->photo_url);
            Storage::disk('public')->delete($oldPath);
        }

        $num = $candidate->candidate_number;
        $name = $candidate->name;
        $candidate->delete();

        AuditLog::log(
            'CANDIDATE_DELETED',
            "Menghapus Paslon #{$num}: {$name} dari '{$event->title}'",
            ['event_id' => $event->id]
        );

        $previousStatus = $event->status;
        $event->syncDynamicStatus();
        $event->refresh();

        $msg = "Pasangan calon #{$num} berhasil dihapus.";
        if ($previousStatus === 'PUBLISHED' && $event->status === 'DRAFT') {
            $msg .= " Status agenda dikembalikan ke DRAFT karena jumlah paslon kurang dari 2 (syarat minimal).";
        }

        return back()->with('success', $msg);
    }

    /**
     * Authorize user access to this event.
     */
    protected function authorizeEventAccess(Event $event): void
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return;
        }

        $isOfficer = $event->officers()->where('users.id', $user->id)->exists();
        if ($event->created_by_user_id !== $user->id && !$isOfficer) {
            abort(403, 'Akses ditolak.');
        }
    }
}
