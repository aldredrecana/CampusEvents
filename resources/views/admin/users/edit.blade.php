<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Edit User: {{ $user->name }}</h2>
    </x-slot>
    <div class="mx-auto max-w-xl py-8 sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow sm:rounded-lg">
            <p class="mb-4 text-sm text-gray-500">{{ $user->email }}</p>

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <x-input-label for="role" value="Role" />
                    <select id="role" name="role"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @foreach (['student', 'organizer', 'admin'] as $r)
                            <option value="{{ $r }}" @selected(old('role', $user->role) === $r)>{{ ucfirst($r) }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="student_number" value="Student Number" />
                    <x-text-input id="student_number" name="student_number" class="mt-1 block w-full"
                        :value="old('student_number', $user->student_number)" />
                    <x-input-error :messages="$errors->get('student_number')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="course" value="Course" />
                    <select id="course" name="course"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">None</option>
                        @foreach (config('campus.courses') as $c)
                            <option value="{{ $c }}" @selected(old('course', $user->course) === $c)>{{ $c }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('course')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="year_level" value="Year Level" />
                        <select id="year_level" name="year_level"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">None</option>
                            @foreach ([1, 2, 3, 4] as $y)
                                <option value="{{ $y }}" @selected((int) old('year_level', $user->year_level) === $y)>{{ $y }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('year_level')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="section" value="Section" />
                        <x-text-input id="section" name="section" class="mt-1 block w-full" :value="old('section', $user->section)" />
                        <x-input-error :messages="$errors->get('section')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>Save</x-primary-button>
                    <a href="{{ route('admin.users.index') }}" class="text-gray-600">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
