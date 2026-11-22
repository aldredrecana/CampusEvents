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
    <div class="min-h-screen grid lg:grid-cols-2">
        {{-- Left: PUP branding panel (hidden on phones) --}}
        <div class="hidden lg:flex flex-col justify-between bg-maroon-700 text-white p-12 relative overflow-hidden">
            <div class="absolute -right-24 -bottom-24 w-96 h-96 rounded-full border-[40px] border-gold-400/20"></div>
            <div class="flex items-center gap-3">
                <x-application-logo class="w-14 h-14" />
                <div>
                    <div class="font-bold text-lg">CampusEvents</div>
                    <div class="text-gold-300 text-sm">PUP Calauan Campus</div>
                </div>
            </div>
            <div>
                <h1 class="text-4xl font-bold leading-tight">QR attendance for<br>seminars, assemblies,<br>and org
                    activities.</h1>
                <p class="mt-4 text-maroon-100 max-w-md">Show your QR at the entrance. No more paper sheets, and your
                    attendance history is always one tap away.</p>
            </div>
            <p class="text-xs text-maroon-200">Polytechnic University of the Philippines</p>
        </div>

        {{-- Right: the form --}}
        <div class="flex flex-col justify-center items-center px-6 py-10 bg-maroon-50/40">
            <a href="/" class="lg:hidden flex flex-col items-center mb-6">
                <x-application-logo class="w-20 h-20" />
                <span class="mt-2 font-bold text-maroon-800">CampusEvents</span>
                <span class="text-xs text-gray-500">PUP Calauan Campus</span>
            </a>
            <div class="w-full sm:max-w-md px-6 py-6 bg-white shadow-md border-t-4 border-gold-400 rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>

</html>
