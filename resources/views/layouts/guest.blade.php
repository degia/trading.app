<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TradeLedger') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-body antialiased dark"
      x-data="{
          theme: localStorage.getItem('theme') || 'dark',
          init() {
              this.applyTheme();
              this.$watch('theme', () => this.applyTheme());
          },
          applyTheme() {
              localStorage.setItem('theme', this.theme);
              document.documentElement.className = this.theme;
              document.body.className = this.theme === 'dark' ? 'font-body antialiased' : 'font-body antialiased light';
          },
          toggle() {
              this.theme = this.theme === 'dark' ? 'light' : 'dark';
          }
      }">

    <div class="bg-glow"></div>

    <div class="min-h-screen flex items-center justify-center p-6 relative z-10">
        <div class="w-full max-w-[420px] backdrop-blur-[28px] saturate-[140%] rounded-[20px] border p-10 transition-colors duration-300
            dark:bg-white/[0.045] dark:border-white/[0.09] dark:shadow-[0_20px_60px_rgba(0,0,0,0.4),inset_0_1px_0_rgba(255,255,255,0.06)]
            bg-white/[0.55] border-black/[0.08] shadow-[0_20px_60px_rgba(0,0,0,0.08),inset_0_1px_0_rgba(255,255,255,0.6)]">

            {{ $slot }}
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
