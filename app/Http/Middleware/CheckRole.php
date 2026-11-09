<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    // Cek hak akses role user (SUPER_ADMIN / PETUGAS) pada route yang dilindungi
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $allowedRoles = [];
        foreach ($roles as $r) {
            foreach (explode(',', $r) as $subRole) {
                $allowedRoles[] = trim($subRole);
            }
        }

        $user = $request->user() ?? Auth::guard('web')->user();

        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->route('admin.login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (! isset($user->role) || ! in_array($user->role, $allowedRoles, true)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Akses ditolak: Peran tidak memiliki izin',
                ], 403);
            }

            abort(403, 'Akses ditolak: Peran tidak memiliki izin');
        }

        return $next($request);
    }
}
