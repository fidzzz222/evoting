<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCandidateRequest;
use App\Http\Requests\UpdateCandidateRequest;
use App\Models\Candidate;
use App\Models\Event;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    // simpan pasangan calon baru ke agenda
    public function store(StoreCandidateRequest $request, string $eventId)
    {
        $event = Event::accessibleBy($request->user())->findOrFail($eventId);

        $validated = $request->validated();

        $candidate = Candidate::create([
            'event_id' => $event->id,
            'candidate_number' => $validated['candidate_number'],
            'name' => $request->getCandidatePairName(),
            'description' => $validated['description'] ?? null,
            'vision' => $validated['vision'] ?? null,
            'mission' => $validated['mission'] ?? null,
        ]);

        if ($request->hasFile('photo')) {
            $candidate->savePhoto($request->file('photo'));
        }

        $event->syncDynamicStatus();

        return response()->json([
            'message' => 'Kandidat berhasil ditambahkan',
            'data' => $candidate,
        ], 201);
    }

    // perbarui data pasangan calon
    public function update(UpdateCandidateRequest $request, string $id)
    {
        $candidate = Candidate::whereHas('event', fn($q) => $q->accessibleBy($request->user()))->findOrFail($id);

        $validated = $request->validated();

        if (isset($validated['candidate_number'])) {
            $candidate->candidate_number = $validated['candidate_number'];
        }

        $name = $request->getCandidatePairName();
        if (!empty($name)) {
            $candidate->name = $name;
        }

        if ($request->hasFile('photo')) {
            $candidate->savePhoto($request->file('photo'));
        }

        if (array_key_exists('description', $validated)) {
            $candidate->description = $validated['description'];
        }

        if (array_key_exists('vision', $validated)) {
            $candidate->vision = $validated['vision'];
        }

        if (array_key_exists('mission', $validated)) {
            $candidate->mission = $validated['mission'];
        }

        $candidate->save();

        return response()->json([
            'message' => 'Kandidat berhasil diperbarui',
            'data' => $candidate,
        ]);
    }

    // hapus pasangan calon jika belum ada suara masuk
    public function destroy(Request $request, string $id)
    {
        $candidate = Candidate::whereHas('event', fn($q) => $q->accessibleBy($request->user()))->findOrFail($id);

        $event = $candidate->event;

        if ($candidate->votes()->exists() || $event->votes()->exists()) {
            return response()->json([
            'message' => 'Kandidat tidak dapat dihapus karena pemilihan telah menerima suara sah.',
            ], 422);
        }

        $candidate->delete();

        $event->syncDynamicStatus();

        return response()->json([
            'message' => 'Kandidat berhasil dihapus',
        ]);
    }
}
