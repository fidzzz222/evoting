<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\User;
use App\Models\Voter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EventController extends Controller
{
    // daftar agenda pemilihan
    public function index(Request $request)
    {
        // sinkronkan status agenda sesuai jadwal
        Event::syncAllEventStatuses();

        $query = Event::accessibleBy(Auth::user())
            ->with(['creator', 'candidates'])
            ->withCount(['candidates', 'votes', 'participations']);

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

        // urutkan berdasarkan status prioritas dan waktu dibuat
        $events = $query->orderByRaw("CASE status WHEN 'PUBLISHED' THEN 1 WHEN 'DRAFT' THEN 2 WHEN 'CLOSED' THEN 3 ELSE 4 END")
            ->orderBy('created_at', 'desc')
            ->paginate(9)
            ->withQueryString();

        return view('events.index', compact('events'));
    }

    // form buat agenda baru
    public function create()
    {
        $suggestedRoomCode = Event::generateUniqueRoomCode();
        $availableClasses = Voter::where('type', 'SISWA')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->orderBy('kelas')
            ->pluck('kelas');

        return view('events.create', compact('suggestedRoomCode', 'availableClasses'));
    }

    // simpan agenda pemilihan baru
    public function store(StoreEventRequest $request)
    {
        $validated = $request->validated();

        $event = Event::create([
            'created_by_user_id' => Auth::id(),
            'title' => $validated['title'],
            'category' => $validated['category'],
            'target_scope' => $validated['target_scope'] ?? 'ALL',
            'target_class' => ($validated['target_scope'] ?? '') === 'KELAS' ? trim($validated['target_class']) : null,
            'description' => $validated['description'] ?? null,
            'room_code' => strtoupper($validated['room_code']),
            'invite_token' => Event::generateUniqueInviteToken(),
            'status' => 'DRAFT',
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        $event->syncDynamicStatus();

        // super admin otomatis dimasukkan ke panitia agenda
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
            ->with('success', "Agenda '{$event->title}' berhasil dibuat. Daftarkan minimal 2 paslon.");
    }

    // detail dan dashboard agenda
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

    // form edit agenda
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

    // update data agenda
    public function update(UpdateEventRequest $request, Event $event)
    {
        $this->authorizeEventAccess($event);

        $validated = $request->validated();

        $event->update([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'target_scope' => $validated['target_scope'] ?? 'ALL',
            'target_class' => ($validated['target_scope'] ?? '') === 'KELAS' ? trim($validated['target_class']) : null,
            'description' => $validated['description'] ?? null,
            'room_code' => strtoupper($validated['room_code']),
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        $event->syncDynamicStatus();

        AuditLog::log(
            'EVENT_UPDATED',
            "Memperbarui informasi agenda: {$event->title} [Target: {$event->target_scope_label}]",
            ['event_id' => $event->id, 'target_scope' => $event->target_scope, 'target_class' => $event->target_class]
        );

        return redirect()->route('events.show', $event)
            ->with('success', 'Agenda pemilihan berhasil diperbarui.');
    }

    // ubah status agenda secara manual
    public function updateStatus(Request $request, Event $event)
    {
        $this->authorizeEventAccess($event);

        $request->validate([
            'status' => ['required', 'in:DRAFT,PUBLISHED,CLOSED'],
        ]);

        $oldStatus = $event->status;
        $newStatus = $request->status;

        // wajib minimal 2 paslon dan belum lewat batas waktu
        if ($newStatus === 'PUBLISHED') {
            if ($event->candidates()->count() < 2) {
                return back()->with('error', 'Minimal 2 paslon untuk mengaktifkan agenda.');
            }

            if ($event->isAfterEndTime()) {
                return back()->with('error', "Batas waktu ({$event->formatted_end_time}) telah berakhir. Perbarui jadwal selesai.");
            }
        }

        $event->update(['status' => $newStatus]);
        $event->syncDynamicStatus();

        AuditLog::log(
            'EVENT_STATUS_CHANGED',
            "Mengubah status pemilihan '{$event->title}' dari {$oldStatus} ke {$newStatus}",
            ['event_id' => $event->id, 'old' => $oldStatus, 'new' => $newStatus]
        );

        return back()->with('success', "Status agenda diubah menjadi {$event->status_label}.");
    }

    // gabung panitia lewat link undangan
    public function joinCollaborator(Request $request, Event $event)
    {
        $token = $request->query('token');

        if (!$token || $event->invite_token !== $token) {
            return redirect()->route('dashboard')->with('error', 'Tautan undangan tidak valid atau kedaluwarsa.');
        }

        $user = Auth::user();

        // cek apakah user sudah jadi panitia
        if ($user->isSuperAdmin() || $event->created_by_user_id === $user->id || $event->officers()->where('users.id', $user->id)->exists()) {
            return redirect()->route('events.show', $event)->with('info', 'Anda sudah terdaftar sebagai panitia.');
        }

        $event->officers()->attach($user->id);

        AuditLog::log(
            'OFFICER_JOINED',
            "Petugas {$user->nama} bergabung dalam kepanitiaan '{$event->title}'",
            ['event_id' => $event->id, 'user_id' => $user->id]
        );

        return redirect()->route('events.show', $event)
            ->with('success', "Berhasil bergabung dalam panitia '{$event->title}'.");
    }

    // buat ulang token link undangan
    public function regenerateInviteToken(Event $event)
    {
        $this->authorizeEventAccess($event);

        $event->update(['invite_token' => Str::random(32)]);

        return back()->with('success', 'Tautan undangan berhasil diperbarui.');
    }

    // hapus agenda
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

        return redirect()->route('events.index')->with('success', "Agenda '{$title}' berhasil dihapus.");
    }

    // cek hak akses kelola agenda
    protected function authorizeEventAccess(Event $event): void
    {
        if (!$event->canBeManagedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola agenda pemilihan ini.');
        }
    }
}
