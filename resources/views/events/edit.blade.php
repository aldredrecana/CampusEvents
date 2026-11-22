<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Edit Event</h2>
    </x-slot>
    <div class="mx-auto max-w-2xl py-8 sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow sm:rounded-lg">
            <form method="POST" action="{{ route('events.update', $event) }}">
                @method('PUT')
                @include('events._form')
            </form>
        </div>
    </div>
</x-app-layout>
