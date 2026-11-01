<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">My Attendance</h2>
    </x-slot>
    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">

        <div class="grid grid-cols-3 gap-3 mb-4 text-center">
            <div class="bg-white p-3 shadow sm:rounded-lg">
                <div class="text-2xl font-bold text-green-700">{{ $present }}</div>
                <div class="text-xs text-gray-500">On time</div>
            </div>
            <div class="bg-white p-3 shadow sm:rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">{{ $late }}</div>
                <div class="text-xs text-gray-500">Late</div>
            </div>
            <div class="bg-white p-3 shadow sm:rounded-lg">
                <div class="text-2xl font-bold text-red-700">{{ $missed->count() }}</div>
                <div class="text-xs text-gray-500">Missed</div>
            </div>
        </div>

        <div class="bg-white shadow sm:rounded-lg overflow-x-auto mb-6">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="p-3">Event</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">Checked in</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attended as $a)
                        <tr class="border-t">
                            <td class="p-3">{{ $a->event->title }}</td>
                            <td class="p-3">{{ $a->event->starts_at->format('M d, Y') }}</td>
                            <td class="p-3">{{ $a->checked_in_at->format('h:i A') }}</td>
                            <td class="p-3">
                                <span
                                    class="px-2 py-1 rounded text-xs {{ $a->status === 'late' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($a->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-500">No attendance recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($missed->isNotEmpty())
            <h3 class="font-semibold mb-2">Missed events</h3>
            <div class="bg-white shadow sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <tbody>
                        @foreach ($missed as $e)
                            <tr class="border-t first:border-t-0">
                                <td class="p-3">{{ $e->title }}</td>
                                <td class="p-3">{{ $e->starts_at->format('M d, Y') }}</td>
                                <td class="p-3 text-red-700">Absent</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
