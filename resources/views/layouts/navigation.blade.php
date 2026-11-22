@php
    $user = Auth::user();

    // One list of links, used by both the desktop bar and the mobile menu
    $links = [['Dashboard', 'dashboard', 'dashboard']];
    $links[] = ['Events', 'events.index', 'events.*'];
    if ($user->role === 'student') {
        $links[] = ['My QR', 'my-qr', 'my-qr'];
        $links[] = ['My Attendance', 'my-attendance', 'my-attendance'];
    }
    if ($user->role === 'admin') {
        $links[] = ['Users', 'admin.users.index', 'admin.*'];
    }
@endphp

<nav x-data="{ open: false }" class="bg-maroon-700 border-b-4 border-gold-400">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <a href="{{ route('dashboard') }}" class="shrink-0 flex items-center gap-2">
                    <x-application-logo class="block h-10 w-10" />
                    <span class="leading-tight">
                        <span class="block text-white font-bold">CampusEvents</span>
                        <span class="block text-[11px] text-gold-300">PUP Calauan</span>
                    </span>
                </a>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @foreach ($links as [$label, $route, $pattern])
                        <x-nav-link :href="route($route)" :active="request()->routeIs($pattern)">{{ $label }}</x-nav-link>
                    @endforeach
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 text-sm leading-4 font-medium rounded-md text-white hover:bg-maroon-800 focus:outline-none transition">
                            <div class="text-right">
                                <div>{{ $user->name }}</div>
                                <div class="text-[11px] uppercase tracking-wide text-gold-300">{{ $user->role }}</div>
                            </div>
                            <svg class="ms-1 fill-current h-4 w-4" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">Log
                                Out</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" aria-label="Menu"
                    class="inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-maroon-800 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu: students use phones, so every link must be here too --}}
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @foreach ($links as [$label, $route, $pattern])
                <x-responsive-nav-link :href="route($route)" :active="request()->routeIs($pattern)">{{ $label }}</x-responsive-nav-link>
            @endforeach
        </div>

        <div class="pt-4 pb-1 border-t border-maroon-600">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ $user->name }}</div>
                <div class="font-medium text-sm text-gold-300">{{ $user->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Profile</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
