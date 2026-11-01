<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    private function generateUniqueRoomCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (Event::where('room_code', $code)->exists());

        return $code;
    }

    private function generateUniqueInviteToken(): string
    {
        do {
            $token = Str::random(32);
        } while (Event::where('invite_token', $token)->exists());

        return $token;
    }

    public function index(Request $request)
    {
        Event::syncAllEventStatuses();

        $user = $request->user();

        // SUPER_ADMIN dapat melihat semua event
        // PETUGAS melihat event yang dibuat oleh dirinya sendiri ATAU di mana dirinya menjadi petugas kolaborator (officer)
        $query = Event::with(['creator', 'officers', 'candidates'])
            ->withCount('candidates')
            ->orderBy('id', 'desc');

        if ($user->role !== 'SUPER_ADMIN') {
            $query->where(function ($q) use ($user) {
                $q->where('created_by_user_id', $user->id)
                  ->orWhereHas('officers', function ($officerQuery) use ($user) {
                      $officerQuery->where('users.id', $user->id);
                  });
            });
        }

        $events = $query->get();

        foreach ($events as $event) {
            $event->syncDynamicStatus();
        }

        // Pagination setelah update
        $perPage = $request->get('per_page', 100);
        $page = $request->get('page', 1);
        $total = $events->count();
        $items = $events->forPage($page, $perPage)->values();

        return response()->json([
            'data' => $items,
            'current_page' => (int) $page,
            'per_page' => (int) $perPage,
            'total' => $total,
            'last_page' => (int) ceil($total / $perPage),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'room_code' => 'nullable|string|unique:events,room_code',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
        ]);

        $roomCode = ! empty($validated['room_code'])
            ? strtoupper($validated['room_code'])
            : $this->generateUniqueRoomCode();

        $inviteToken = $this->generateUniqueInviteToken();

        $event = Event::create([
            'created_by_user_id' => $request->user()->id,
            'title' => $validated['title'],
            'category' => $validated['category'] ?? 'KETUA_OSIS',
            'description' => $validated['description'] ?? null,
            'room_code' => $roomCode,
            'invite_token' => $inviteToken,
            'status' => 'DRAFT',
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
        ]);

        $event->syncDynamicStatus();

        $superAdminIds = User::where('role', 'SUPER_ADMIN')->pluck('id')->all();
        if (!empty($superAdminIds)) {
            $event->officers()->syncWithoutDetaching($superAdminIds);
        }

        AuditLog::log('CREATE_EVENT', "Membuat event baru '{$event->title}' (Kode Room: {$event->room_code})", [
            'event_id' => $event->id,
            'title' => $event->title,
        ]);

        return response()->json([
            'message' => 'Event berhasil dibuat',
            'data' => $event->load(['creator', 'officers', 'candidates']),
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $query = Event::with(['creator', 'officers', 'candidates' => function ($q) {
            $q->orderBy('candidate_number', 'asc');
        }]);

        if ($user->role !== 'SUPER_ADMIN') {
            $query->where(function ($q) use ($user) {
                $q->where('created_by_user_id', $user->id)
                  ->orWhereHas('officers', function ($officerQuery) use ($user) {
                      $officerQuery->where('users.id', $user->id);
                  });
            });
        }

        $event = $query->findOrFail($id);
        $event->syncDynamicStatus();

        return response()->json([
            'data' => $event,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $query = Event::query();

        if ($user->role !== 'SUPER_ADMIN') {
            $query->where(function ($q) use ($user) {
                $q->where('created_by_user_id', $user->id)
                  ->orWhereHas('officers', function ($officerQuery) use ($user) {
                      $officerQuery->where('users.id', $user->id);
                  });
            });
        }

        $event = $query->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'room_code' => ['nullable', 'string', Rule::unique('events', 'room_code')->ignore($event->id)],
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
        ]);

        if (isset($validated['title'])) {
            $event->title = $validated['title'];
        }

        if (array_key_exists('category', $validated)) {
            $event->category = $validated['category'];
        }

        if (array_key_exists('description', $validated)) {
            $event->description = $validated['description'];
        }

        if (! empty($validated['room_code'])) {
            $event->room_code = strtoupper($validated['room_code']);
        }

        if (array_key_exists('start_time', $validated)) {
            $event->start_time = $validated['start_time'];
        }

        if (array_key_exists('end_time', $validated)) {
            $event->end_time = $validated['end_time'];
        }

        $event->save();
        $event->syncDynamicStatus();

        AuditLog::log('UPDATE_EVENT', "Memperbarui data event '{$event->title}'", [
            'event_id' => $event->id,
            'start_time' => $event->start_time,
            'end_time' => $event->end_time,
        ]);

        return response()->json([
            'message' => 'Event berhasil diperbarui',
            'data' => $event,
        ]);
    }

    public function updateStatus(Request $request, string $id)
    {
        $user = $request->user();
        $query = Event::query();

        if ($user->role !== 'SUPER_ADMIN') {
            $query->where(function ($q) use ($user) {
                $q->where('created_by_user_id', $user->id)
                  ->orWhereHas('officers', function ($officerQuery) use ($user) {
                      $officerQuery->where('users.id', $user->id);
                  });
            });
        }

        $event = $query->findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['DRAFT', 'PUBLISHED', 'CLOSED'])],
            'end_time' => 'nullable|date|after:now', // Untuk reopen event yang sudah selesai
        ]);

        $newStatus = $validated['status'];

        // Jika mengubah status menjadi PUBLISHED, wajib memiliki minimal 2 kandidat terdaftar
        if ($newStatus === 'PUBLISHED' && $event->candidates()->count() < 2) {
            return response()->json([
                'message' => 'Event belum dapat diaktifkan karena belum memiliki minimal 2 pasangan calon terdaftar.',
                'candidates_count' => $event->candidates()->count(),
                'min_candidates_required' => 2,
            ], 422);
        }

        // Jika membuka kembali event yang sudah CLOSED, wajib isi end_time baru
        if ($event->status === 'CLOSED' && $newStatus === 'PUBLISHED') {
            // Cek apakah end_time sudah lewat atau belum ada
            $now = now();
            $needsNewEndTime = !$event->end_time || $now->greaterThan($event->end_time);

            if ($needsNewEndTime && !isset($validated['end_time'])) {
                return response()->json([
                    'message' => 'Untuk membuka kembali event ini, Anda harus menentukan waktu selesai baru.',
                    'requires_end_time' => true,
                ], 422);
            }

            // Update end_time jika diberikan
            if (isset($validated['end_time'])) {
                $event->end_time = $validated['end_time'];
            }
        }

        $event->status = $newStatus;
        $event->save();

        AuditLog::log('CHANGE_EVENT_STATUS', "Mengubah status event '{$event->title}' menjadi {$event->status}", [
            'event_id' => $event->id,
            'status' => $event->status,
            'end_time' => $event->end_time,
        ]);

        return response()->json([
            'message' => 'Status event berhasil diubah',
            'data' => $event,
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $query = Event::query();

        if ($user->role !== 'SUPER_ADMIN') {
            $query->where(function ($q) use ($user) {
                $q->where('created_by_user_id', $user->id)
                  ->orWhereHas('officers', function ($officerQuery) use ($user) {
                      $officerQuery->where('users.id', $user->id);
                  });
            });
        }

        $event = $query->findOrFail($id);
        $eventTitle = $event->title;

        // Hapus kandidat berserta foto terkait jika ada
        foreach ($event->candidates as $candidate) {
            if ($candidate->photo_url) {
                $relativePath = str_replace('/storage/', '', $candidate->photo_url);
                Storage::disk('public')->delete($relativePath);
            }
            $candidate->delete();
        }

        // Hapus votes dan event participations
        $event->votes()->delete();
        $event->participations()->delete();
        $event->officers()->detach();

        // Hapus event
        $event->delete();

        AuditLog::log('DELETE_EVENT', "Menghapus event '{$eventTitle}' beserta data terkait", [
            'event_id' => $id,
            'title' => $eventTitle,
        ]);

        return response()->json([
            'message' => "Event '{$eventTitle}' berhasil dihapus.",
        ]);
    }

    /**
     * Dapatkan detail link invite kolaborasi petugas
     */
    public function getInviteInfo(Request $request, string $id)
    {
        $user = $request->user();
        $query = Event::with('officers');

        if ($user->role !== 'SUPER_ADMIN') {
            $query->where(function ($q) use ($user) {
                $q->where('created_by_user_id', $user->id)
                  ->orWhereHas('officers', function ($officerQuery) use ($user) {
                      $officerQuery->where('users.id', $user->id);
                  });
            });
        }

        $event = $query->findOrFail($id);

        // Jika invite_token belum ada (misal event lama), generate baru
        if (empty($event->invite_token)) {
            $event->invite_token = $this->generateUniqueInviteToken();
            $event->save();
        }

        return response()->json([
            'invite_token' => $event->invite_token,
            'event_id' => $event->id,
            'title' => $event->title,
            'officers' => $event->officers,
        ]);
    }

    /**
     * Regenerate token invite link jika link bocor
     */
    public function regenerateInviteToken(Request $request, string $id)
    {
        $user = $request->user();
        $query = Event::query();

        if ($user->role !== 'SUPER_ADMIN') {
            $query->where('created_by_user_id', $user->id);
        }

        $event = $query->findOrFail($id);
        $event->invite_token = $this->generateUniqueInviteToken();
        $event->save();

        AuditLog::log('REGENERATE_INVITE_TOKEN', "Membuat ulang invite token untuk event '{$event->title}'", [
            'event_id' => $event->id,
        ]);

        return response()->json([
            'message' => 'Link undang petugas berhasil diperbarui',
            'invite_token' => $event->invite_token,
        ]);
    }

    /**
     * Dapatkan detail info event berdasarkan token undangan sebelum bergabung
     */
    public function getJoinEventInfo(Request $request, string $token)
    {
        $event = Event::where('invite_token', $token)
            ->with(['creator', 'officers', 'candidates'])
            ->withCount(['candidates', 'officers'])
            ->first();

        if (! $event) {
            return response()->json([
                'message' => 'Link undangan tidak valid atau sudah kedaluwarsa.',
            ], 404);
        }

        return response()->json([
            'data' => $event,
        ]);
    }

    /**
     * Petugas lain bergabung ke event menggunakan invite token via link URL
     */
    public function joinViaInviteToken(Request $request, string $token)
    {
        $user = $request->user();

        $event = Event::where('invite_token', $token)->first();

        if (! $event) {
            return response()->json([
                'message' => 'Link undangan tidak valid atau sudah kedaluwarsa.',
            ], 404);
        }

        // Cek jika user adalah pembuat event
        if ((int) $event->created_by_user_id === (int) $user->id) {
            return response()->json([
                'message' => 'Anda adalah pembuat event ini.',
                'data' => $event->load(['creator', 'officers', 'candidates']),
                'already_joined' => true,
            ]);
        }

        // Cek jika user sudah terdaftar sebagai officer
        if ($event->officers()->where('users.id', $user->id)->exists()) {
            return response()->json([
                'message' => 'Anda sudah terdaftar sebagai petugas pada event ini.',
                'data' => $event->load(['creator', 'officers', 'candidates']),
                'already_joined' => true,
            ]);
        }

        // Hubungkan petugas ke event
        $event->officers()->attach($user->id);

        AuditLog::log('JOIN_EVENT_OFFICER', "Petugas '{$user->nama}' bergabung mengelola event '{$event->title}' via link undangan", [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'user_name' => $user->nama,
        ]);

        return response()->json([
            'message' => "Berhasil bergabung sebagai pengelola event '{$event->title}'.",
            'data' => $event->load(['creator', 'officers', 'candidates']),
            'already_joined' => false,
        ]);
    }

    /**
     * Hapus / batalkan akses petugas kolaborator dari event
     */
    public function removeOfficer(Request $request, string $id, string $officerId)
    {
        $user = $request->user();
        $query = Event::query();

        if ($user->role !== 'SUPER_ADMIN') {
            $query->where('created_by_user_id', $user->id);
        }

        $event = $query->findOrFail($id);
        $event->officers()->detach($officerId);

        AuditLog::log('REMOVE_EVENT_OFFICER', "Menghapus petugas ID: {$officerId} dari pengelolaan event '{$event->title}'", [
            'event_id' => $event->id,
            'removed_officer_id' => $officerId,
        ]);

        return response()->json([
            'message' => 'Petugas berhasil dihapus dari pengelolaan event.',
            'officers' => $event->fresh()->officers,
        ]);
    }
}
