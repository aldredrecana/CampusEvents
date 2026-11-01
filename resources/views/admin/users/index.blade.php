<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Users</h2>
    </x-slot>
    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <form method="GET" class="mb-4 flex gap-2">
            <x-text-input name="q" class="w-full" placeholder="Search name, email, or student number"
                :value="$search" />
            <x-primary-button>Search</x-primary-button>
        </form>

        <div class="bg-white shadow sm:rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="p-3">Name</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Student No.</th>
                        <th class="p-3">Course / Year / Section</th>
                        <th class="p-3">Role</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                        <tr class="border-t">
                            <td class="p-3">{{ $u->name }}</td>
                            <td class="p-3">{{ $u->email }}</td>
                            <td class="p-3">{{ $u->student_number ?? '-' }}</td>
                            <td class="p-3">
                                @if ($u->course)
                                    {{ $u->course }} {{ $u->year_level }}-{{ $u->section }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-3">
                                <span
                                    class="px-2 py-1 rounded text-xs
                                    {{ $u->role === 'admin' ? 'bg-red-100 text-red-800' : ($u->role === 'organizer' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($u->role) }}
                                </span>
                            </td>
                            <td class="p-3"><a href="{{ route('admin.users.edit', $u) }}"
                                    class="text-indigo-600">Edit</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
    </div>
</x-app-layout>
