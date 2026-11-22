<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Student Masterlist</h2>
        <p class="text-sm text-gray-500">Official student list from the registrar. Used to check who can register.</p>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="rounded bg-green-100 p-3 text-green-800">{{ session('success') }}</div>
        @endif

        @if (session('import_error_count'))
            <div class="rounded border border-gold-300 bg-gold-50 p-3 text-sm">
                <p class="font-semibold text-maroon-800">{{ session('import_error_count') }} row(s) were skipped:</p>
                <ul class="ms-5 mt-1 list-disc text-gray-700">
                    @foreach (session('import_errors', []) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-3 gap-3 sm:gap-4">
            <div class="rounded-lg border-l-4 border-maroon-700 bg-white p-4 shadow-sm">
                <div class="text-3xl font-bold text-maroon-700">{{ $total }}</div>
                <div class="text-xs uppercase tracking-wide text-gray-500">In masterlist</div>
            </div>
            <div class="rounded-lg border-l-4 border-maroon-700 bg-white p-4 shadow-sm">
                <div class="text-3xl font-bold text-maroon-700">{{ $registered }}</div>
                <div class="text-xs uppercase tracking-wide text-gray-500">Registered</div>
            </div>
            <div class="rounded-lg border-l-4 border-maroon-700 bg-white p-4 shadow-sm">
                <div class="text-3xl font-bold text-maroon-700">{{ $total - $registered }}</div>
                <div class="text-xs uppercase tracking-wide text-gray-500">Not yet registered</div>
            </div>
        </div>

        <div class="rounded-lg bg-white p-4 shadow-sm">
            <h3 class="font-semibold text-maroon-800">Import CSV</h3>
            <p class="mt-1 text-sm text-gray-600">
                Columns: student_number, last_name, first_name, course, year_level, section.
                Existing student numbers are updated, new ones are added.
                <a href="{{ route('admin.masterlist.template') }}"
                    class="font-semibold text-maroon-700 underline">Download template</a>
            </p>
            <p class="{{ config('campus.require_masterlist') ? 'text-green-700' : 'text-gray-500' }} mt-1 text-sm">
                Registration check is
                {{ config('campus.require_masterlist') ? 'ON: only students in this list can register.' : 'OFF (set REQUIRE_MASTERLIST=true in .env to turn it on).' }}
            </p>

            <form method="POST" action="{{ route('admin.masterlist.import') }}" enctype="multipart/form-data"
                class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                @csrf
                <input type="file" name="file" accept=".csv,text/csv" required
                    class="text-sm file:me-3 file:rounded-md file:border-0 file:bg-maroon-50 file:px-4 file:py-2 file:font-semibold file:text-maroon-800">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="sync_users" value="1" checked
                        class="rounded border-gray-300 text-maroon-700 focus:ring-maroon-600">
                    Also update course/year/section of students who already registered
                </label>
                <x-primary-button>Import</x-primary-button>
            </form>
            <x-input-error :messages="$errors->get('file')" class="mt-2" />
        </div>

        <div>
            <form method="GET" class="mb-3 flex gap-2">
                <x-text-input name="q" class="w-full" placeholder="Search student number or name"
                    :value="$search" />
                <x-primary-button>Search</x-primary-button>
            </form>

            <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left">
                        <tr>
                            <th class="p-3">Student No.</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Course / Year / Section</th>
                            <th class="p-3">Account</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $s)
                            <tr class="border-t">
                                <td class="p-3 font-mono">{{ $s->student_number }}</td>
                                <td class="p-3">{{ $s->last_name }}, {{ $s->first_name }}</td>
                                <td class="p-3">{{ $s->course }} {{ $s->year_level }}-{{ $s->section }}</td>
                                <td class="p-3">
                                    @if ($s->user)
                                        <span
                                            class="rounded bg-green-100 px-2 py-1 text-xs text-green-800">Registered</span>
                                    @else
                                        <span class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600">Not yet</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-gray-500">No students yet. Import the
                                    registrar's CSV above.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $students->links() }}</div>
        </div>
    </div>
</x-app-layout>
