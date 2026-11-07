<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VoterAuthenticate
{
    // Verifikasi otentikasi dan status aktif akun pemilih
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('voter')->check()) {
            return redirect()->route('voter.login')->with('error', 'Silakan masukkan token / NISN untuk login ke bilik suara.');
        }

        $voter = Auth::guard('voter')->user();
        if (!$voter->is_active) {
            Auth::guard('voter')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('voter.login')->with('error', 'Akun pemilih Anda telah dinonaktifkan oleh panitia.');
        }

        return $next($request);
    }
}
