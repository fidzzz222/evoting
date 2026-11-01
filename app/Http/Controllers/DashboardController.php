<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the admin/petugas executive dashboard.
     */
    public function index()
    {
        Event::syncAllEventStatuses();

        $user = Auth::user();

        // Scope metrics and events based on user role
        // Super Admin oversees all events; Panitia (Petugas) only sees & manages events they created.
        if ($user->isSuperAdmin()) {
            $totalEvents = Event::count();
            $activeEventsCount = Event::where('status', 'PUBLISHED')->count();
            $draftEventsCount = Event::where('status', 'DRAFT')->count();
            $closedEventsCount = Event::where('status', 'CLOSED')->count();

            $eventsQuery = Event::with(['candidates', 'creator'])
                ->withCount(['candidates', 'votes', 'participations']);
        } else {
            $baseQuery = Event::where(function ($q) use ($user) {
                $q->where('created_by_user_id', $user->id)
                  ->orWhereHas('officers', function ($officerQuery) use ($user) {
                      $officerQuery->where('users.id', $user->id);
                  });
            });

            $totalEvents = (clone $baseQuery)->count();
            $activeEventsCount = (clone $baseQuery)->where('status', 'PUBLISHED')->count();
            $draftEventsCount = (clone $baseQuery)->where('status', 'DRAFT')->count();
            $closedEventsCount = (clone $baseQuery)->where('status', 'CLOSED')->count();

            $eventsQuery = (clone $baseQuery)
                ->with(['candidates', 'creator'])
                ->withCount(['candidates', 'votes', 'participations']);
        }

        $events = $eventsQuery->orderByRaw("CASE status WHEN 'PUBLISHED' THEN 1 WHEN 'DRAFT' THEN 2 WHEN 'CLOSED' THEN 3 ELSE 4 END")
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        return view('dashboard.index', compact(
            'totalEvents',
            'activeEventsCount',
            'draftEventsCount',
            'closedEventsCount',
            'events',
            'user'
        ));
    }
}
