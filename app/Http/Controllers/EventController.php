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
    $data = $request->validate([
        'title' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'venue' => ['required', 'string', 'max:255'],
        'starts_at' => ['required', 'date'],
        'ends_at' => ['required', 'date', 'after:starts_at'],
        'target_courses' => ['nullable', 'array'],
        'target_courses.*' => ['in:' . implode(',', config('campus.courses'))],
        'target_year_levels' => ['nullable', 'array'],
        'target_year_levels.*' => ['integer', 'between:1,6'],
        'target_sections' => ['nullable', 'string', 'max:255'],
        'grace_minutes' => ['required', 'integer', 'min:0', 'max:240'],
    ]);

    $data['target_courses'] = $data['target_courses'] ?? [];
    $data['target_year_levels'] = array_map('intval', $data['target_year_levels'] ?? []);
    $data['target_sections'] = collect(explode(',', $data['target_sections'] ?? ''))
        ->map(fn ($s) => strtoupper(trim($s)))
        ->filter()
        ->values()
        ->all();
    $data['requires_checkout'] = $request->boolean('requires_checkout');

    return $data;
}

    private function authorizeOwner(Request $request, Event $event): void
    {
        $user = $request->user();

        if ($user->role !== 'admin' && $event->created_by !== $user->id) {
            abort(403);
        }
    }
}