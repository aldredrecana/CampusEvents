<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Attendees: {{ $event->title }}</h2>
    </x-slot>
    <div class="mx-auto max-w-5xl py-8 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 rounded bg-green-100 p-3 text-green-800">{{ session('success') }}</div>
        @endif

        @error('reason')
            <div class="mb-4 rounded bg-red-100 p-3 text-red-800">{{ $message }}</div>
        @enderror

        @if ($event->canBeManagedBy(auth()->user()))
            <div class="mb-4 text-right">
                <a href="{{ route('events.export', $event) }}"
                    class="inline-block rounded-md bg-maroon-700 px-4 py-2 text-sm text-white">Download CSV</a>
            </div>
        @endif

        <div class="mb-4 grid grid-cols-2 gap-3 text-center sm:grid-cols-4">
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
                <div class="text-xs text-gray-500">{{ $event->ends_at->isPast() ? 'Absent' : 'Not yet in' }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('events.manual', $event) }}"
            class="mb-4 grid gap-3 bg-white p-4 shadow sm:grid-cols-3 sm:rounded-lg">
            @csrf
            <div>
                <x-input-label for="student_number" value="Manual check-in: student number" />
                <x-text-input id="student_number" name="student_number" class="mt-1 block w-full" :value="old('student_number')"
                    required />
                <x-input-error :messages="$errors->get('student_number')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="note" value="Reason (required)" />
                <x-text-input id="note" name="note" class="mt-1 block w-full"
                    placeholder="e.g. phone battery died" :value="old('note')" required />
                <x-input-error :messages="$errors->get('note')" class="mt-1" />
            </div>
            <div class="flex items-end"><x-primary-button>Add manually</x-primary-button></div>
        </form>

        <div class="overflow-x-auto bg-white shadow sm:rounded-lg">
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
                        @if ($event->canBeManagedBy(auth()->user()))
                            <th class="p-3"></th>
                        @endif
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
                                    class="{{ $a->status === 'late' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }} rounded px-2 py-1 text-xs">
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
                            @if ($event->canBeManagedBy(auth()->user()))
                                <td class="p-3 text-right">
                                    <form method="POST"
                                        action="{{ route('events.attendances.destroy', [$event, $a]) }}"
                                        onsubmit="const r = prompt('Reason for removing this check-in? (required)'); if (!r) return false; this.reason.value = r;">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="reason">
                                        <button class="text-xs text-red-700 hover:underline">Undo</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-4 text-center text-gray-500">No attendance yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <details class="mt-4 bg-white p-4 shadow sm:rounded-lg">
            <summary class="cursor-pointer font-semibold">
                {{ $event->ends_at->isPast() ? 'Absent' : 'Not yet checked in' }} ({{ $absentees->count() }})
            </summary>
            <table class="mt-3 min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
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
