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
    <div class="flex min-h-screen flex-col bg-maroon-50/40">
        @include('layouts.navigation')

        @isset($header)
            <header class="border-b border-maroon-100 bg-white shadow-sm">
                <div class="mx-auto max-w-7xl px-4 py-5 text-maroon-800 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="bg-maroon-900 text-xs text-maroon-100">
            <div class="mx-auto flex max-w-7xl flex-col justify-between gap-1 px-4 py-4 sm:flex-row sm:px-6 lg:px-8">
                <span>Polytechnic University of the Philippines &middot; Calauan Campus</span>
                <span class="text-gold-300">CampusEvents Attendance Monitoring System</span>
            </div>
        </footer>
    </div>
</body>

</html>
