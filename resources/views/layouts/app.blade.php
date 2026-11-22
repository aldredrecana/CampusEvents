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

<body class="font-sans antialiased text-gray-900">
    <div class="min-h-screen flex flex-col bg-maroon-50/40">
        @include('layouts.navigation')

        @isset($header)
            <header class="bg-white shadow-sm border-b border-maroon-100">
                <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8 text-maroon-800">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="bg-maroon-900 text-maroon-100 text-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row justify-between gap-1">
                <span>Polytechnic University of the Philippines &middot; Calauan Campus</span>
                <span class="text-gold-300">CampusEvents Attendance Monitoring System</span>
            </div>
        </footer>
    </div>
</body>

</html>
