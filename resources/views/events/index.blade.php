<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Events</h2>
            @if (in_array(auth()->user()->role, ['admin', 'organizer']))
                <a href="{{ route('events.create') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm">New Event</a>
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <div class="bg-white shadow sm:rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="p-3">Title</th>
                        <th class="p-3">Venue</th>
                        <th class="p-3">Starts</th>
                        <th class="p-3">Ends</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr class="border-t">
                            <td class="p-3">{{ $event->title }}</td>
                            <td class="p-3">{{ $event->venue }}</td>
                            <td class="p-3">{{ $event->starts_at->format('M d, Y h:i A') }}</td>
                            <td class="p-3">{{ $event->ends_at->format('M d, Y h:i A') }}</td>
                            <td class="p-3 flex gap-3">
                                @if (auth()->user()->role === 'admin' || $event->created_by === auth()->id())
                                    <a href="{{ route('events.edit', $event) }}" class="text-indigo-600">Edit</a>
                                    <form method="POST" action="{{ route('events.destroy', $event) }}"
                                          onsubmit="return confirm('Delete this event?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-4 text-center text-gray-500">No events yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $events->links() }}</div>
    </div>
</x-app-layout>