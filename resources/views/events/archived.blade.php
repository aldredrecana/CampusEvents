<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Archived Events</h2>
    </x-slot>
    <div class="mx-auto max-w-5xl py-8 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 rounded bg-green-100 p-3 text-green-800">{{ session('success') }}</div>
        @endif

        <div class="overflow-x-auto bg-white shadow sm:rounded-lg">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="p-3">Title</th>
                        <th class="p-3">Venue</th>
                        <th class="p-3">Archived</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr class="border-t">
                            <td class="p-3">{{ $event->title }}</td>
                            <td class="p-3">{{ $event->venue }}</td>
                            <td class="p-3">{{ $event->deleted_at->format('M d, Y h:i A') }}</td>
                            <td class="p-3">
                                <form method="POST" action="{{ route('events.restore', $event) }}">
                                    @csrf @method('PATCH')
                                    <button class="text-maroon-600">Restore</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-500">No archived events.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $events->links() }}</div>
        <a href="{{ route('events.index') }}" class="mt-4 inline-block text-maroon-600">Back to events</a>
    </div>
</x-app-layout>
