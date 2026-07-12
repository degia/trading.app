<div x-data="{ open: false }" @click.away="open = false" @keydown.escape.window="open = false" class="relative">
    <button @click="open = !open"
            class="flex items-center gap-1.5 px-2 sm:px-3.5 py-[7px] rounded-full text-xs font-medium border transition-all duration-200
            {{ $this->activeType === 'real'
                ? 'bg-profit/10 text-profit border-profit/25'
                : 'bg-target/10 text-target border-target/25' }}">
        <span class="w-1.5 h-1.5 rounded-full shrink-0 shadow-[0_0_0_3px_rgba(32,227,162,0.2)]
            {{ $this->activeType === 'real' ? 'bg-profit' : 'bg-target' }}"></span>
        <span class="hidden sm:inline">{{ $this->activeType === 'real' ? 'Real Account' : 'Demo Account' }}</span>
        <svg class="w-3.5 h-3.5 sm:ml-0.5 transition-transform duration-200 shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 top-full mt-2 w-56 rounded-[14px] border z-50 overflow-hidden
         dark:bg-ink-2 dark:border-white/[0.09] bg-white border-black/[0.08] shadow-xl"
         style="display:none">

        <div class="p-1.5">
            @foreach($this->accounts as $account)
                <button wire:click="promptSwitch('{{ $account->type }}')"
                        @click="open = false"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-[9px] text-left transition-all duration-150
                        {{ $account->type === $this->activeType
                            ? 'bg-white/[0.06]'
                            : 'hover:bg-white/[0.04]' }}">
                    <span class="w-2 h-2 rounded-full shrink-0 {{ $account->type === 'real' ? 'bg-profit' : 'bg-target' }}"></span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[13px] font-medium truncate {{ $account->type === $this->activeType ? 'text-white' : 'dark:text-[#8b8b93] text-[#6b6b70]' }}">
                            {{ $account->name }}
                        </div>
                        <div class="text-[11px] font-mono {{ $account->type === $this->activeType ? 'text-white/60' : 'dark:text-[#8b8b93]/60 text-[#6b6b70]/60' }}">
                            ${{ number_format($account->current_balance, 2) }}
                        </div>
                    </div>
                    @if($account->type === $this->activeType)
                        <svg class="w-4 h-4 text-profit shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-data>
            <div class="fixed inset-0 bg-black/60" wire:click="cancelSwitch"></div>
            <div class="relative w-full max-w-sm rounded-[20px] border p-6 z-10
                dark:bg-ink-2 dark:border-white/[0.09] bg-white border-black/[0.08] shadow-2xl">
                <h3 class="font-display text-lg font-semibold mb-2">Switch Account?</h3>
                <p class="text-sm dark:text-[#8b8b93] text-[#6b6b70] mb-5">
                    Kamu akan beralih ke
                    <span class="font-medium {{ $pendingType === 'real' ? 'text-profit' : 'text-target' }}">
                        {{ $pendingType === 'real' ? 'Real Account' : 'Demo Account' }}
                    </span>.
                    Semua data yang ditampilkan akan berbeda sesuai akun yang aktif.
                </p>
                <div class="flex gap-2">
                    <button wire:click="cancelSwitch"
                            class="flex-1 py-2.5 rounded-[10px] text-[13px] font-medium border transition-all
                            dark:border-white/[0.09] dark:text-[#8b8b93] dark:hover:text-white border-black/[0.08] text-[#6b6b70] hover:text-ink">
                        Batal
                    </button>
                    <button wire:click="confirmSwitch"
                            class="flex-1 py-2.5 rounded-[10px] text-[13px] font-medium border-0 transition-all
                            bg-white text-ink hover:opacity-80 dark:bg-white dark:text-ink light:bg-ink light:text-white">
                        Switch
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
