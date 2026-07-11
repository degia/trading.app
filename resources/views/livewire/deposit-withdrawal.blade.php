<div class="py-6 space-y-5">

    @if(session('error'))
        <div class="mb-2 p-3 rounded-[10px] bg-loss/10 text-loss text-sm font-body">{{ session('error') }}</div>
    @endif

    {{-- Header + Actions --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="font-display text-2xl font-bold tracking-tight">Deposit & Withdrawal</h2>
            <p class="mt-1 text-sm dark:text-[#8b8b93] text-[#6b6b70]">
                Kelola dana untuk
                <span class="font-semibold text-warn">{{ $this->activeAccount->name ?? '—' }}</span>
                &middot; Saldo:
                <span class="font-mono font-semibold">${{ number_format((float) ($this->activeAccount->current_balance ?? 0), 2) }}</span>
            </p>
        </div>
        <div class="flex gap-2">
            <button wire:click="openModal('deposit')"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-[9px] text-[12px] font-semibold bg-profit text-[#04231a] hover:brightness-110 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Deposit
            </button>
            <button wire:click="openModal('withdrawal')"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-[9px] text-[12px] font-semibold bg-loss/15 text-loss border border-loss/20 hover:bg-loss/25 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                Withdraw
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
        <div class="glass-card p-5">
            <div class="text-[11.5px] uppercase tracking-[0.06em] text-[#8b8b93] font-body mb-2">Total Deposit</div>
            <div class="font-mono text-[22px] font-semibold tracking-tight num-pos">${{ number_format($this->totalDeposit, 2) }}</div>
            <div class="text-[11px] text-[#8b8b93] mt-1">{{ $this->selectedMonthLabel }}</div>
        </div>
        <div class="glass-card p-5">
            <div class="text-[11.5px] uppercase tracking-[0.06em] text-[#8b8b93] font-body mb-2">Total Withdrawal</div>
            <div class="font-mono text-[22px] font-semibold tracking-tight num-neg">${{ number_format($this->totalWithdrawal, 2) }}</div>
            <div class="text-[11px] text-[#8b8b93] mt-1">{{ $this->selectedMonthLabel }}</div>
        </div>
        <div class="glass-card p-5">
            <div class="text-[11.5px] uppercase tracking-[0.06em] text-[#8b8b93] font-body mb-2">Net Flow</div>
            <div class="font-mono text-[22px] font-semibold tracking-tight {{ ($this->totalDeposit - $this->totalWithdrawal) >= 0 ? 'num-pos' : 'num-neg' }}">
                @if(($this->totalDeposit - $this->totalWithdrawal) < 0) -@endif${{ number_format(abs($this->totalDeposit - $this->totalWithdrawal), 2) }}
            </div>
            <div class="text-[11px] text-[#8b8b93] mt-1">{{ $this->selectedMonthLabel }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex items-center gap-2">
        @if(count($this->monthOptions) > 0)
            <select wire:model.live="selectedMonth"
                    class="bg-white/[0.06] dark:bg-white/[0.06] border border-white/[0.09] dark:border-white/[0.09] rounded-[9px] px-3 py-2 text-[12px] font-mono text-[#8b8b93] dark:text-[#8b8b93] outline-none focus:border-profit/40 transition-colors cursor-pointer">
                @foreach($this->monthOptions as $key => $label)
                    <option value="{{ $key }}" class="bg-[#141418] text-[#f5f5f4]">{{ $label }}</option>
                @endforeach
            </select>
        @endif

        <select wire:model.live="selectedType"
                class="bg-white/[0.06] dark:bg-white/[0.06] border border-white/[0.09] dark:border-white/[0.09] rounded-[9px] px-3 py-2 text-[12px] font-mono text-[#8b8b93] dark:text-[#8b8b93] outline-none focus:border-profit/40 transition-colors cursor-pointer">
            <option value="all" class="bg-[#141418] text-[#f5f5f4]">Semua</option>
            <option value="deposit" class="bg-[#141418] text-[#f5f5f4]">Deposit</option>
            <option value="withdrawal" class="bg-[#141418] text-[#f5f5f4]">Withdrawal</option>
        </select>
    </div>

    {{-- Transactions Table --}}
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[13px] min-w-[560px]">
                <thead>
                    <tr>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Tanggal</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Tipe</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Jumlah</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Catatan</th>
                        <th class="text-right py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->transactions as $txn)
                        <tr class="border-b dark:border-white/[0.04] border-black/[0.04] dark:hover:bg-white/[0.02] hover:bg-black/[0.015] transition-colors">
                            <td class="py-3 px-5 font-mono text-[12px]">{{ $txn->transaction_date->format('d/m/Y') }}</td>
                            <td class="py-3 px-5">
                                @if($txn->type === 'deposit')
                                    <span class="px-2.5 py-1 rounded-full text-[10.5px] font-medium bg-profit/10 text-profit">Deposit</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10.5px] font-medium bg-loss/10 text-loss">Withdrawal</span>
                                @endif
                            </td>
                            <td class="py-3 px-5 font-mono text-[12px] font-semibold {{ $txn->type === 'deposit' ? 'num-pos' : 'num-neg' }}">
                                {{ $txn->type === 'deposit' ? '+' : '-' }}${{ number_format((float) $txn->amount, 2) }}
                            </td>
                            <td class="py-3 px-5 text-[12px] dark:text-[#8b8b93] text-[#6b6b70] max-w-[200px] truncate">{{ $txn->notes ?? '—' }}</td>
                            <td class="py-3 px-5 text-right">
                                <button wire:click="confirmDelete({{ $txn->id }})"
                                        class="p-1.5 rounded-lg dark:hover:bg-white/[0.06] hover:bg-black/[0.04] transition-colors text-[#8b8b93] hover:text-loss">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-5 text-center text-[#8b8b93] font-body">
                                Belum ada transaksi untuk bulan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($this->transactions->hasPages())
            <div class="px-5 py-3 border-t dark:border-white/[0.06] border-black/[0.04]">
                {{ $this->transactions->links() }}
            </div>
        @endif
    </div>

    {{-- Create Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-data x-init="$nextTick(() => $refs.modalAmount.focus())">
            <div class="fixed inset-0 bg-black/60" wire:click="closeModal"></div>
            <div class="relative w-full max-w-md rounded-[20px] border p-6 z-10
                dark:bg-ink-2 dark:border-white/[0.09] bg-white border-black/[0.08] shadow-2xl max-h-[90vh] overflow-y-auto">

                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-display text-lg font-semibold">
                        {{ $formType === 'deposit' ? 'Tambah Deposit' : 'Tambah Withdrawal' }}
                    </h3>
                    <button wire:click="closeModal" class="p-1.5 rounded-lg dark:hover:bg-white/[0.06] hover:bg-black/[0.04] transition-colors">
                        <svg class="w-4 h-4 text-[#8b8b93]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="save">
                    <div class="space-y-4">
                        {{-- Type Toggle --}}
                        <div>
                            <label class="block text-[12px] uppercase tracking-[0.04em] text-[#8b8b93] mb-1.5 font-body">Tipe</label>
                            <div class="flex bg-white/[0.04] dark:bg-white/[0.04] border border-white/[0.09] dark:border-white/[0.09] rounded-xl p-1 gap-1">
                                <button type="button" wire:click="$set('formType', 'deposit')"
                                        class="flex-1 text-center py-2 rounded-[9px] text-[12px] font-medium transition-all
                                        {{ $formType === 'deposit' ? 'bg-profit/14 text-profit' : 'text-[#8b8b93] dark:hover:text-white hover:text-ink' }}">
                                    Deposit
                                </button>
                                <button type="button" wire:click="$set('formType', 'withdrawal')"
                                        class="flex-1 text-center py-2 rounded-[9px] text-[12px] font-medium transition-all
                                        {{ $formType === 'withdrawal' ? 'bg-loss/14 text-loss' : 'text-[#8b8b93] dark:hover:text-white hover:text-ink' }}">
                                    Withdrawal
                                </button>
                            </div>
                        </div>

                        {{-- Amount --}}
                        <div>
                            <label class="block text-[12px] uppercase tracking-[0.04em] text-[#8b8b93] mb-1.5 font-body">Jumlah ($)</label>
                            <input type="number" step="0.01" min="0.01" wire:model="formAmount" x-ref="modalAmount"
                                   class="w-full px-3 py-2.5 rounded-[9px] text-[13px] font-mono border outline-none transition-colors
                                   dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-[#f5f5f4] focus:border-profit/50
                                   bg-black/[0.03] border-black/[0.08] text-[#0a0a0c]"
                                   placeholder="0.00">
                            @error('formAmount') <span class="text-[11px] text-loss mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Date --}}
                        <div>
                            <label class="block text-[12px] uppercase tracking-[0.04em] text-[#8b8b93] mb-1.5 font-body">Tanggal</label>
                            <input type="date" wire:model="formDate"
                                   class="w-full px-3 py-2.5 rounded-[9px] text-[13px] font-mono border outline-none transition-colors
                                   dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-[#f5f5f4] focus:border-profit/50
                                   bg-black/[0.03] border-black/[0.08] text-[#0a0a0c]">
                            @error('formDate') <span class="text-[11px] text-loss mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label class="block text-[12px] uppercase tracking-[0.04em] text-[#8b8b93] mb-1.5 font-body">Catatan (opsional)</label>
                            <textarea wire:model="formNotes" rows="2"
                                      class="w-full px-3 py-2.5 rounded-[9px] text-[13px] border outline-none transition-colors resize-none
                                      dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-[#f5f5f4] focus:border-profit/50
                                      bg-black/[0.03] border-black/[0.08] text-[#0a0a0c]"
                                      placeholder="Catatan transaksi..."></textarea>
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
                                {{ $formType === 'deposit' ? 'bg-profit text-[#04231a] hover:brightness-110' : 'bg-loss text-white hover:opacity-90' }}">
                            {{ $formType === 'deposit' ? 'Deposit' : 'Withdraw' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation --}}
    @if($showDeleteConfirm)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-data>
            <div class="fixed inset-0 bg-black/60" wire:click="cancelDelete"></div>
            <div class="relative w-full max-w-sm rounded-[20px] border p-6 z-10
                dark:bg-ink-2 dark:border-white/[0.09] bg-white border-black/[0.08] shadow-2xl">
                <h3 class="font-display text-lg font-semibold mb-2">Hapus Transaksi?</h3>
                <p class="text-sm dark:text-[#8b8b93] text-[#6b6b70] mb-5">
                    Transaksi ini akan dihapus permanen. Saldo akun akan dikembalikan sesuai jumlah transaksi.
                </p>
                <div class="flex gap-2">
                    <button wire:click="cancelDelete"
                            class="flex-1 py-2.5 rounded-[10px] text-[13px] font-medium border transition-all
                            dark:border-white/[0.09] dark:text-[#8b8b93] dark:hover:text-white border-black/[0.08] text-[#6b6b70] hover:text-ink">
                        Batal
                    </button>
                    <button wire:click="deleteEntry"
                            class="flex-1 py-2.5 rounded-[10px] text-[13px] font-medium border-0 transition-all bg-loss text-white hover:opacity-80">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
