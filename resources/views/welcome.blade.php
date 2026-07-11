<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('theme') === 'light' ? false : true }" :class="{ 'light': !dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'TradeLedger') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-body antialiased">
        <div class="bg-glow"></div>

        <div class="relative z-10 min-h-screen flex items-center justify-center p-6">
            <div class="text-center max-w-lg">
                <div class="flex items-center justify-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-[14px] bg-gradient-to-br from-profit to-profit-dim flex items-center justify-center font-display font-bold text-xl text-[#04231a]">T</div>
                    <span class="font-display font-semibold text-3xl tracking-tight text-white light:text-black">TradeLedger</span>
                </div>
                <p class="text-mute-d light:text-mute-l text-lg mb-8">
                    Personal portfolio trading management.<br>Catat, pantau, dan kontrol aktivitas trading harianmu.
                </p>
                <div class="flex gap-4 justify-center">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-6 py-3 rounded-xl bg-white text-ink font-display font-semibold text-sm hover:opacity-90 transition light:bg-ink light:text-white" wire:navigate>
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-6 py-3 rounded-xl bg-white text-ink font-display font-semibold text-sm hover:opacity-90 transition light:bg-ink light:text-white" wire:navigate>
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="px-6 py-3 rounded-xl bg-white/[.06] text-white font-display font-medium text-sm border border-white/[.12] hover:bg-white/[.1] transition light:bg-black/5 light:text-black light:border-black/10" wire:navigate>
                            Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </body>
</html>
