<div class="py-6">
    @if(session('error'))
        <div class="mb-4 p-3 rounded-[10px] bg-loss/10 text-loss text-sm font-body">{{ session('error') }}</div>
    @endif

    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <select wire:model.live="selectedMonth"
                    class="bg-white/[0.06] dark:bg-white/[0.06] border border-white/[0.09] dark:border-white/[0.09] rounded-[9px] px-3 py-2 text-[12px] font-mono text-[#8b8b93] dark:text-[#8b8b93] outline-none focus:border-profit/40 transition-colors cursor-pointer">
                @foreach($this->monthOptions as $key => $label)
                    <option value="{{ $key }}" class="bg-[#141418] text-[#f5f5f4]">{{ $label }}</option>
                @endforeach
            </select>

            <select wire:model.live="selectedStatus"
                    class="bg-white/[0.06] dark:bg-white/[0.06] border border-white/[0.09] dark:border-white/[0.09] rounded-[9px] px-3 py-2 text-[12px] font-mono text-[#8b8b93] dark:text-[#8b8b93] outline-none focus:border-profit/40 transition-colors cursor-pointer">
                <option value="all" class="bg-[#141418] text-[#f5f5f4]">Semua Status</option>
                <option value="profit" class="bg-[#141418] text-[#f5f5f4]">Profit</option>
                <option value="loss" class="bg-[#141418] text-[#f5f5f4]">Loss</option>
                <option value="day_off" class="bg-[#141418] text-[#f5f5f4]">Day Off</option>
            </select>
        </div>

        <div class="flex items-center gap-1" x-data="{
            calcA: '',
            calcB: '',
            calcOp: '+',
            calcResult: '',
            calcRun() {
                const a = parseFloat(this.calcA), b = parseFloat(this.calcB);
                if (isNaN(a) || isNaN(b)) { this.calcResult = '—'; return; }
                let r;
                switch (this.calcOp) {
                    case '+': r = a + b; break;
                    case '-': r = a - b; break;
                    case 'x': r = a * b; break;
                    case '/': r = b === 0 ? 'Error' : a / b; break;
                }
                this.calcResult = r === 'Error' ? 'Error' : parseFloat(r.toFixed(10)).toString();
            },
            useResult() {
                if (!this.calcResult || this.calcResult === 'Error' || this.calcResult === '—') return;
                $wire.set('formProfitAmount', parseFloat(this.calcResult) || 0);
                $wire.set('formLossAmount', parseFloat(this.calcResult) || 0);
            }
        }" @keydown.enter="calcRun()">
            <input type="number" step="any" x-model="calcA" placeholder="0"
                   class="w-14 px-1.5 py-1.5 rounded-lg text-[11px] font-mono border text-center outline-none transition-colors
                   dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-[#f5f5f4] focus:border-profit/50
                   bg-black/[0.03] border-black/[0.08] text-[#0a0a0c]">

            <select x-model="calcOp"
                    class="w-8 px-0.5 py-1.5 rounded-lg text-[11px] font-mono border text-center outline-none cursor-pointer transition-colors
                    dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-profit
                    bg-black/[0.03] border-black/[0.08] text-profit">
                <option value="+">+</option>
                <option value="-">−</option>
                <option value="x">×</option>
                <option value="/">÷</option>
            </select>

            <input type="number" step="any" x-model="calcB" placeholder="0"
                   class="w-14 px-1.5 py-1.5 rounded-lg text-[11px] font-mono border text-center outline-none transition-colors
                   dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-[#f5f5f4] focus:border-profit/50
                   bg-black/[0.03] border-black/[0.08] text-[#0a0a0c]">

            <button @click="calcRun()"
                    class="w-7 h-[30px] flex items-center justify-center rounded-lg text-[11px] font-bold transition-colors
                    dark:bg-profit/15 dark:hover:bg-profit/25 dark:text-profit
                    bg-profit/10 hover:bg-profit/20 text-profit" title="Hitung">
                =
            </button>

            <div class="px-2 h-[30px] flex items-center rounded-lg text-[11px] font-mono font-semibold min-w-[48px] justify-center truncate
                dark:bg-white/[0.06] dark:text-[#f5f5f4]
                bg-black/[0.04] text-[#0a0a0c]"
                x-text="calcResult || '—'" :class="{
                    'dark:text-profit text-profit': calcResult && calcResult !== 'Error' && calcResult !== '—' && parseFloat(calcResult) > 0,
                    'dark:text-loss text-loss': calcResult === 'Error' || (calcResult && parseFloat(calcResult) < 0),
                    'dark:text-[#8b8b93] text-[#6b6b70]': !calcResult || calcResult === '—'
                }">
            </div>

            <button @click="useResult()"
                    x-show="calcResult && calcResult !== 'Error' && calcResult !== '—'"
                    class="w-7 h-[30px] flex items-center justify-center rounded-lg transition-colors
                    dark:bg-profit dark:hover:brightness-110 dark:text-[#04231a]
                    bg-profit hover:brightness-110 text-[#04231a]" title="Gunakan hasil">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </button>
        </div>

        <button wire:click="openCreateModal"
                class="flex items-center gap-1.5 px-4 py-2 rounded-[9px] text-[12px] font-medium bg-white text-ink dark:bg-white dark:text-ink light:bg-ink light:text-white hover:opacity-80 transition-opacity">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Entri
            </button>
        </div>

    {{-- Bulk delete bar --}}
    @if(count($selectedIds) > 0)
        <div class="flex items-center justify-between px-4 py-2.5 rounded-[10px] bg-loss/8 border border-loss/15">
            <span class="text-[12px] font-medium text-loss">{{ count($selectedIds) }} entri dipilih</span>
            <button wire:click="confirmBulkDelete"
                    class="px-3.5 py-1.5 rounded-lg text-[11px] font-semibold bg-loss text-white hover:opacity-80 transition-opacity">
                Hapus Terpilih
            </button>
        </div>
    @endif

    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[13px] min-w-[900px]">
                <thead>
                    <tr>
                        <th class="py-2.5 px-3 border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium w-[40px]">
                            <input type="checkbox" wire:click="toggleAll" @checked($this->isAllSelected)
                                   class="w-3.5 h-3.5 rounded border-white/20 dark:bg-white/[0.06] bg-black/[0.06] text-profit focus:ring-profit/30 cursor-pointer">
                        </th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Status</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Day</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Tanggal</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Balance</th>
                        <th class="text-right py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Daily %</th>
                        <th class="text-right py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Running {{ $this->rules->target_1_pct }}%</th>
                        <th class="text-right py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Closing {{ $this->rules->target_1_pct }}%</th>
                        <th class="text-right py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Running {{ $this->rules->target_2_pct }}%</th>
                        <th class="text-right py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Closing {{ $this->rules->target_2_pct }}%</th>
                        <th class="text-right py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Profit</th>
                        <th class="text-right py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Loss</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Notes</th>
                        <th class="text-right py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->logs as $log)
                        @php
                            $target5 = $log->targets->firstWhere('target_type', 'target_1');
                            $target10 = $log->targets->firstWhere('target_type', 'target_2');
                            $running5 = $target5 ? (float) $target5->running_amount : 0;
                            $running10 = $target10 ? (float) $target10->running_amount : 0;
                            $closing5 = $target5 ? (float) $target5->target_closing : 0;
                            $closing10 = $target10 ? (float) $target10->target_closing : 0;
                            $dailyPct = (float) $log->daily_percent;
                        @endphp
                        <tr class="border-b dark:border-white/[0.04] border-black/[0.04] dark:hover:bg-white/[0.02] hover:bg-black/[0.015] transition-colors">
                            <td class="py-3 px-3">
                                <input type="checkbox" wire:click="toggleSelect({{ $log->id }})" @checked(in_array($log->id, $selectedIds))
                                       class="w-3.5 h-3.5 rounded border-white/20 dark:bg-white/[0.06] bg-black/[0.06] text-profit focus:ring-profit/30 cursor-pointer">
                            </td>
                            <td class="py-3 px-5">
                                @if($log->status === 'profit')
                                    <span class="status-chip px-2.5 py-1 rounded-full text-[10.5px] font-medium bg-profit/10 text-profit">Profit</span>
                                @elseif($log->status === 'loss')
                                    <span class="status-chip px-2.5 py-1 rounded-full text-[10.5px] font-medium bg-loss/10 text-loss">Loss</span>
                                @else
                                    <span class="status-chip px-2.5 py-1 rounded-full text-[10.5px] font-medium bg-white/[0.06] dark:bg-white/[0.06] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70]">Day off</span>
                                @endif
                            </td>
                            <td class="py-3 px-5 text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] text-[12px]">{{ $log->day_name }}</td>
                            <td class="py-3 px-5 font-mono text-[12px]">{{ $log->log_date->format('d/m/Y') }}</td>
                            <td class="py-3 px-5 font-mono font-medium">${{ number_format((float) $log->balance, 2) }}</td>
                            <td class="py-3 px-5 font-mono text-[12px] text-right {{ $dailyPct >= 0 ? 'num-pos' : 'num-neg' }}">
                                @if($log->status !== 'day_off'){{ number_format($dailyPct, 2) }}%@else—@endif
                            </td>
                            <td class="py-3 px-5 font-mono text-[12px] text-right {{ $running5 >= 0 ? 'num-pos' : 'num-neg' }}">
                                @if($log->status !== 'day_off')${{ number_format($running5, 2) }}@else—@endif
                            </td>
                            <td class="py-3 px-5 font-mono text-[12px] text-right {{ $closing5 > 0 ? 'text-target dark:text-target' : 'num-pos' }}">
                                @if($log->status !== 'day_off')${{ number_format($closing5, 2) }}@else—@endif
                            </td>
                            <td class="py-3 px-5 font-mono text-[12px] text-right {{ $running10 >= 0 ? 'num-pos' : 'num-neg' }}">
                                @if($log->status !== 'day_off')${{ number_format($running10, 2) }}@else—@endif
                            </td>
                            <td class="py-3 px-5 font-mono text-[12px] text-right {{ $closing10 > 0 ? 'text-target dark:text-target' : 'num-pos' }}">
                                @if($log->status !== 'day_off')${{ number_format($closing10, 2) }}@else—@endif
                            </td>
                            <td class="py-3 px-5 font-mono text-[12px] text-right num-pos">
                                @if((float) $log->profit_amount > 0)${{ number_format((float) $log->profit_amount, 2) }}@else—@endif
                            </td>
                            <td class="py-3 px-5 font-mono text-[12px] text-right num-neg">
                                @if((float) $log->loss_amount > 0)${{ number_format((float) $log->loss_amount, 2) }}@else—@endif
                            </td>
                            <td class="py-3 px-5 text-[11px] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] max-w-[150px] truncate" @if($log->notes) title="{{ $log->notes }}" @endif>
                                {{ $log->notes ?? '—' }}
                            </td>
                            <td class="py-3 px-5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openEditModal({{ $log->id }})"
                                            class="p-1.5 rounded-lg dark:hover:bg-white/[0.06] hover:bg-black/[0.04] transition-colors text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] hover:text-white dark:hover:text-white hover:text-ink">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $log->id }})"
                                            class="p-1.5 rounded-lg dark:hover:bg-white/[0.06] hover:bg-black/[0.04] transition-colors text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] hover:text-loss">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="py-12 px-5 text-center text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-body">
                                Belum ada data trading untuk bulan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->logs->hasPages())
            <div class="px-5 py-3 border-t dark:border-white/[0.06] border-black/[0.04]">
                {{ $this->logs->links() }}
            </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-data x-init="$nextTick(() => $refs.modalForm.focus())">
            <div class="fixed inset-0 bg-black/60" wire:click="closeModal"></div>
            <div class="relative w-full max-w-lg rounded-[20px] border p-6 z-10
                dark:bg-ink-2 dark:border-white/[0.09] bg-white border-black/[0.08] shadow-2xl max-h-[90vh] overflow-y-auto">

                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-display text-lg font-semibold">{{ $isEditing ? 'Edit Entri' : 'Tambah Entri Harian' }}</h3>
                    <button wire:click="closeModal" class="p-1.5 rounded-lg dark:hover:bg-white/[0.06] hover:bg-black/[0.04] transition-colors">
                        <svg class="w-4 h-4 text-[#8b8b93]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="save">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[12px] uppercase tracking-[0.04em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mb-1.5 font-body">Tanggal</label>
                            <input type="date" wire:model="formDate" x-ref="modalForm"
                                   class="w-full px-3 py-2.5 rounded-[9px] text-[13px] font-mono border outline-none transition-colors
                                   dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-[#f5f5f4] focus:border-profit/50
                                   bg-black/[0.03] border-black/[0.08] text-[#0a0a0c]">
                            @error('formDate') <span class="text-[11px] text-loss mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[12px] uppercase tracking-[0.04em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mb-1.5 font-body">Status</label>
                            <div class="flex bg-white/[0.04] dark:bg-white/[0.04] border border-white/[0.09] dark:border-white/[0.09] rounded-xl p-1 gap-1">
                                <button type="button" wire:click="$set('formStatus', 'profit')"
                                        class="flex-1 text-center py-2 rounded-[9px] text-[12px] font-medium transition-all
                                        {{ $formStatus === 'profit' ? 'bg-profit/14 text-profit' : 'text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] dark:hover:text-white hover:text-ink' }}">
                                    Profit
                                </button>
                                <button type="button" wire:click="$set('formStatus', 'loss')"
                                        class="flex-1 text-center py-2 rounded-[9px] text-[12px] font-medium transition-all
                                        {{ $formStatus === 'loss' ? 'bg-loss/14 text-loss' : 'text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] dark:hover:text-white hover:text-ink' }}">
                                    Loss
                                </button>
                                <button type="button" wire:click="$set('formStatus', 'day_off')"
                                        class="flex-1 text-center py-2 rounded-[9px] text-[12px] font-medium transition-all
                                        {{ $formStatus === 'day_off' ? 'bg-white/[0.08] dark:text-white text-ink' : 'text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] dark:hover:text-white hover:text-ink' }}">
                                    Day Off
                                </button>
                            </div>
                            @error('formStatus') <span class="text-[11px] text-loss mt-1">{{ $message }}</span> @enderror
                        </div>

                        @if($formStatus === 'profit')
                            <div>
                                <label class="block text-[12px] uppercase tracking-[0.04em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mb-1.5 font-body">Profit Amount ($)</label>
                                <input type="number" step="0.01" min="0" wire:model="formProfitAmount"
                                       class="w-full px-3 py-2.5 rounded-[9px] text-[13px] font-mono border outline-none transition-colors
                                       dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-[#f5f5f4] focus:border-profit/50
                                       bg-black/[0.03] border-black/[0.08] text-[#0a0a0c]"
                                       placeholder="0.00">
                                @error('formProfitAmount') <span class="text-[11px] text-loss mt-1">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if($formStatus === 'loss')
                            <div>
                                <label class="block text-[12px] uppercase tracking-[0.04em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mb-1.5 font-body">Loss Amount ($)</label>
                                <input type="number" step="0.01" min="0" wire:model="formLossAmount"
                                       class="w-full px-3 py-2.5 rounded-[9px] text-[13px] font-mono border outline-none transition-colors
                                       dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-[#f5f5f4] focus:border-profit/50
                                       bg-black/[0.03] border-black/[0.08] text-[#0a0a0c]"
                                       placeholder="0.00">
                                @error('formLossAmount') <span class="text-[11px] text-loss mt-1">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div>
                            <label class="block text-[12px] uppercase tracking-[0.04em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mb-1.5 font-body">Catatan (opsional)</label>
                            <textarea wire:model="formNotes" rows="2"
                                      class="w-full px-3 py-2.5 rounded-[9px] text-[13px] border outline-none transition-colors resize-none
                                      dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-[#f5f5f4] focus:border-profit/50
                                      bg-black/[0.03] border-black/[0.08] text-[#0a0a0c]"
                                      placeholder="Catatan trading hari ini..."></textarea>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-6">
                        <button type="button" wire:click="closeModal"
                                class="flex-1 py-2.5 rounded-[10px] text-[13px] font-medium border transition-all
                                dark:border-white/[0.09] dark:text-[#8b8b93] dark:hover:text-white border-black/[0.08] text-[#6b6b70] hover:text-ink">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 py-2.5 rounded-[10px] text-[13px] font-medium border-0 transition-all
                                bg-white text-ink hover:opacity-80 dark:bg-white dark:text-ink light:bg-ink light:text-white">
                            {{ $isEditing ? 'Simpan Perubahan' : 'Tambah Entri' }}
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
                <h3 class="font-display text-lg font-semibold mb-2">Hapus Entri?</h3>
                <p class="text-sm dark:text-[#8b8b93] text-[#6b6b70] mb-5">
                    Data trading pada tanggal ini akan dihapus permanen. Running balance dan target akan dihitung ulang otomatis.
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

    {{-- Bulk Delete Confirmation --}}
    @if($showBulkConfirm)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-data>
            <div class="fixed inset-0 bg-black/60" wire:click="cancelBulkDelete"></div>
            <div class="relative w-full max-w-sm rounded-[20px] border p-6 z-10
                dark:bg-ink-2 dark:border-white/[0.09] bg-white border-black/[0.08] shadow-2xl">
                <h3 class="font-display text-lg font-semibold mb-2">Hapus {{ count($selectedIds) }} Entri?</h3>
                <p class="text-sm dark:text-[#8b8b93] text-[#6b6b70] mb-5">
                    Semua data trading yang dipilih akan dihapus permanen. Running balance dan target akan dihitung ulang otomatis.
                </p>
                <div class="flex gap-2">
                    <button wire:click="cancelBulkDelete"
                            class="flex-1 py-2.5 rounded-[10px] text-[13px] font-medium border transition-all
                            dark:border-white/[0.09] dark:text-[#8b8b93] dark:hover:text-white border-black/[0.08] text-[#6b6b70] hover:text-ink">
                        Batal
                    </button>
                    <button wire:click="bulkDelete"
                            class="flex-1 py-2.5 rounded-[10px] text-[13px] font-medium border-0 transition-all bg-loss text-white hover:opacity-80">
                        Hapus Semua
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
