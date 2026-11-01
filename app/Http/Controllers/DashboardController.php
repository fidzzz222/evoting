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

        // Scope metrics and events based on user role using centralized scope
        $baseQuery = Event::accessibleBy($user);

        $totalEvents = (clone $baseQuery)->count();
        $activeEventsCount = (clone $baseQuery)->where('status', 'PUBLISHED')->count();
        $draftEventsCount = (clone $baseQuery)->where('status', 'DRAFT')->count();
        $closedEventsCount = (clone $baseQuery)->where('status', 'CLOSED')->count();

        $events = (clone $baseQuery)
            ->with(['candidates', 'creator'])
            ->withCount(['candidates', 'votes', 'participations'])
            ->orderByRaw("CASE status WHEN 'PUBLISHED' THEN 1 WHEN 'DRAFT' THEN 2 WHEN 'CLOSED' THEN 3 ELSE 4 END")
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
