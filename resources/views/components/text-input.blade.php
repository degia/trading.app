@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full px-3.5 py-3 rounded-[10px] text-sm bg-white/[0.04] border border-white/[0.09] text-white font-body outline-none transition-colors focus:border-profit dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-white light:bg-white/70 light:border-black/[0.08] light:text-ink']) }}>
