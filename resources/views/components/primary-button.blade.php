<button {{ $attributes->merge(['type' => 'submit', 'class' => 'w-full py-3.5 rounded-[10px] border-0 mt-2 bg-white text-ink font-display font-semibold text-sm cursor-pointer transition-opacity hover:opacity-[0.88] active:scale-[0.98] dark:bg-white dark:text-ink light:bg-ink light:text-white']) }}>
    {{ $slot }}
</button>
