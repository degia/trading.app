<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('theme') === 'light' ? false : true }" :class="{ 'light': !dark }" x-init="$watch('dark', val => localStorage.setItem('theme', val ? 'dark' : 'light'))">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'TradeLedger') }} — Login</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-body antialiased">
        <div class="bg-glow"></div>

        <div class="relative z-10 min-h-screen flex items-center justify-center p-6">
            <div class="w-full max-w-[420px] bg-white/[.045] light:bg-white/[.55] backdrop-blur-[28px] saturate-[140%] border border-white/[.09] light:border-black/[.08] rounded-[20px] p-10 shadow-[0_20px_60px_rgba(0,0,0,0.4),inset_0_1px_0_rgba(255,255,255,0.06)] light:shadow-[0_20px_60px_rgba(0,0,0,0.08),inset_0_1px_0_rgba(255,255,255,0.6)]">
                {{ $slot }}
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
