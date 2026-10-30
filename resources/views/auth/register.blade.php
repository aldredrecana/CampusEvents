<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
    <x-input-label for="student_number" value="Student Number" />
    <x-text-input id="student_number" class="block mt-1 w-full" type="text" name="student_number" :value="old('student_number')" required />
    <x-input-error :messages="$errors->get('student_number')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="course" value="Course" />
    <select id="course" name="course" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
        <option value="">Select course</option>
        @foreach (config('campus.courses') as $course)
            <option value="{{ $course }}" @selected(old('course') === $course)>{{ $course }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('course')" class="mt-2" />
</div>

<div class="mt-4 grid grid-cols-2 gap-4">
    <div>
        <x-input-label for="year_level" value="Year Level" />
        <select id="year_level" name="year_level" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
            <option value="">Select</option>
            @foreach ([1, 2, 3, 4] as $y)
                <option value="{{ $y }}" @selected(old('year_level') == $y)>{{ $y }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('year_level')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="section" value="Section" />
        <x-text-input id="section" class="block mt-1 w-full" type="text" name="section" :value="old('section')" required />
        <x-input-error :messages="$errors->get('section')" class="mt-2" />
    </div>
</div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
