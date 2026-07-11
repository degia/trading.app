<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('theme') === 'light' ? false : true }" :class="{ 'light': !dark }" x-init="$watch('dark', val => localStorage.setItem('theme', val ? 'dark' : 'light'))">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'TradeLedger') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-body antialiased">
        {{-- Ambient background glow --}}
        <div class="bg-glow"></div>

        {{-- App shell --}}
        <div class="relative z-10 min-h-screen flex" x-data="{ sidebarOpen: true, mobileMenu: false }">
            {{-- Sidebar (desktop) --}}
            <aside class="hidden lg:flex lg:flex-col w-[220px] shrink-0 p-6 border-r border-white/[.09] light:border-black/[.08] backdrop-blur-xl bg-white/[.025] light:bg-white/50">
                {{-- Brand --}}
                <div class="flex items-center gap-2.5 mb-9 pl-1">
                    <div class="w-9 h-9 rounded-[10px] bg-gradient-to-br from-profit to-profit-dim flex items-center justify-center font-display font-bold text-sm text-[#04231a]">T</div>
                    <span class="font-display font-semibold text-[19px] tracking-tight text-white light:text-black">TradeLedger</span>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 space-y-[3px]">
                    @php
                        $navItems = [
                            ['label' => 'Overview', 'route' => 'dashboard', 'icon' => 'overview'],
                            ['label' => 'Daily Log', 'route' => 'daily-log', 'icon' => 'log'],
                            ['label' => 'Target & Rules', 'route' => 'targets', 'icon' => 'target'],
                            ['label' => 'Deposit / WD', 'route' => 'transactions', 'icon' => 'deposit'],
                            ['label' => 'Analytics', 'route' => 'analytics', 'icon' => 'analytics'],
                            ['label' => 'Journal', 'route' => 'journal', 'icon' => 'journal'],
                            ['label' => 'Pengaturan', 'route' => 'settings', 'icon' => 'settings'],
                        ];
                    @endphp

                    @foreach($navItems as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-[10px] text-[13.5px] transition-all duration-200
                                  {{ request()->routeIs($item['route']) ? 'bg-white/[.06] text-white light:bg-black/5 light:text-black' : 'text-mute-d light:text-mute-l hover:bg-white/[.03] light:hover:bg-black/[.03]' }}">
                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ request()->routeIs($item['route']) ? 'bg-profit' : 'bg-current opacity-50' }}"></span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                {{-- Sidebar footer --}}
                <div class="pt-5 mt-5 border-t border-white/[.09] light:border-black/[.08] text-[11px] text-mute-d light:text-mute-l">
                    TradeLedger v1.0<br>Personal Portfolio Manager
                </div>
            </aside>

            {{-- Mobile bottom nav --}}
            <nav class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-ink-2/90 light:bg-paper-3/90 backdrop-blur-xl border-t border-white/[.09] light:border-black/[.08] px-2 py-1.5 flex justify-around">
                @foreach(array_slice($navItems, 0, 5) as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-lg text-[10px] {{ request()->routeIs($item['route']) ? 'text-profit' : 'text-mute-d light:text-mute-l' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs($item['route']) ? 'bg-profit' : 'bg-current opacity-40' }}"></span>
                        {{ Str::limit($item['label'], 8) }}
                    </a>
                @endforeach
                <button @click="mobileMenu = !mobileMenu" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-lg text-[10px] text-mute-d light:text-mute-l">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-40"></span>
                    Lainnya
                </button>
            </nav>

            {{-- Mobile menu overlay --}}
            <div x-show="mobileMenu" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @click="mobileMenu = false" class="lg:hidden fixed inset-0 z-40 bg-black/50" style="display:none"></div>
            <div x-show="mobileMenu" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
                 class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-ink-2 light:bg-paper-2 border-t border-white/[.09] light:border-black/[.08] rounded-t-2xl p-6 pb-24" style="display:none">
                <div class="space-y-1">
                    @foreach($navItems as $item)
                        <a href="{{ route($item['route']) }}" @click="mobileMenu = false"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition-all
                                  {{ request()->routeIs($item['route']) ? 'bg-white/[.06] text-white light:bg-black/5 light:text-black' : 'text-mute-d light:text-mute-l' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs($item['route']) ? 'bg-profit' : 'bg-current opacity-50' }}"></span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Main content --}}
            <main class="flex-1 min-w-0 p-6 lg:p-7 pb-24 lg:pb-7">
                {{-- Topbar --}}
                <div class="flex items-center justify-between mb-7 flex-wrap gap-4">
                    <h1 class="font-display text-[22px] font-semibold tracking-tight text-white light:text-black">
                        @yield('page-title', 'Overview')
                    </h1>

                    <div class="flex items-center gap-3">
                        {{-- Account pill --}}
                        <div class="flex items-center gap-2 px-3.5 py-[7px] rounded-full text-xs font-medium bg-profit/12 text-profit border border-profit/25">
                            <span class="w-1.5 h-1.5 rounded-full bg-profit shadow-[0_0_0_3px_rgba(32,227,162,0.2)]"></span>
                            Real Account
                        </div>

                        {{-- Theme toggle --}}
                        <button @click="dark = !dark"
                                class="w-[52px] h-[28px] rounded-full bg-white/[.08] light:bg-black/6 border border-white/[.09] light:border-black/[.08] relative cursor-pointer transition-colors duration-300">
                            <span class="absolute top-[2px] left-[2px] w-[22px] h-[22px] rounded-full bg-white light:bg-ink flex items-center justify-center transition-transform duration-300"
                                  :class="!dark ? 'translate-x-[24px]' : ''">
                                <span x-text="dark ? '☀' : '☾'" class="text-[11px]"></span>
                            </span>
                        </button>
                    </div>
                </div>

                {{-- Flash messages --}}
                @if (session('status'))
                    <div class="mb-4 p-4 rounded-lg bg-profit/10 text-profit text-sm border border-profit/20">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Page content --}}
                {{ $slot }}
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
