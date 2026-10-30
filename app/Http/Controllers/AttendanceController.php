<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function scan(Request $request, Event $event)
    {
        $this->authorizeOwner($request, $event);

        return view('events.scan', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $this->authorizeOwner($request, $event);
        $request->validate(['token' => ['required', 'string']]);

        $student = User::where('qr_token', $request->token)->first();

        if (! $student) {
            return response()->json(['message' => 'Invalid QR code.'], 404);
        }

        if (! $event->isScanOpen()) {
    return response()->json(['message' => 'Scanning is not open for this event right now.'], 422);
}

if (! $event->isEligible($student)) {
    return response()->json(['message' => "{$student->name} is not on the list for this event."], 403);
}

        $attendance = Attendance::firstOrCreate([
            'event_id' => $event->id,
            'user_id' => $student->id,
        ]);

        if (! $attendance->checked_in_at) {
            $attendance->update(['checked_in_at' => now()]);

            return response()->json(['message' => "{$student->name} checked in."]);
        }
        if (! $event->requires_checkout) {
    return response()->json(['message' => "{$student->name} is already checked in."]);
}

        if (! $attendance->checked_out_at) {
            // Ignore double scans within one minute
            if ($attendance->checked_in_at->gt(now()->subMinute())) {
                return response()->json(['message' => "{$student->name} is already checked in."]);
            }

            $attendance->update(['checked_out_at' => now()]);

            return response()->json(['message' => "{$student->name} checked out."]);
        }

        return response()->json(['message' => "{$student->name} already checked out."]);
    }

    public function attendees(Request $request, Event $event)
    {
        $this->authorizeOwner($request, $event);

        $attendances = $event->attendances()->with('user')->orderBy('checked_in_at')->get();

        return view('events.attendees', compact('event', 'attendances'));
    }

    private function authorizeOwner(Request $request, Event $event): void
    {
        $user = $request->user();

        if ($user->role !== 'admin' && $event->created_by !== $user->id) {
            abort(403);
        }
    }
}