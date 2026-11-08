<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Voter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Login panitia atau admin via email dan password
    public function userLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Kredensial tidak valid',
            ], 401);
        }

        // Generate personal access token Sanctum
        $token = $user->createToken('user_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    // Login pemilih via nomor identitas (NIS/NIP)
    public function voterLogin(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $voter = Voter::where('identifier', $request->identifier)->first();

        if (! $voter) {
            return response()->json([
                'message' => 'Kredensial tidak valid. NIS/NIP tidak ditemukan.',
            ], 401);
        }

        // Tolak akses jika akun dinonaktifkan panitia
        if (! $voter->is_active) {
            return response()->json([
                'message' => 'Akun pemilih tidak aktif',
            ], 403);
        }

        // Generate token akses untuk sesi bilik suara pemilih
        $token = $voter->createToken('voter_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'voter' => [
                'id' => $voter->id,
                'identifier' => $voter->identifier,
                'nama' => $voter->nama,
                'type' => $voter->type,
                'is_active' => $voter->is_active,
            ],
        ]);
    }

    // Ambil data profil pemilik token saat ini
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    // Hapus token akses saat pengguna logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }
}
