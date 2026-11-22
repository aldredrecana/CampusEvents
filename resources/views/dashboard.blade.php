<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Magandang araw, {{ Str::before($user->name, ' ') }}!</h2>
        <p class="text-sm text-gray-500">{{ now()->format('l, F j, Y') }}</p>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Stat cards --}}
        <div class="grid {{ count($stats) > 3 ? 'grid-cols-2 md:grid-cols-4' : 'grid-cols-3' }} gap-3 sm:gap-4">
            @foreach ($stats as $label => $value)
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-maroon-700 p-4">
                    <div class="text-3xl font-bold text-maroon-700">{{ $value }}</div>
                    <div class="text-xs uppercase tracking-wide text-gray-500">{{ $label }}</div>
                </div>
            @endforeach
        </div>

        {{-- Quick actions --}}
        <div class="flex flex-wrap gap-3">
            @if ($user->role === 'student')
                <a href="{{ route('my-qr') }}"
                    class="px-5 py-3 rounded-lg bg-maroon-700 text-white font-semibold hover:bg-maroon-800">Show my
                    QR</a>
                <a href="{{ route('my-attendance') }}"
                    class="px-5 py-3 rounded-lg bg-gold-400 text-maroon-900 font-semibold hover:bg-gold-300">My
                    attendance</a>
            @else
                <a href="{{ route('events.create') }}"
                    class="px-5 py-3 rounded-lg bg-maroon-700 text-white font-semibold hover:bg-maroon-800">+ New
                    event</a>
                <a href="{{ route('events.index') }}"
                    class="px-5 py-3 rounded-lg bg-gold-400 text-maroon-900 font-semibold hover:bg-gold-300">All
                    events</a>
            @endif
        </div>

        {{-- Gate staff assignments --}}
        @if ($staffEvents->isNotEmpty())
            <div class="bg-gold-50 border border-gold-300 rounded-lg p-4">
                <h3 class="font-semibold text-maroon-800 mb-2">You are gate staff for</h3>
                <ul class="divide-y divide-gold-200">
                    @foreach ($staffEvents as $e)
                        <li class="py-2 flex justify-between items-center gap-3">
                            <div>
                                <div class="font-medium">{{ $e->title }}</div>
                                <div class="text-xs text-gray-600">{{ $e->venue }} &middot;
                                    {{ $e->starts_at->format('M d, h:i A') }}</div>
                            </div>
                            <a href="{{ route('events.scan', $e) }}"
                                class="shrink-0 px-3 py-1.5 rounded bg-maroon-700 text-white text-sm">Scan</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Upcoming events --}}
        <div class="bg-white rounded-lg shadow-sm">
            <h3 class="px-4 py-3 border-b font-semibold text-maroon-800">Upcoming events</h3>
            <ul class="divide-y">
                @forelse ($upcoming as $e)
                    @php $live = now()->between($e->starts_at, $e->ends_at); @endphp
                    <li class="px-4 py-3 flex flex-wrap justify-between items-center gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 text-center rounded bg-maroon-700 text-white py-1">
                                <div class="text-[10px] uppercase">{{ $e->starts_at->format('M') }}</div>
                                <div class="text-lg font-bold leading-none">{{ $e->starts_at->format('d') }}</div>
                            </div>
                            <div>
                                <div class="font-medium">
                                    {{ $e->title }}
                                    @if ($live)
                                        <span
                                            class="ms-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800">LIVE</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">{{ $e->venue }} &middot;
                                    {{ $e->starts_at->format('h:i A') }} - {{ $e->ends_at->format('h:i A') }}</div>
                            </div>
                        </div>
                        @if ($user->role !== 'student')
                            <div class="flex items-center gap-3 text-sm">
                                <span class="text-gray-500">{{ $e->checked_in_count }} checked in</span>
                                <a href="{{ route('events.scan', $e) }}" class="text-maroon-700 font-semibold">Scan</a>
                                <a href="{{ route('events.attendees', $e) }}"
                                    class="text-maroon-700 font-semibold">Attendees</a>
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
