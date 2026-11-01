<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VoterAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('voter')->check()) {
            return redirect()->route('voter.login')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $voter = Auth::guard('voter')->user();
        if (!$voter->is_active) {
            Auth::guard('voter')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('voter.login')->with('error', 'Akun pemilih dinonaktifkan oleh panitia.');
        }

        return $next($request);
    }
}
