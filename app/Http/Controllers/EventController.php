<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('creator')->orderBy('starts_at', 'desc')->paginate(10);

        return view('events.index', compact('events'));
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        Event::create($data);

        return redirect()->route('events.index')->with('success', 'Event created.');
    }

    public function edit(Request $request, Event $event)
    {
        $this->authorizeOwner($request, $event);

        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeOwner($request, $event);
        $event->update($this->validated($request));

        return redirect()->route('events.index')->with('success', 'Event updated.');
    }

    public function destroy(Request $request, Event $event)
    {
        $this->authorizeOwner($request, $event);
        $event->delete();

        return redirect()->route('events.index')->with('success', 'Event deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'venue' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);
    }

    private function authorizeOwner(Request $request, Event $event): void
    {
        $user = $request->user();

        if ($user->role !== 'admin' && $event->created_by !== $user->id) {
            abort(403);
        }
    }
}