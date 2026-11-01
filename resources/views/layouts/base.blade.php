<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'E-Voting Sekolah' }} | E-Voting Sistem Digital</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
    </style>
</head>
<body class="h-full text-slate-800 antialiased font-sans flex flex-col selection:bg-blue-600 selection:text-white">
    {{-- Global Notifications --}}
    <div class="fixed top-4 right-4 left-4 sm:left-auto sm:right-6 sm:top-6 z-50 flex flex-col gap-2 pointer-events-none max-w-sm w-auto">
        @if(session('success'))
            <x-toast-notification type="success" :fixed="false" :timeout="4000" :dismissible="true">
                {{ session('success') }}
            </x-toast-notification>
        @endif

        @if(session('error'))
            <x-toast-notification type="error" :fixed="false" :timeout="5000" :dismissible="true">
                {{ session('error') }}
            </x-toast-notification>
        @endif

        @if(session('warning'))
            <x-toast-notification type="warning" :fixed="false" :timeout="5000" :dismissible="true">
                {{ session('warning') }}
            </x-toast-notification>
        @endif

        @if(session('info'))
            <x-toast-notification type="info" :fixed="false" :timeout="4000" :dismissible="true">
                {{ session('info') }}
            </x-toast-notification>
        @endif
    </div>
    </div>

    {{ $slot ?? '' }}

    @stack('scripts')
</body>
</html>
