<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Event;
use App\Models\User;
use App\Models\Voter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display a listing of election events.
     */
    public function index(Request $request)
    {
        // Sinkronisasi status seluruh agenda secara otomatis sesuai jadwal mulai & selesai
        Event::syncAllEventStatuses();

        $user = Auth::user();
        $query = Event::with(['creator', 'candidates'])
            ->withCount(['candidates', 'votes', 'participations']);

        if (!$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by_user_id', $user->id)
                  ->orWhereHas('officers', function ($officerQuery) use ($user) {
                      $officerQuery->where('users.id', $user->id);
                  });
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('room_code', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $events = $query->orderByRaw("CASE status WHEN 'PUBLISHED' THEN 1 WHEN 'DRAFT' THEN 2 WHEN 'CLOSED' THEN 3 ELSE 4 END")
            ->orderBy('created_at', 'desc')
            ->paginate(9)
            ->withQueryString();

        return view('events.index', compact('events'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        $suggestedRoomCode = strtoupper(Str::random(6));
        $availableClasses = Voter::where('type', 'SISWA')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->orderBy('kelas')
            ->pluck('kelas');

        return view('events.create', compact('suggestedRoomCode', 'availableClasses'));
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'target_scope' => ['required', 'in:ALL,SISWA,GURU,KELAS'],
            'target_class' => ['nullable', 'string', 'max:255', 'required_if:target_scope,KELAS'],
            'description' => ['nullable', 'string'],
            'room_code' => ['required', 'string', 'size:6', 'alpha_num', 'unique:events,room_code'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ], [
            'start_time.required' => 'Jadwal mulai pemilihan wajib diisi.',
            'start_time.date' => 'Format jadwal mulai tidak valid.',
            'end_time.required' => 'Jadwal selesai pemilihan wajib diisi.',
            'end_time.date' => 'Format jadwal selesai tidak valid.',
            'end_time.after' => 'Jadwal selesai harus setelah jadwal mulai.',
            'target_class.required_if' => 'Nama kelas target wajib diisi jika jangkauan pemilihan adalah spesifik kelas.',
            'room_code.size' => 'Kode bilik suara harus tepat 6 karakter.',
        ]);

        $event = Event::create([
            'created_by_user_id' => Auth::id(),
            'title' => $request->title,
            'category' => $request->category,
            'target_scope' => $request->target_scope ?? 'ALL',
            'target_class' => $request->target_scope === 'KELAS' ? trim($request->target_class) : null,
            'description' => $request->description,
            'room_code' => strtoupper($request->room_code),
            'invite_token' => Str::random(32),
            'status' => 'DRAFT',
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        $event->syncDynamicStatus();

        // Hubungkan seluruh pengguna role SUPER_ADMIN ke dalam kepanitiaan event (event_officers)
        // agar seluruh administrator selalu terdaftar dan memiliki akses penuh ke agenda ini
        $superAdminIds = User::where('role', 'SUPER_ADMIN')
            ->where('id', '!=', Auth::id())
            ->pluck('id');
        if ($superAdminIds->isNotEmpty()) {
            $event->officers()->syncWithoutDetaching($superAdminIds);
        }

        AuditLog::log(
            'EVENT_CREATED',
            "Membuat agenda pemilihan baru: {$event->title} (#{$event->room_code}) [Target: {$event->target_scope_label}]",
            ['event_id' => $event->id, 'title' => $event->title, 'target_scope' => $event->target_scope, 'target_class' => $event->target_class]
        );

        return redirect()->route('events.candidates.create', $event)
            ->with('success', "Agenda pemilihan '{$event->title}' berhasil dibuat. Silakan daftarkan pasangan calon (wajib minimal 2 paslon).");
    }

    /**
     * Display the specified event details and management dashboard.
     */
    public function show(Event $event)
    {
        $this->authorizeEventAccess($event);
        $event->syncDynamicStatus();

        $event->load(['creator', 'officers', 'candidates' => function ($q) {
            $q->orderBy('candidate_number', 'asc');
        }]);

        $totalDPT = $event->eligibleVotersCount();
        $totalVotes = $event->votes()->count();
        $turnoutRate = $totalDPT > 0 ? round(($totalVotes / $totalDPT) * 100, 1) : 0;

        return view('events.show', compact('event', 'totalDPT', 'totalVotes', 'turnoutRate'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event)
    {
        $this->authorizeEventAccess($event);
        $event->syncDynamicStatus();
        $availableClasses = Voter::where('type', 'SISWA')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->orderBy('kelas')
            ->pluck('kelas');

        return view('events.edit', compact('event', 'availableClasses'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, Event $event)
    {
        $this->authorizeEventAccess($event);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'target_scope' => ['required', 'in:ALL,SISWA,GURU,KELAS'],
            'target_class' => ['nullable', 'string', 'max:255', 'required_if:target_scope,KELAS'],
            'description' => ['nullable', 'string'],
            'room_code' => ['required', 'string', 'size:6', 'alpha_num', 'unique:events,room_code,' . $event->id],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ], [
            'start_time.required' => 'Jadwal mulai pemilihan wajib diisi.',
            'start_time.date' => 'Format jadwal mulai tidak valid.',
            'end_time.required' => 'Jadwal selesai pemilihan wajib diisi.',
            'end_time.date' => 'Format jadwal selesai tidak valid.',
            'end_time.after' => 'Jadwal selesai harus setelah jadwal mulai.',
            'target_class.required_if' => 'Nama kelas target wajib diisi jika jangkauan pemilihan adalah spesifik kelas.',
            'room_code.size' => 'Kode bilik suara harus tepat 6 karakter.',
        ]);

        $event->update([
            'title' => $request->title,
            'category' => $request->category,
            'target_scope' => $request->target_scope ?? 'ALL',
            'target_class' => $request->target_scope === 'KELAS' ? trim($request->target_class) : null,
            'description' => $request->description,
            'room_code' => strtoupper($request->room_code),
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        $event->syncDynamicStatus();

        AuditLog::log(
            'EVENT_UPDATED',
            "Memperbarui informasi agenda: {$event->title} [Target: {$event->target_scope_label}]",
            ['event_id' => $event->id, 'target_scope' => $event->target_scope, 'target_class' => $event->target_class]
        );

        return redirect()->route('events.show', $event)
            ->with('success', 'Informasi agenda pemilihan berhasil diperbarui.');
    }

    /**
     * Update the election status (SCHEDULED, ACTIVE, PAUSED, CLOSED).
     */
    public function updateStatus(Request $request, Event $event)
    {
        $this->authorizeEventAccess($event);

        $request->validate([
            'status' => ['required', 'in:DRAFT,PUBLISHED,CLOSED'],
        ]);

        $oldStatus = $event->status;
        $newStatus = $request->status;

        // If activating, make sure there are at least 2 candidates
        if ($newStatus === 'PUBLISHED') {
            if ($event->candidates()->count() < 2) {
                return back()->with('error', 'Pemilihan membutuhkan minimal 2 pasangan calon sebelum dapat diaktifkan.');
            }

            if ($event->isAfterEndTime()) {
                return back()->with('error', "Pemilihan tidak dapat diaktifkan karena batas jadwal selesai ({$event->formatted_end_time}) telah terlewati. Silakan edit jadwal selesai terlebih dahulu.");
            }
        }

        $event->update(['status' => $newStatus]);
        $event->syncDynamicStatus();

        AuditLog::log(
            'EVENT_STATUS_CHANGED',
            "Mengubah status pemilihan '{$event->title}' dari {$oldStatus} ke {$newStatus}",
            ['event_id' => $event->id, 'old' => $oldStatus, 'new' => $newStatus]
        );

        return back()->with('success', "Status pemilihan berhasil diubah menjadi: {$event->status_label}.");
    }

    /**
     * Join event as officer via collaborator invite link.
     */
    public function joinCollaborator(Request $request, Event $event)
    {
        $token = $request->query('token');

        if (!$token || $event->invite_token !== $token) {
            return redirect()->route('dashboard')->with('error', 'Tautan undangan kolaborator tidak valid atau sudah kadaluarsa.');
        }

        $user = Auth::user();

        // Check if already creator or officer or super admin
        if ($user->isSuperAdmin() || $event->created_by_user_id === $user->id || $event->officers()->where('users.id', $user->id)->exists()) {
            return redirect()->route('events.show', $event)->with('info', 'Anda sudah terdaftar sebagai petugas pada pemilihan ini.');
        }

        $event->officers()->attach($user->id);

        AuditLog::log(
            'OFFICER_JOINED',
            "Petugas {$user->nama} bergabung dalam kepanitiaan '{$event->title}'",
            ['event_id' => $event->id, 'user_id' => $user->id]
        );

        return redirect()->route('events.show', $event)
            ->with('success', "Selamat datang! Anda kini menjadi panitia kolaborator untuk '{$event->title}'.");
    }

    /**
     * Regenerate collaborator invite token.
     */
    public function regenerateInviteToken(Event $event)
    {
        $this->authorizeEventAccess($event);

        $event->update(['invite_token' => Str::random(32)]);

        return back()->with('success', 'Tautan undangan kolaborator berhasil diperbarui.');
    }

    /**
     * Remove the specified event.
     */
    public function destroy(Event $event)
    {
        $this->authorizeEventAccess($event);

        $title = $event->title;
        $event->delete();

        AuditLog::log(
            'EVENT_DELETED',
            "Menghapus agenda pemilihan: {$title}",
            ['title' => $title]
        );

        return redirect()->route('events.index')->with('success', "Agenda pemilihan '{$title}' berhasil dihapus.");
    }

    /**
     * Check if user has permission to manage this event.
     */
    protected function authorizeEventAccess(Event $event): void
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return;
        }

        $isOfficer = $event->officers()->where('users.id', $user->id)->exists();
        if ($event->created_by_user_id !== $user->id && !$isOfficer) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola agenda pemilihan ini.');
        }
    }
}
