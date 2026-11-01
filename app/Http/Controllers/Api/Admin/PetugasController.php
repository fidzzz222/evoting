<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PetugasController extends Controller
{
    public function index(Request $request)
    {
        $petugas = User::where('role', 'PETUGAS')
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($petugas);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $petugas = User::create([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'PETUGAS',
        ]);

        return response()->json([
            'message' => 'Petugas berhasil ditambahkan',
            'data' => $petugas,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $petugas = User::where('role', 'PETUGAS')->findOrFail($id);

        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
        ]);

        if (isset($validated['nama'])) {
            $petugas->nama = $validated['nama'];
        }

        if (isset($validated['email'])) {
            $petugas->email = $validated['email'];
        }

        if (! empty($validated['password'])) {
            $petugas->password = Hash::make($validated['password']);
        }

        $petugas->save();

        return response()->json([
            'message' => 'Data petugas berhasil diperbarui',
            'data' => $petugas,
        ]);
    }

    public function destroy(string $id)
    {
        $petugas = User::where('role', 'PETUGAS')->findOrFail($id);

        if ($petugas->events()->exists()) {
            return response()->json([
                'message' => 'Petugas tidak dapat dihapus karena masih memiliki event pemilihan yang dibuat.',
            ], 422);
        }

        $petugas->delete();

        return response()->json([
            'message' => 'Petugas berhasil dihapus',
        ]);
    }
}
