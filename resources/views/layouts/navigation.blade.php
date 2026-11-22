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
        $links[] = ['Users', 'admin.users.index', 'admin.users.*'];
        $links[] = ['Masterlist', 'admin.masterlist.index', 'admin.masterlist.*'];
    }
@endphp

<nav x-data="{ open: false }" class="border-b-4 border-gold-400 bg-maroon-700">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2">
                    <x-application-logo class="block h-10 w-10" />
                    <span class="leading-tight">
                        <span class="block font-bold text-white">CampusEvents</span>
                        <span class="block text-[11px] text-gold-300">PUP Calauan</span>
                    </span>
                </a>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @foreach ($links as [$label, $route, $pattern])
                        <x-nav-link :href="route($route)" :active="request()->routeIs($pattern)">{{ $label }}</x-nav-link>
                    @endforeach
                </div>
            </div>

            <div class="hidden sm:ms-6 sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium leading-4 text-white transition hover:bg-maroon-800 focus:outline-none">
                            <div class="text-right">
                                <div>{{ $user->name }}</div>
                                <div class="text-[11px] uppercase tracking-wide text-gold-300">{{ $user->role }}</div>
                            </div>
                            <svg class="ms-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
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
                    class="inline-flex items-center justify-center rounded-md p-2 text-white transition hover:bg-maroon-800 focus:outline-none">
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
        <div class="space-y-1 pb-3 pt-2">
            @foreach ($links as [$label, $route, $pattern])
                <x-responsive-nav-link :href="route($route)" :active="request()->routeIs($pattern)">{{ $label }}</x-responsive-nav-link>
            @endforeach
        </div>

        <div class="border-t border-maroon-600 pb-1 pt-4">
            <div class="px-4">
                <div class="text-base font-medium text-white">{{ $user->name }}</div>
                <div class="text-sm font-medium text-gold-300">{{ $user->email }}</div>
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
