<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voter;
use App\Services\VoterImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class VoterController extends Controller
{
    public function index(Request $request)
    {
        $query = Voter::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('identifier', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        $voters = $query->orderBy('id', 'desc')->paginate($request->get('per_page', 15));

        return response()->json($voters);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'identifier' => 'required|string|unique:voters,identifier',
            'nama' => 'required|string|max:255',
            'type' => ['required', Rule::in(['SISWA', 'GURU'])],
            'password' => 'required|string|min:4',
            'is_active' => 'sometimes|boolean',
        ]);

        $voter = Voter::create([
            'identifier' => $validated['identifier'],
            'nama' => $validated['nama'],
            'type' => $validated['type'],
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Pemilih berhasil ditambahkan',
            'data' => $voter,
        ], 201);
    }

    public function import(Request $request, VoterImportService $importService)
    {
        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt',
            ]);

            $result = $importService->importCsvForApi($request->file('file'));

            return response()->json([
                'message' => "Import selesai. {$result['imported']} pemilih berhasil diimport.",
                'errors' => $result['errors'],
            ]);
        }

        $validated = $request->validate([
            'voters' => 'required|array',
            'voters.*.identifier' => 'required|string',
            'voters.*.nama' => 'required|string',
            'voters.*.type' => ['required', Rule::in(['SISWA', 'GURU'])],
            'voters.*.password' => 'required|string',
        ]);

        $result = $importService->importArrayForApi($validated['voters']);

        return response()->json([
            'message' => "Import selesai. {$result['imported']} pemilih berhasil diimport.",
            'errors' => $result['errors'],
        ]);
    }

    public function update(Request $request, string $id)
    {
        $voter = Voter::findOrFail($id);

        $validated = $request->validate([
            'identifier' => ['required', 'string', Rule::unique('voters', 'identifier')->ignore($voter->id)],
            'nama' => 'required|string|max:255',
            'type' => ['required', Rule::in(['SISWA', 'GURU'])],
            'password' => 'nullable|string|min:4',
            'is_active' => 'sometimes|boolean',
        ]);

        $voter->identifier = $validated['identifier'];
        $voter->nama = $validated['nama'];
        $voter->type = $validated['type'];
        if (!empty($validated['password'])) {
            $voter->password = Hash::make($validated['password']);
        }
        if (isset($validated['is_active'])) {
            $voter->is_active = $validated['is_active'];
        }
        $voter->save();

        return response()->json([
            'message' => 'Data pemilih berhasil diperbarui',
            'data' => $voter,
        ]);
    }

    public function destroy(string $id)
    {
        $voter = Voter::findOrFail($id);
        $voter->delete();

        return response()->json([
            'message' => 'Data pemilih berhasil dihapus',
        ]);
    }

    public function toggleStatus(string $id)
    {
        $voter = Voter::findOrFail($id);
        $voter->is_active = ! $voter->is_active;
        $voter->save();

        return response()->json([
            'message' => 'Status pemilih berhasil diperbarui',
            'data' => $voter,
        ]);
    }
}
