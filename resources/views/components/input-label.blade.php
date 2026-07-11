@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mb-1.5 tracking-wide uppercase font-body']) }}>
    {{ $value ?? $slot }}
</label>
