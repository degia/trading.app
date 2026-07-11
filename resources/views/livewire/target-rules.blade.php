<div class="max-w-2xl mx-auto space-y-8">

    {{-- Header --}}
    <div>
        <h2 class="font-display text-2xl font-bold tracking-tight">Target & Rules</h2>
        <p class="mt-1 text-sm dark:text-[#8b8b93] text-[#6b6b70]">
            Configure target percentages and off-day rules for
            <span class="font-semibold text-warn">{{ $this->activeAccount->name ?? '—' }}</span>
        </p>
    </div>

    {{-- Target Percentages --}}
    <div class="glass-card p-6 space-y-6">
        <div class="flex items-center gap-2.5 mb-2">
            <div class="w-8 h-8 rounded-lg bg-profit/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-profit" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <h3 class="font-display text-base font-semibold">Target Percentages</h3>
        </div>

        <p class="text-xs dark:text-[#8b8b93] text-[#6b6b70]">
            Percentage of balance used to calculate daily target amounts. Changes trigger full recalculation.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-xs font-medium uppercase tracking-wider dark:text-[#8b8b93] text-[#6b6b70]">
                    Target 1 (Running)
                </label>
                <div class="flex items-center gap-3">
                    <input type="number" min="1" max="100" wire:model.live="target_1_pct"
                           class="w-24 px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm font-mono
                           focus:border-profit focus:ring-1 focus:ring-profit/30 outline-none transition-all">
                    <span class="text-sm dark:text-[#8b8b93] text-[#6b6b70]">% of balance</span>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-medium uppercase tracking-wider dark:text-[#8b8b93] text-[#6b6b70]">
                    Target 2 (Aggressive)
                </label>
                <div class="flex items-center gap-3">
                    <input type="number" min="1" max="100" wire:model.live="target_2_pct"
                           class="w-24 px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm font-mono
                           focus:border-profit focus:ring-1 focus:ring-profit/30 outline-none transition-all">
                    <span class="text-sm dark:text-[#8b8b93] text-[#6b6b70]">% of balance</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Off Days --}}
    <div class="glass-card p-6 space-y-5">
        <div class="flex items-center gap-2.5 mb-2">
            <div class="w-8 h-8 rounded-lg bg-warn/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-warn" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="font-display text-base font-semibold">Off Days</h3>
        </div>

        <p class="text-xs dark:text-[#8b8b93] text-[#6b6b70]">
            Days marked as off days reset running targets to zero.
        </p>

        <div class="flex flex-wrap gap-2">
            @foreach($this->allDays() as $day)
                <button wire:click="toggleOffDay('{{ $day }}')"
                        class="px-4 py-2 rounded-xl text-xs font-semibold capitalize border transition-all duration-200
                        {{ in_array($day, $offDays)
                            ? 'bg-warn/15 border-warn/30 text-warn'
                            : 'bg-white/5 border-white/10 dark:text-[#8b8b93] text-[#6b6b70] hover:text-white' }}">
                    {{ $day }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Save --}}
    <div class="flex items-center gap-4">
        <button wire:click="save" wire:loading.attr="disabled"
                class="px-6 py-2.5 rounded-xl bg-profit text-[#04231a] text-sm font-semibold transition-all hover:brightness-110 disabled:opacity-50">
            <span wire:loading.remove wire:target="save">Save & Recalculate</span>
            <span wire:loading wire:target="save">Saving…</span>
        </button>

        @if($showSaved)
            <span x-data x-init="setTimeout(() => $el.remove(), 2000)" class="text-profit text-sm font-medium">
                Saved & all targets recalculated.
            </span>
        @endif
    </div>

</div>
