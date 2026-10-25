<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    public function store(Request $request, string $eventId)
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
        $event = $query->findOrFail($eventId);

        $validated = $request->validate([
            'candidate_number' => 'required|integer',
            'name' => 'required|string|max:255',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:20480',
            'description' => 'nullable|string',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
        ]);

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('candidates', 'public');
            $photoUrl = Storage::url($path);
        }

        $candidate = Candidate::create([
            'event_id' => $event->id,
            'candidate_number' => $validated['candidate_number'],
            'name' => $validated['name'],
            'photo_url' => $photoUrl,
            'description' => $validated['description'] ?? null,
            'vision' => $validated['vision'] ?? null,
            'mission' => $validated['mission'] ?? null,
        ]);

        return response()->json([
            'message' => 'Kandidat berhasil ditambahkan',
            'data' => $candidate,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $candidate = Candidate::whereHas('event', function ($query) use ($user) {
            if ($user->role !== 'SUPER_ADMIN') {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by_user_id', $user->id)
                      ->orWhereHas('officers', function ($officerQuery) use ($user) {
                          $officerQuery->where('users.id', $user->id);
                      });
                });
            }
        })->findOrFail($id);

        $validated = $request->validate([
            'candidate_number' => 'sometimes|required|integer',
            'name' => 'sometimes|required|string|max:255',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:20480',
            'description' => 'nullable|string',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
        ]);

        if (isset($validated['candidate_number'])) {
            $candidate->candidate_number = $validated['candidate_number'];
        }

        if (isset($validated['name'])) {
            $candidate->name = $validated['name'];
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('candidates', 'public');
            $candidate->photo_url = Storage::url($path);
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

    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $candidate = Candidate::whereHas('event', function ($query) use ($user) {
            if ($user->role !== 'SUPER_ADMIN') {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by_user_id', $user->id)
                      ->orWhereHas('officers', function ($officerQuery) use ($user) {
                          $officerQuery->where('users.id', $user->id);
                      });
                });
            }
        })->findOrFail($id);

        $candidate->delete();

        return response()->json([
            'message' => 'Kandidat berhasil dihapus',
        ]);
    }
}
