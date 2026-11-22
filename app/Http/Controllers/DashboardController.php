<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // Events this user is assigned to scan (gate staff), even if they are not in the audience
        $staffEvents = $user->staffEvents()
            ->where('ends_at', '>=', now())
            ->orderBy('starts_at')
            ->get();

        if ($user->role === 'student') {
            $upcoming = Event::visibleTo($user)
                ->where('ends_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(5)
                ->get();

            $attended = $user->attendances()->whereNotNull('checked_in_at');
            $stats = [
                'On time' => (clone $attended)->where('status', 'present')->count(),
                'Late' => (clone $attended)->where('status', 'late')->count(),
                'Upcoming' => $upcoming->count(),
            ];

            return view('dashboard', compact('user', 'upcoming', 'staffEvents', 'stats'));
        }

        // Admin sees all events, organizer sees only their own
        $events = Event::query()
            ->when($user->role !== 'admin', fn ($q) => $q->where('created_by', $user->id));

        $upcoming = (clone $events)
            ->where('ends_at', '>=', now())
            ->withCount(['attendances as checked_in_count' => fn ($q) => $q->whereNotNull('checked_in_at')])
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        $stats = [
            'Upcoming events' => (clone $events)->where('ends_at', '>=', now())->count(),
            'Happening now' => (clone $events)->where('starts_at', '<=', now())->where('ends_at', '>=', now())->count(),
            'Total check-ins' => Attendance::whereNotNull('checked_in_at')
                ->whereIn('event_id', (clone $events)->select('id'))
                ->count(),
            'Students' => User::where('role', 'student')->count(),
        ];

        return view('dashboard', compact('user', 'upcoming', 'staffEvents', 'stats'));
    }
}
