<div class="py-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="font-display text-2xl font-bold tracking-tight">Journal</h2>
            <p class="mt-1 text-sm dark:text-[#8b8b93] text-[#6b6b70]">
                Catatan trading harian
                <span class="font-semibold text-warn">{{ $this->activeAccount->name ?? '—' }}</span>
            </p>
        </div>
        <div>
            @if(count($this->monthOptions) > 0)
                <select wire:model.live="selectedMonth"
                        class="bg-white/[0.06] dark:bg-white/[0.06] border border-white/[0.09] dark:border-white/[0.09] rounded-[9px] px-3 py-2 text-[12px] font-mono text-[#8b8b93] dark:text-[#8b8b93] outline-none focus:border-profit/40 transition-colors cursor-pointer">
                    @foreach($this->monthOptions as $key => $label)
                        <option value="{{ $key }}" class="bg-[#141418] text-[#f5f5f4]">{{ $label }}</option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>

    {{-- Timeline --}}
    @forelse($this->logs as $log)
        @php
            $target1 = $log->targets->firstWhere('target_type', 'target_1');
            $target2 = $log->targets->firstWhere('target_type', 'target_2');
            $running1 = $target1 ? (float) $target1->running_amount : 0;
            $running2 = $target2 ? (float) $target2->running_amount : 0;
            $isEditing = $this->editingId === $log->id;
        @endphp
        <div class="relative pl-8">
            {{-- Timeline line --}}
            @if(!$loop->last)
                <div class="absolute left-[11px] top-8 bottom-0 w-px dark:bg-white/[0.08] bg-black/[0.06]"></div>
            @endif

            {{-- Timeline dot --}}
            <div class="absolute left-0 top-4 w-[23px] h-[23px] rounded-full border-2 flex items-center justify-center
                @if($log->status === 'profit') border-profit bg-profit/10
                @elseif($log->status === 'loss') border-loss bg-loss/10
                @else border-[#8b8b93]/40 bg-white/[0.04] @endif">
                @if($log->status === 'profit')
                    <svg class="w-2.5 h-2.5 text-profit" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"/></svg>
                @elseif($log->status === 'loss')
                    <svg class="w-2.5 h-2.5 text-loss" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z"/></svg>
                @else
                    <div class="w-2 h-2 rounded-full dark:bg-[#8b8b93] bg-[#6b6b70]"></div>
                @endif
            </div>

            {{-- Card --}}
            <div class="glass-card p-5">
                {{-- Header row --}}
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="font-display text-[15px] font-semibold">{{ $log->log_date->format('l, d M Y') }}</span>
                        @if($log->status === 'profit')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-profit/10 text-profit">Profit</span>
                        @elseif($log->status === 'loss')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-loss/10 text-loss">Loss</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-white/[0.06] dark:bg-white/[0.06] text-[#8b8b93]">Day Off</span>
                        @endif
                    </div>

                    @if(!$isEditing)
                        <button wire:click="startEdit({{ $log->id }}, @js($log->notes))"
                                class="p-1.5 rounded-lg dark:hover:bg-white/[0.06] hover:bg-black/[0.04] transition-colors text-[#8b8b93] hover:text-white dark:hover:text-white shrink-0"
                                title="Edit catatan">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Metrics row --}}
                <div class="flex flex-wrap gap-x-5 gap-y-1 text-[12px] mb-3 font-mono">
                    <span>
                        Balance:
                        <span class="font-semibold">${{ number_format((float) $log->balance, 2) }}</span>
                    </span>
                    @if($log->status !== 'day_off')
                        <span class="{{ $running1 >= 0 ? 'num-target' : 'num-neg' }}">
                            {{ $this->rules->target_1_pct }}%:
                            ${{ number_format($running1, 2) }}
                        </span>
                        <span class="{{ $running2 >= 0 ? 'num-pos' : 'num-neg' }}">
                            {{ $this->rules->target_2_pct }}%:
                            ${{ number_format($running2, 2) }}
                        </span>
                    @endif
                    @if((float) $log->profit_amount > 0)
                        <span class="num-pos">+${{ number_format((float) $log->profit_amount, 2) }}</span>
                    @endif
                    @if((float) $log->loss_amount > 0)
                        <span class="num-neg">-${{ number_format((float) $log->loss_amount, 2) }}</span>
                    @endif
                </div>

                {{-- Notes --}}
                @if($isEditing)
                    <div class="mt-1">
                        <textarea wire:model="editNotes" rows="3" x-init="$nextTick(() => $el.focus())"
                                  class="w-full px-3 py-2.5 rounded-xl text-[13px] border outline-none transition-colors resize-none
                                  dark:bg-white/[0.04] dark:border-profit/30 dark:text-[#f5f5f4] focus:border-profit/50
                                  bg-black/[0.03] border-black/[0.08] text-[#0a0a0c]"
                                  placeholder="Tulis catatan trading..."></textarea>
                        <div class="flex gap-2 mt-2">
                            <button wire:click="saveNote({{ $log->id }})"
                                    class="px-3.5 py-1.5 rounded-lg text-[11px] font-semibold bg-profit text-[#04231a] hover:brightness-110 transition-all">
                                Simpan
                            </button>
                            <button wire:click="cancelEdit"
                                    class="px-3.5 py-1.5 rounded-lg text-[11px] font-medium border dark:border-white/[0.09] dark:text-[#8b8b93] dark:hover:text-white border-black/[0.08] text-[#6b6b70] hover:text-ink transition-all">
                                Batal
                            </button>
                        </div>
                    </div>
                @elseif($log->notes)
                    <p class="text-[13px] dark:text-[#c2c2cb] text-[#3a3a40] leading-relaxed whitespace-pre-line">{{ $log->notes }}</p>
                @else
                    <p class="text-[12px] dark:text-[#8b8b93]/50 text-[#6b6b70]/50 italic">Belum ada catatan</p>
                @endif
            </div>
        </div>
    @empty
        <div class="glass-card p-12 text-center">
            <div class="text-[#8b8b93] text-sm">Belum ada data trading untuk bulan ini.</div>
        </div>
    @endforelse
</div>
