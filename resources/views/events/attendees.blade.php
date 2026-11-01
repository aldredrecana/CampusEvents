<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Attendees: {{ $event->title }}</h2></x-slot>
    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr><th class="p-3">Name</th><th class="p-3">Email</th><th class="p-3">Check-in</th><th class="p-3">Check-out</th></tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $a)
                        <tr class="border-t">
                            <td class="p-3">{{ $a->user->name }}</td>
                            <td class="p-3">{{ $a->user->email }}</td>
                            <td class="p-3">{{ $a->checked_in_at?->format('h:i A') ?? '-' }}</td>
                            <td class="p-3">{{ $a->checked_out_at?->format('h:i A') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-4 text-center text-gray-500">No attendance yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="mt-4 text-sm text-gray-600">Total: {{ $attendances->count() }}</p>
    </div>
</x-app-layout>