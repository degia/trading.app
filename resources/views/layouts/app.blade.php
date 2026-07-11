<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
                @php
                    $navItems = [
                        ['label' => 'Overview', 'route' => 'dashboard', 'slug' => 'dashboard'],
                        ['label' => 'Daily Log', 'route' => 'daily-log', 'slug' => 'daily-log'],
                        ['label' => 'Target & Rules', 'route' => 'target-rules', 'slug' => 'target-rules'],
                        ['label' => 'Deposit / WD', 'route' => 'deposit-withdrawal', 'slug' => 'deposit-withdrawal'],
                        ['label' => 'Analytics', 'route' => 'analytics', 'slug' => 'analytics'],
                        ['label' => 'Journal', 'route' => null, 'slug' => 'journal'],
                        ['label' => 'Pengaturan', 'route' => null, 'slug' => 'pengaturan'],
                    ];
                    $currentRoute = request()->route()->getName() ?? '';
                @endphp

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
            <header class="flex items-center justify-between px-6 lg:px-8 py-5 flex-wrap gap-3.5">
                <div class="flex items-center gap-3">
                    <button @click="mobileNav = !mobileNav" class="lg:hidden p-2 -ml-2 rounded-lg dark:hover:bg-white/5 hover:bg-black/5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="font-display text-xl font-semibold tracking-tight">
                        {{ $pageTitle ?? 'Dashboard' }}
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    @livewire('account-switcher')

                    <button @click="toggle()"
                            class="w-[52px] h-[28px] rounded-full border relative cursor-pointer transition-colors duration-300
                            dark:bg-white/[0.08] dark:border-white/[0.09] bg-black/[0.06] border-black/[0.08]">
                        <div class="w-[22px] h-[22px] rounded-full absolute top-[2px] left-[2px] transition-transform duration-300 flex items-center justify-center text-[11px]
                            dark:bg-[#f5f5f4] bg-[#0a0a0c]"
                             :class="$store.theme === 'light' ? 'translate-x-[24px]' : 'translate-x-0'">
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
                 class="fixed inset-y-0 left-0 z-50 w-[220px] p-6 flex flex-col border-r border-white/[0.09] backdrop-blur-xl bg-ink lg:hidden"
                 style="display:none">
                <div class="flex items-center gap-2.5 mb-9 pl-1">
                    <div class="w-9 h-9 rounded-[10px] bg-gradient-to-br from-profit to-profit-dim flex items-center justify-center font-display font-bold text-base text-[#04231a]">T</div>
                    <span class="font-display font-semibold text-[19px] tracking-tight text-white">TradeLedger</span>
                </div>
                <nav class="flex flex-col gap-0.5 flex-1">
                    @foreach($navItems as $item)
                        @php
                            $isActive = $currentRoute === $item['route'] || ($item['slug'] === 'dashboard' && $currentRoute === 'dashboard');
                            $href = $item['route'] ? route($item['route']) : '#';
                        @endphp
                        <a href="{{ $href }}" wire:navigate @click="mobileNav = false"
                           class="flex items-center gap-2.5 py-2.5 px-3 rounded-[10px] text-[13.5px] transition-all duration-200
                           {{ $isActive ? 'bg-white/[0.06] text-white' : 'text-[#8b8b93] hover:text-white' }}">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $isActive ? 'bg-profit' : 'bg-current opacity-50' }}"></span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- Page content --}}
            <main class="px-6 lg:px-8 pb-24 lg:pb-8 flex-1">
                {{ $slot }}
            </main>
        </div>
    </div>

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
    </style>
</body>
</html>
