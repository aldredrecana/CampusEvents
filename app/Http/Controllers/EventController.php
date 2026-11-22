<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\EventRequest;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $events = Event::visibleTo($user)
            ->with([
                'creator',
                // Load only this user's staff assignment, so the Scan button check needs no extra query per event
                'staff' => fn ($q) => $q->where('users.id', $user->id),
            ])
            ->orderBy('starts_at', 'desc')
            ->paginate(10);

        return view('events.index', compact('events'));
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(EventRequest $request)
    {
        $data = $request->eventData();
        $data['created_by'] = $request->user()->id;

        Event::create($data);

        return redirect()->route('events.index')->with('success', 'Event created.');
    }

    public function edit(Event $event)
    {
        Gate::authorize('manage', $event);

        return view('events.edit', compact('event'));
    }

    // EventRequest already checked that this user can manage the event
    public function update(EventRequest $request, Event $event)
    {
        $event->update($request->eventData());

        return redirect()->route('events.index')->with('success', 'Event updated.');
    }

    public function destroy(Event $event)
    {
        Gate::authorize('manage', $event);
        $event->delete();

        return redirect()->route('events.index')->with('success', 'Event archived.');
    }

    public function archived(Request $request)
    {
        $user = $request->user();

        $events = Event::onlyTrashed()
            ->when($user->role !== Role::Admin->value, fn ($q) => $q->where('created_by', $user->id))
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        return view('events.archived', compact('events'));
    }

    public function restore(Event $event)
    {
        Gate::authorize('manage', $event);
        $event->restore();

        return redirect()->route('events.index')->with('success', 'Event restored.');
    }
}
