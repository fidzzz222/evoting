<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voter;
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

    public function import(Request $request)
    {
        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt',
            ]);

            $path = $request->file('file')->getRealPath();
            $file = fopen($path, 'r');
            $header = fgetcsv($file);

            $imported = 0;
            $errors = [];

            while (($row = fgetcsv($file)) !== false) {
                if (count($row) < 4) {
                    continue;
                }

                $identifier = trim($row[0]);
                $nama = trim($row[1]);
                $type = strtoupper(trim($row[2]));
                $password = trim($row[3]);

                if (Voter::where('identifier', $identifier)->exists()) {
                    $errors[] = "Identifier {$identifier} sudah terdaftar.";
                    continue;
                }

                Voter::create([
                    'identifier' => $identifier,
                    'nama' => $nama,
                    'type' => in_array($type, ['SISWA', 'GURU'], true) ? $type : 'SISWA',
                    'password' => Hash::make($password),
                    'is_active' => true,
                ]);

                $imported++;
            }
            fclose($file);

            return response()->json([
                'message' => "Import selesai. {$imported} pemilih berhasil diimport.",
                'errors' => $errors,
            ]);
        }

        $validated = $request->validate([
            'voters' => 'required|array',
            'voters.*.identifier' => 'required|string',
            'voters.*.nama' => 'required|string',
            'voters.*.type' => ['required', Rule::in(['SISWA', 'GURU'])],
            'voters.*.password' => 'required|string',
        ]);

        $imported = 0;
        $errors = [];

        foreach ($validated['voters'] as $vData) {
            if (Voter::where('identifier', $vData['identifier'])->exists()) {
                $errors[] = "Identifier {$vData['identifier']} sudah terdaftar.";
                continue;
            }

            Voter::create([
                'identifier' => $vData['identifier'],
                'nama' => $vData['nama'],
                'type' => $vData['type'],
                'password' => Hash::make($vData['password']),
                'is_active' => true,
            ]);

            $imported++;
        }

        return response()->json([
            'message' => "Import selesai. {$imported} pemilih berhasil diimport.",
            'errors' => $errors,
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
