<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a0a0c" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#f7f7f5" media="(prefers-color-scheme: light)">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="TradeLedger">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">

    <title>{{ config('app.name', 'TradeLedger') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <script>
        (function(){var t=localStorage.getItem('theme')||'dark';document.documentElement.className=t;
        if(t==='light'){document.body&&(document.body.className='font-body antialiased light');}})();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.2/dist/apexcharts.min.js"></script>
</head>
<body class="font-body antialiased dark"
      x-data="{
          mobileNav: false,
          init() {
              if (!Alpine.store('theme')) {
                  Alpine.store('theme', localStorage.getItem('theme') || 'dark');
              }
              this.applyTheme();
              this.$watch('$store.theme', () => this.applyTheme());
          },
          applyTheme() {
              const t = Alpine.store('theme');
              localStorage.setItem('theme', t);
              document.documentElement.className = t;
              document.body.className = t === 'dark' ? 'font-body antialiased' : 'font-body antialiased light';
          },
          toggle() {
              Alpine.store('theme', Alpine.store('theme') === 'dark' ? 'light' : 'dark');
          }
      }">

    <div class="bg-glow"></div>

    @php
        $navItems = [
            ['label' => 'Overview', 'route' => 'dashboard', 'slug' => 'dashboard', 'icon' => 'overview'],
            ['label' => 'Daily Log', 'route' => 'daily-log', 'slug' => 'daily-log', 'icon' => 'dailylog'],
            ['label' => 'Target', 'route' => 'target-rules', 'slug' => 'target-rules', 'icon' => 'target'],
            ['label' => 'Deposit', 'route' => 'deposit-withdrawal', 'slug' => 'deposit-withdrawal', 'icon' => 'deposit'],
            ['label' => 'Analytics', 'route' => 'analytics', 'slug' => 'analytics', 'icon' => 'analytics'],
            ['label' => 'Journal', 'route' => 'journal', 'slug' => 'journal', 'icon' => 'journal'],
        ];
        $currentRoute = request()->route()->getName() ?? '';
    @endphp

    <div class="flex min-h-screen relative z-10">

        {{-- Sidebar (desktop) --}}
        <aside class="w-[220px] shrink-0 py-6 px-4 flex-col border-r backdrop-blur-xl hidden lg:flex
            dark:border-white/[0.09] dark:bg-white/[0.025]
            border-black/[0.08] bg-white/50">
            <div class="flex items-center gap-2.5 mb-9 pl-1">
                <div class="w-9 h-9 rounded-[10px] bg-gradient-to-br from-profit to-profit-dim flex items-center justify-center font-display font-bold text-base text-[#04231a]">T</div>
                <span class="font-display font-semibold text-[19px] tracking-tight">TradeLedger</span>
            </div>

            <nav class="flex flex-col gap-0.5 flex-1">
                @foreach($navItems as $item)
                    @php
                        $isActive = $currentRoute === $item['route'] || ($item['slug'] === 'dashboard' && $currentRoute === 'dashboard');
                        $href = $item['route'] ? route($item['route']) : '#';
                    @endphp
                    <a href="{{ $href }}" wire:navigate
                       class="flex items-center gap-2.5 py-2.5 px-3 rounded-[10px] text-[13.5px] transition-all duration-200
                       {{ $isActive
                           ? 'bg-white/[0.06] dark:text-white text-black'
                           : 'dark:text-[#8b8b93] text-[#6b6b70] dark:hover:text-white hover:text-black' }}">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $isActive ? 'bg-profit' : 'bg-current opacity-50' }}"></span>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="pt-5 mt-5 border-t dark:border-white/[0.09] border-black/[0.08] text-[11px] dark:text-[#8b8b93] text-[#6b6b70]">
                TradeLedger v1.0<br>Personal Portfolio Manager
            </div>
        </aside>

        {{-- Main content area --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- Topbar --}}
            <header class="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-4 lg:py-5 flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <button @click="mobileNav = !mobileNav" class="lg:hidden p-2 -ml-2 rounded-lg dark:hover:bg-white/5 hover:bg-black/5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="font-display text-lg sm:text-xl font-semibold tracking-tight">
                        {{ $pageTitle ?? 'Dashboard' }}
                    </h1>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    @livewire('account-switcher')

                    <button @click="toggle()"
                            class="w-[48px] h-[26px] sm:w-[52px] sm:h-[28px] rounded-full border relative cursor-pointer transition-colors duration-300
                            dark:bg-white/[0.08] dark:border-white/[0.09] bg-black/[0.06] border-black/[0.08]">
                        <div class="w-[20px] h-[20px] sm:w-[22px] sm:h-[22px] rounded-full absolute top-[2px] left-[2px] transition-transform duration-300 flex items-center justify-center text-[11px]
                            dark:bg-[#f5f5f4] bg-[#0a0a0c]"
                             :class="$store.theme === 'light' ? 'translate-x-[24px] sm:translate-x-[24px]' : 'translate-x-0'">
                            <span x-text="$store.theme === 'dark' ? '☀' : '☾'"></span>
                        </div>
                    </button>
                </div>
            </header>

            {{-- Mobile sidebar overlay --}}
            <div x-show="mobileNav" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @click="mobileNav = false"
                 class="fixed inset-0 z-40 bg-black/60 lg:hidden" style="display:none"></div>

            {{-- Mobile sidebar drawer --}}
            <div x-show="mobileNav" x-transition:enter="transition-transform ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition-transform ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                 class="fixed inset-y-0 left-0 z-50 w-[240px] p-6 flex flex-col border-r backdrop-blur-xl lg:hidden
                 dark:border-white/[0.09] dark:bg-ink-2 border-black/[0.08] bg-white"
                 style="display:none">
                <div class="flex items-center gap-2.5 mb-9 pl-1">
                    <div class="w-9 h-9 rounded-[10px] bg-gradient-to-br from-profit to-profit-dim flex items-center justify-center font-display font-bold text-base text-[#04231a]">T</div>
                    <span class="font-display font-semibold text-[19px] tracking-tight dark:text-white text-black">TradeLedger</span>
                </div>
                <nav class="flex flex-col gap-0.5 flex-1">
                    @foreach($navItems as $item)
                        @php
                            $isActive = $currentRoute === $item['route'] || ($item['slug'] === 'dashboard' && $currentRoute === 'dashboard');
                            $href = $item['route'] ? route($item['route']) : '#';
                        @endphp
                        <a href="{{ $href }}" wire:navigate @click="mobileNav = false"
                           class="flex items-center gap-2.5 py-2.5 px-3 rounded-[10px] text-[13.5px] transition-all duration-200
                           {{ $isActive ? 'bg-white/[0.06] dark:text-white text-black' : 'dark:text-[#8b8b93] text-[#6b6b70] dark:hover:text-white hover:text-black' }}">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $isActive ? 'bg-profit' : 'bg-current opacity-50' }}"></span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- Page content --}}
            <main class="px-4 sm:px-6 lg:px-8 pb-24 lg:pb-8 flex-1">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Bottom Navigation Bar (mobile/tablet) --}}
    <nav class="fixed bottom-0 inset-x-0 z-50 lg:hidden border-t backdrop-blur-xl safe-area-bottom
        dark:border-white/[0.09] dark:bg-ink-2/90 border-black/[0.08] bg-white/90">
        <div class="flex items-stretch justify-around max-w-lg mx-auto">
            @php
                $bottomNavIcons = [
                    'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/>',
                    'daily-log' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
                    'target-rules' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>',
                    'deposit-withdrawal' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 10v1m-4-5h8"/>',
                    'analytics' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                    'journal' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
                ];
            @endphp
            @foreach($navItems as $item)
                @php
                    $isActive = $currentRoute === $item['route'] || ($item['slug'] === 'dashboard' && $currentRoute === 'dashboard');
                    $href = $item['route'] ? route($item['route']) : '#';
                @endphp
                <a href="{{ $href }}" wire:navigate
                   class="flex flex-col items-center justify-center gap-0.5 py-2 px-1 min-w-0 flex-1 transition-colors
                   {{ $isActive ? 'text-profit' : 'dark:text-[#8b8b93] text-[#6b6b70]' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $bottomNavIcons[$item['slug']] ?? '' !!}
                    </svg>
                    <span class="text-[9px] font-medium leading-tight truncate w-full text-center">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    <style>
        .bg-glow {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background:
                radial-gradient(600px 400px at 12% 8%, rgba(32,227,162,0.10), transparent 60%),
                radial-gradient(500px 380px at 90% 15%, rgba(255,84,112,0.08), transparent 60%),
                radial-gradient(700px 500px at 50% 100%, rgba(232,196,104,0.05), transparent 60%);
            transition: opacity 0.35s ease;
        }
        .light .bg-glow { opacity: 0.5; }

        .safe-area-bottom {
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }
    </style>
</body>
</html>
