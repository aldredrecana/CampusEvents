<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#800000">

    <title>{{ config('app.name', 'CampusEvents') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- Left: PUP branding panel (hidden on phones) --}}
        <div class="relative hidden flex-col justify-between overflow-hidden bg-maroon-700 p-12 text-white lg:flex">
            <div class="absolute -bottom-24 -right-24 h-96 w-96 rounded-full border-[40px] border-gold-400/20"></div>
            <div class="flex items-center gap-3">
                <x-application-logo class="h-14 w-14" />
                <div>
                    <div class="text-lg font-bold">CampusEvents</div>
                    <div class="text-sm text-gold-300">PUP Calauan Campus</div>
                </div>
            </div>
            <div>
                <h1 class="text-4xl font-bold leading-tight">QR attendance for<br>seminars, assemblies,<br>and org
                    activities.</h1>
                <p class="mt-4 max-w-md text-maroon-100">Show your QR at the entrance. No more paper sheets, and your
                    attendance history is always one tap away.</p>
            </div>
            <p class="text-xs text-maroon-200">Polytechnic University of the Philippines</p>
        </div>

        {{-- Right: the form --}}
        <div class="flex flex-col items-center justify-center bg-maroon-50/40 px-6 py-10">
            <a href="/" class="mb-6 flex flex-col items-center lg:hidden">
                <x-application-logo class="h-20 w-20" />
                <span class="mt-2 font-bold text-maroon-800">CampusEvents</span>
                <span class="text-xs text-gray-500">PUP Calauan Campus</span>
            </a>
            <div class="w-full rounded-lg border-t-4 border-gold-400 bg-white px-6 py-6 shadow-md sm:max-w-md">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>

</html>
