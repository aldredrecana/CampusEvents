<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Magandang araw, {{ Str::before($user->name, ' ') }}!</h2>
        <p class="text-sm text-gray-500">{{ now()->format('l, F j, Y') }}</p>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

        {{-- Stat cards --}}
        <div class="{{ count($stats) > 3 ? 'grid-cols-2 md:grid-cols-4' : 'grid-cols-3' }} grid gap-3 sm:gap-4">
            @foreach ($stats as $label => $value)
                <div class="rounded-lg border-l-4 border-maroon-700 bg-white p-4 shadow-sm">
                    <div class="text-3xl font-bold text-maroon-700">{{ $value }}</div>
                    <div class="text-xs uppercase tracking-wide text-gray-500">{{ $label }}</div>
                </div>
            @endforeach
        </div>

        {{-- Quick actions --}}
        <div class="flex flex-wrap gap-3">
            @if ($user->role === 'student')
                <a href="{{ route('my-qr') }}"
                    class="rounded-lg bg-maroon-700 px-5 py-3 font-semibold text-white hover:bg-maroon-800">Show my
                    QR</a>
                <a href="{{ route('my-attendance') }}"
                    class="rounded-lg bg-gold-400 px-5 py-3 font-semibold text-maroon-900 hover:bg-gold-300">My
                    attendance</a>
            @else
                <a href="{{ route('events.create') }}"
                    class="rounded-lg bg-maroon-700 px-5 py-3 font-semibold text-white hover:bg-maroon-800">+ New
                    event</a>
                <a href="{{ route('events.index') }}"
                    class="rounded-lg bg-gold-400 px-5 py-3 font-semibold text-maroon-900 hover:bg-gold-300">All
                    events</a>
            @endif
        </div>

        {{-- Gate staff assignments --}}
        @if ($staffEvents->isNotEmpty())
            <div class="rounded-lg border border-gold-300 bg-gold-50 p-4">
                <h3 class="mb-2 font-semibold text-maroon-800">You are gate staff for</h3>
                <ul class="divide-y divide-gold-200">
                    @foreach ($staffEvents as $e)
                        <li class="flex items-center justify-between gap-3 py-2">
                            <div>
                                <div class="font-medium">{{ $e->title }}</div>
                                <div class="text-xs text-gray-600">{{ $e->venue }} &middot;
                                    {{ $e->starts_at->format('M d, h:i A') }}</div>
                            </div>
                            <a href="{{ route('events.scan', $e) }}"
                                class="shrink-0 rounded bg-maroon-700 px-3 py-1.5 text-sm text-white">Scan</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Upcoming events --}}
        <div class="rounded-lg bg-white shadow-sm">
            <h3 class="border-b px-4 py-3 font-semibold text-maroon-800">Upcoming events</h3>
            <ul class="divide-y">
                @forelse ($upcoming as $e)
                    @php $live = now()->between($e->starts_at, $e->ends_at); @endphp
                    <li class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 rounded bg-maroon-700 py-1 text-center text-white">
                                <div class="text-[10px] uppercase">{{ $e->starts_at->format('M') }}</div>
                                <div class="text-lg font-bold leading-none">{{ $e->starts_at->format('d') }}</div>
                            </div>
                            <div>
                                <div class="font-medium">
                                    {{ $e->title }}
                                    @if ($live)
                                        <span
                                            class="ms-1 rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-bold text-green-800">LIVE</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">{{ $e->venue }} &middot;
                                    {{ $e->starts_at->format('h:i A') }} - {{ $e->ends_at->format('h:i A') }}</div>
                            </div>
                        </div>
                        @if ($user->role !== 'student')
                            <div class="flex items-center gap-3 text-sm">
                                <span class="text-gray-500">{{ $e->checked_in_count }} checked in</span>
                                <a href="{{ route('events.scan', $e) }}" class="font-semibold text-maroon-700">Scan</a>
                                <a href="{{ route('events.attendees', $e) }}"
                                    class="font-semibold text-maroon-700">Attendees</a>
                            </div>
                        @endif
                    </li>
                @empty
                    <li class="px-4 py-6 text-center text-gray-500">No upcoming events.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>
