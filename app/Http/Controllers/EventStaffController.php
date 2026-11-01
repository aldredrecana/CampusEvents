<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;

class EventStaffController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $this->authorizeManage($request, $event);

        $staff = $event->staff()->orderBy('name')->get();

        return view('events.staff', compact('event', 'staff'));
    }

    public function store(Request $request, Event $event)
    {
        $this->authorizeManage($request, $event);
        $request->validate(['identifier' => ['required', 'string', 'max:255']]);

        $identifier = trim($request->identifier);

        $user = User::where('email', strtolower($identifier))
            ->orWhere('student_number', $identifier)
            ->first();

        if (! $user) {
            return back()->withErrors(['identifier' => 'No user found with that email or student number.'])->withInput();
        }

        $event->staff()->syncWithoutDetaching([$user->id]);

        return back()->with('success', "{$user->name} can now scan for this event.");
    }

    public function destroy(Request $request, Event $event, User $user)
    {
        $this->authorizeManage($request, $event);

        $event->staff()->detach($user->id);

        return back()->with('success', 'Staff removed.');
    }

    private function authorizeManage(Request $request, Event $event): void
    {
        if (! $event->canBeManagedBy($request->user())) {
            abort(403);
        }
    }
}