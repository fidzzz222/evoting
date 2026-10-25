<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfVoterAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('voter')->check()) {
            $roomCode = session('voter_room_code');
            if ($roomCode && ($event = \App\Models\Event::where('room_code', $roomCode)->where('status', 'PUBLISHED')->first())) {
                return redirect()->route('voter.booth', $event->room_code);
            }

            $activeEvent = \App\Models\Event::where('status', 'PUBLISHED')->latest()->first();
            if ($activeEvent) {
                return redirect()->route('voter.booth', $activeEvent->room_code);
            }

            return redirect()->route('voter.dashboard');
        }

        return $next($request);
    }
}
