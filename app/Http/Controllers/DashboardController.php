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
            $totalEvents = Event::where('created_by_user_id', $user->id)->count();
            $activeEventsCount = Event::where('created_by_user_id', $user->id)->where('status', 'PUBLISHED')->count();
            $draftEventsCount = Event::where('created_by_user_id', $user->id)->where('status', 'DRAFT')->count();
            $closedEventsCount = Event::where('created_by_user_id', $user->id)->where('status', 'CLOSED')->count();

            $eventsQuery = Event::where('created_by_user_id', $user->id)
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
