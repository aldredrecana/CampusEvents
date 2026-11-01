<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Attendees: {{ $event->title }}</h2>
    </x-slot>
    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        @if ($event->canBeManagedBy(auth()->user()))
            <div class="mb-4 text-right">
                <a href="{{ route('events.export', $event) }}"
                    class="inline-block px-4 py-2 bg-gray-800 text-white rounded-md text-sm">Download CSV</a>
            </div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4 text-center">
            <div class="bg-white p-3 shadow sm:rounded-lg">
                <div class="text-2xl font-bold">{{ $expected }}</div>
                <div class="text-xs text-gray-500">Expected</div>
            </div>
            <div class="bg-white p-3 shadow sm:rounded-lg">
                <div class="text-2xl font-bold text-green-700">{{ $present }}</div>
                <div class="text-xs text-gray-500">Present</div>
            </div>
            <div class="bg-white p-3 shadow sm:rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">{{ $late }}</div>
                <div class="text-xs text-gray-500">Late</div>
            </div>
            <div class="bg-white p-3 shadow sm:rounded-lg">
                <div class="text-2xl font-bold text-red-700">{{ $absent }}</div>
                {{ $event->ends_at->isPast() ? 'Absent' : 'Not yet in' }}
            </div>
        </div>

        <form method="POST" action="{{ route('events.manual', $event) }}"
            class="bg-white p-4 shadow sm:rounded-lg mb-4 grid gap-3 sm:grid-cols-3">
            @csrf
            <div>
                <x-input-label for="student_number" value="Manual check-in: student number" />
                <x-text-input id="student_number" name="student_number" class="block mt-1 w-full" :value="old('student_number')"
                    required />
                <x-input-error :messages="$errors->get('student_number')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="note" value="Reason (required)" />
                <x-text-input id="note" name="note" class="block mt-1 w-full"
                    placeholder="e.g. phone battery died" :value="old('note')" required />
                <x-input-error :messages="$errors->get('note')" class="mt-1" />
            </div>
            <div class="flex items-end"><x-primary-button>Add manually</x-primary-button></div>
        </form>

        <div class="bg-white shadow sm:rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="p-3">Name</th>
                        <th class="p-3">Student No.</th>
                        <th class="p-3">Section</th>
                        <th class="p-3">Check-in</th>
                        <th class="p-3">Check-out</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Source</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $a)
                        <tr class="border-t">
                            <td class="p-3">{{ $a->user->name }}</td>
                            <td class="p-3">{{ $a->user->student_number ?? '-' }}</td>
                            <td class="p-3">{{ $a->user->course }}
                                {{ $a->user->year_level }}-{{ $a->user->section }}</td>
                            <td class="p-3">{{ $a->checked_in_at?->format('h:i A') ?? '-' }}</td>
                            <td class="p-3">{{ $a->checked_out_at?->format('h:i A') ?? '-' }}</td>
                            <td class="p-3">
                                <span
                                    class="px-2 py-1 rounded text-xs {{ $a->status === 'late' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($a->status) }}
                                </span>
                            </td>
                            <td class="p-3">
                                {{ strtoupper($a->source) }}
                                @if ($a->scanner)
                                    <span class="text-gray-500">by {{ $a->scanner->name }}</span>
                                @endif
                                @if ($a->note)
                                    <div class="text-xs text-gray-500">{{ $a->note }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-gray-500">No attendance yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <details class="mt-4 bg-white shadow sm:rounded-lg p-4">
            <summary class="cursor-pointer font-semibold">
                {{ $event->ends_at->isPast() ? 'Absent' : 'Not yet checked in' }} ({{ $absentees->count() }})
            </summary>
            <table class="min-w-full text-sm mt-3">
                <thead class="text-left bg-gray-50">
                    <tr>
                        <th class="p-2">Student No.</th>
                        <th class="p-2">Name</th>
                        <th class="p-2">Section</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($absentees as $s)
                        <tr class="border-t">
                            <td class="p-2">{{ $s->student_number ?? '-' }}</td>
                            <td class="p-2">{{ $s->name }}</td>
                            <td class="p-2">{{ $s->course }} {{ $s->year_level }}-{{ $s->section }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-3 text-center text-gray-500">Everyone expected has checked in.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </details>
    </div>
</x-app-layout>
