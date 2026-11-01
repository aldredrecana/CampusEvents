<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Staff: {{ $event->title }}</h2>
    </x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('events.staff.store', $event) }}"
            class="bg-white p-4 shadow sm:rounded-lg mb-4 flex gap-3 items-end">
            @csrf
            <div class="flex-1">
                <x-input-label for="identifier" value="Add scanner staff: email or student number" />
                <x-text-input id="identifier" name="identifier" class="block mt-1 w-full" :value="old('identifier')" required />
                <x-input-error :messages="$errors->get('identifier')" class="mt-1" />
            </div>
            <x-primary-button>Add</x-primary-button>
        </form>

        <div class="bg-white shadow sm:rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="p-3">Name</th>
                        <th class="p-3">Email</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $member)
                        <tr class="border-t">
                            <td class="p-3">{{ $member->name }}</td>
                            <td class="p-3">{{ $member->email }}</td>
                            <td class="p-3">
                                <form method="POST" action="{{ route('events.staff.destroy', [$event, $member]) }}"
                                    onsubmit="return confirm('Remove this staff member?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-4 text-center text-gray-500">No staff assigned yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('events.index') }}" class="inline-block mt-4 text-indigo-600">Back to events</a>
    </div>
</x-app-layout>
