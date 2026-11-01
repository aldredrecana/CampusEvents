<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\User;
use App\Support\QrToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AttendanceController extends Controller
{
    // Student: fresh signed code for the QR
    public function myCode(Request $request)
    {
        return response()->json(['token' => QrToken::make($request->user())]);
    }

    public function scan(Request $request, Event $event)
    {
        $this->authorizeOwner($request, $event);

        return view('events.scan', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $this->authorizeOwner($request, $event);
        $request->validate(['token' => ['required', 'string']]);

        $data = QrToken::resolve($request->token);
        if (! $data) {
            return response()->json(['message' => 'Invalid or expired QR code. Wait for the student\'s fresh code.'], 422);
        }

        $student = User::find($data['user_id']);
        if (! $student) {
            return response()->json(['message' => 'Student not found.'], 404);
        }

        if (! $event->isScanOpen()) {
            return response()->json(['message' => 'Scanning is not open for this event right now.'], 422);
        }

        if (! $event->isEligible($student)) {
            return response()->json(['message' => "{$student->name} is not on the list for this event."], 403);
        }

        // One-time use: a code that was already scanned is rejected
        if (! Cache::add('qr_used:' . $data['nonce'], true, 180)) {
            return response()->json(['message' => 'This code was already used.'], 422);
        }

        return response()->json([
            'message' => $this->record($event, $student, $request->user(), 'qr'),
        ]);
    }

    public function manual(Request $request, Event $event)
    {
        $this->authorizeOwner($request, $event);

        $request->validate([
            'student_number' => ['required', 'string'],
            'note' => ['required', 'string', 'max:255'],
        ]);

        $student = User::where('student_number', trim($request->student_number))->first();

        if (! $student) {
            return back()->withErrors(['student_number' => 'No student with that number.'])->withInput();
        }

        if (! $event->isScanOpen() && $request->user()->role !== 'admin') {
            return back()->withErrors(['student_number' => 'Check-in is not open for this event.'])->withInput();
        }

        if (! $event->isEligible($student)) {
            return back()->withErrors(['student_number' => "{$student->name} is not on the list for this event."])->withInput();
        }

        $message = $this->record($event, $student, $request->user(), 'manual', $request->note);

        return back()->with('success', $message);
    }

    public function attendees(Request $request, Event $event)
    {
        $this->authorizeOwner($request, $event);

        $attendances = $event->attendances()
            ->with(['user', 'scanner'])
            ->whereNotNull('checked_in_at')
            ->orderBy('checked_in_at')
            ->get();

        $expected = $event->eligibleStudents()->count();
        $present = $attendances->where('status', 'present')->count();
        $late = $attendances->where('status', 'late')->count();
        $absent = max($expected - $attendances->count(), 0);

        return view('events.attendees', compact('event', 'attendances', 'expected', 'present', 'late', 'absent'));
    }

    private function record(Event $event, User $student, User $scanner, string $source, ?string $note = null): string
    {
        $attendance = Attendance::firstOrCreate([
            'event_id' => $event->id,
            'user_id' => $student->id,
        ]);

        if (! $attendance->checked_in_at) {
            $isLate = now()->gt($event->starts_at->copy()->addMinutes($event->grace_minutes));

            $attendance->update([
                'checked_in_at' => now(),
                'status' => $isLate ? 'late' : 'present',
                'scanned_by' => $scanner->id,
                'source' => $source,
                'note' => $note,
            ]);

            return "{$student->name} checked in" . ($isLate ? ' (late).' : '.');
        }

        if (! $event->requires_checkout) {
            return "{$student->name} is already checked in.";
        }

        if (! $attendance->checked_out_at) {
            $minGap = config('campus.min_minutes_before_checkout');

            if ($attendance->checked_in_at->gt(now()->subMinutes($minGap))) {
                return "{$student->name} just checked in. Check-out opens after {$minGap} minutes.";
            }

            $attendance->update(['checked_out_at' => now()]);

            return "{$student->name} checked out.";
        }

        return "{$student->name} already checked out.";
    }

    private function authorizeOwner(Request $request, Event $event): void
    {
        $user = $request->user();

        if ($user->role !== 'admin' && $event->created_by !== $user->id) {
            abort(403);
        }
    }
}