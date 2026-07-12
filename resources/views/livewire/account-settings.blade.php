<div x-data="{ open: false }" @keydown.escape.window="open = false">
    @if($showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-data>
            <div class="fixed inset-0 bg-black/60" wire:click="closeModal"></div>
            <div class="relative w-full max-w-md rounded-[20px] border p-6 z-10
                dark:bg-ink-2 dark:border-white/[0.09] bg-white border-black/[0.08] shadow-2xl max-h-[90vh] overflow-y-auto">

                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-display text-lg font-semibold">Account Settings</h3>
                    <button wire:click="closeModal" class="p-1.5 rounded-lg dark:hover:bg-white/[0.06] hover:bg-black/[0.04] transition-colors">
                        <svg class="w-4 h-4 text-[#8b8b93]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @if($saved)
                    <div x-data x-init="setTimeout(() => $wire.set('saved', false), 2500)"
                         class="mb-4 p-3 rounded-[10px] bg-profit/10 text-profit text-[12px] font-medium">
                        Settings saved successfully.
                    </div>
                @endif

                <form wire:submit="save">
                    <div class="space-y-4">
                        {{-- Account Name --}}
                        <div>
                            <label class="block text-[12px] uppercase tracking-[0.04em] text-[#8b8b93] mb-1.5 font-body">Account Name</label>
                            <input type="text" wire:model="formName"
                                   class="w-full px-3 py-2.5 rounded-[9px] text-[13px] font-mono border outline-none transition-colors
                                   dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-[#f5f5f4] focus:border-profit/50
                                   bg-black/[0.03] border-black/[0.08] text-[#0a0a0c]"
                                   placeholder="Account name">
                            @error('formName') <span class="text-[11px] text-loss mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Initial Balance --}}
                        <div>
                            <label class="block text-[12px] uppercase tracking-[0.04em] text-[#8b8b93] mb-1.5 font-body">Initial Balance ($)</label>
                            <input type="number" step="0.01" min="0" wire:model="formInitialBalance"
                                   class="w-full px-3 py-2.5 rounded-[9px] text-[13px] font-mono border outline-none transition-colors
                                   dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-[#f5f5f4] focus:border-profit/50
                                   bg-black/[0.03] border-black/[0.08] text-[#0a0a0c]"
                                   placeholder="0.00">
                            <p class="text-[11px] dark:text-[#8b8b93] text-[#6b6b70] mt-1">Base balance for target calculations. Changing this triggers full recalculation.</p>
                            @error('formInitialBalance') <span class="text-[11px] text-loss mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Current Balance --}}
                        <div>
                            <label class="block text-[12px] uppercase tracking-[0.04em] text-[#8b8b93] mb-1.5 font-body">Current Balance ($)</label>
                            <div class="flex gap-2">
                                <input type="number" step="0.01" wire:model="formCurrentBalance"
                                       class="flex-1 px-3 py-2.5 rounded-[9px] text-[13px] font-mono border outline-none transition-colors
                                       dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-[#f5f5f4] focus:border-profit/50
                                       bg-black/[0.03] border-black/[0.08] text-[#0a0a0c]"
                                       placeholder="0.00">
                                <button type="button" wire:click="recalculateBalance"
                                        class="px-3 py-2.5 rounded-[9px] text-[12px] font-medium border transition-all shrink-0
                                        dark:border-white/[0.09] dark:text-[#8b8b93] dark:hover:text-white dark:hover:bg-white/[0.04]
                                        border-black/[0.08] text-[#6b6b70] hover:text-black hover:bg-black/[0.04]"
                                        title="Recalculate from transactions & daily logs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-[11px] dark:text-[#8b8b93] text-[#6b6b70] mt-1">Auto-calculated from deposits, withdrawals & daily P/L. Click refresh to recalculate.</p>
                            @error('formCurrentBalance') <span class="text-[11px] text-loss mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Account Type (read-only) --}}
                        <div>
                            <label class="block text-[12px] uppercase tracking-[0.04em] text-[#8b8b93] mb-1.5 font-body">Account Type</label>
                            <div class="px-3 py-2.5 rounded-[9px] text-[13px] font-mono border
                                dark:bg-white/[0.02] dark:border-white/[0.06] dark:text-[#8b8b93]
                                bg-black/[0.02] border-black/[0.04] text-[#6b6b70]">
                                {{ ucfirst($this->activeAccount?->type ?? '—') }}
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-6">
                        <button type="button" wire:click="closeModal"
                                class="flex-1 py-2.5 rounded-[10px] text-[13px] font-medium border transition-all
                                dark:border-white/[0.09] dark:text-[#8b8b93] dark:hover:text-white border-black/[0.08] text-[#6b6b70] hover:text-ink">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 py-2.5 rounded-[10px] text-[13px] font-semibold transition-all
                                bg-profit text-[#04231a] hover:brightness-110">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
