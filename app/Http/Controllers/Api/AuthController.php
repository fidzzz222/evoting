<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Voter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
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

    public function voterLogin(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'nullable|string',
        ]);

        $voter = Voter::where('identifier', $request->identifier)->first();

        if (! $voter) {
            return response()->json([
                'message' => 'Kredensial tidak valid. NIS/NIP tidak ditemukan.',
            ], 401);
        }

        if ($request->filled('password') && ! Hash::check($request->password, $voter->password)) {
            return response()->json([
                'message' => 'Password tidak valid',
            ], 401);
        }

        if (! $voter->is_active) {
            return response()->json([
                'message' => 'Akun pemilih tidak aktif',
            ], 403);
        }

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

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }
}
