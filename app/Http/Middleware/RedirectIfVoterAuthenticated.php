<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfVoterAuthenticated
{
    // Arahkan pemilih yang sudah login langsung ke bilik suara aktif atau dashboard
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('voter')->check()) {
            $roomCode = session('voter_room_code');
            if ($roomCode && ($event = \App\Models\Event::where('room_code', $roomCode)->where('status', 'PUBLISHED')->first()) && $event->isOngoing()) {
                return redirect()->route('voter.booth', $event->room_code);
            }

            $activeEvent = \App\Models\Event::where('status', 'PUBLISHED')->latest()->get()->first(function ($e) {
                return $e->isOngoing();
            });
            if ($activeEvent) {
                return redirect()->route('voter.booth', $activeEvent->room_code);
            }

            return redirect()->route('voter.dashboard');
        }

        return $next($request);
    }
}
