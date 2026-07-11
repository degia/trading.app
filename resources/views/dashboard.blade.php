<x-app-layout>
    <div class="py-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5 mb-5">
            <div class="glass-card p-5">
                <div class="text-[11.5px] uppercase tracking-[0.06em] mb-2.5 text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-body">Total Profits</div>
                <div class="font-mono text-[26px] font-semibold tracking-tight num-pos">$0.00</div>
                <div class="text-xs text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mt-1.5">Akumulasi bulan berjalan</div>
            </div>
            <div class="glass-card p-5">
                <div class="text-[11.5px] uppercase tracking-[0.06em] mb-2.5 text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-body">Total Loss</div>
                <div class="font-mono text-[26px] font-semibold tracking-tight num-neg">$0.00</div>
                <div class="text-xs text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mt-1.5">Akumulasi bulan berjalan</div>
            </div>
            <div class="glass-card p-5">
                <div class="text-[11.5px] uppercase tracking-[0.06em] mb-2.5 text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-body">P/L Nett</div>
                <div class="font-mono text-[26px] font-semibold tracking-tight num-pos">$0.00</div>
                <div class="text-xs text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mt-1.5">Net setelah profit dan loss</div>
            </div>
            <div class="glass-card p-5">
                <div class="text-[11.5px] uppercase tracking-[0.06em] mb-2.5 text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-body">Balance saat ini</div>
                <div class="font-mono text-[26px] font-semibold tracking-tight">$0.00</div>
                <div class="text-xs text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mt-1.5">Belum ada data</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1.5fr_1fr] gap-4 mb-4">
            <div class="glass-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-[15px] font-semibold">Equity curve</h3>
                    <span class="text-[11px] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-mono">—</span>
                </div>
                <div class="h-40 flex items-center justify-center text-sm text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70]">
                    Chart akan muncul setelah ada data
                </div>
            </div>

            <div class="glass-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-[15px] font-semibold">Target harian</h3>
                    <span class="text-[11px] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-mono">—</span>
                </div>
                <div class="flex gap-5 justify-around mt-1.5">
                    <div class="text-center">
                        <svg width="76" height="76" viewBox="0 0 76 76">
                            <circle cx="38" cy="38" r="32" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="6"/>
                            <circle cx="38" cy="38" r="32" fill="none" stroke="#e8c468" stroke-width="6" stroke-linecap="round"
                                stroke-dasharray="201" stroke-dashoffset="201" transform="rotate(-90 38 38)"/>
                            <text x="38" y="43" text-anchor="middle" font-family="JetBrains Mono" font-size="13" fill="currentColor">0%</text>
                        </svg>
                        <div class="text-[11px] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mt-2 uppercase tracking-[0.04em]">Target 5%</div>
                        <div class="font-mono text-[13px] font-semibold mt-0.5 num-target">$0.00</div>
                    </div>
                    <div class="text-center">
                        <svg width="76" height="76" viewBox="0 0 76 76">
                            <circle cx="38" cy="38" r="32" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="6"/>
                            <circle cx="38" cy="38" r="32" fill="none" stroke="#ff5470" stroke-width="6" stroke-linecap="round"
                                stroke-dasharray="201" stroke-dashoffset="201" transform="rotate(-90 38 38)"/>
                            <text x="38" y="43" text-anchor="middle" font-family="JetBrains Mono" font-size="13" fill="currentColor">0%</text>
                        </svg>
                        <div class="text-[11px] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mt-2 uppercase tracking-[0.04em]">Target 10%</div>
                        <div class="font-mono text-[13px] font-semibold mt-0.5 num-neg">$0.00</div>
                    </div>
                </div>
                <div class="text-xs text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mt-4 text-center">Ring terisi otomatis dari running % harian</div>
            </div>
        </div>

        <div class="glass-card overflow-hidden">
            <div class="flex items-center justify-between px-5 pt-5 pb-3.5">
                <h3 class="font-display text-[15px] font-semibold">Daily trading log</h3>
                <span class="text-[11px] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-mono">—</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-[13px] min-w-[640px]">
                    <thead>
                        <tr>
                            <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b border-white/[0.09] font-medium">Status</th>
                            <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b border-white/[0.09] font-medium">Day</th>
                            <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b border-white/[0.09] font-medium">Tanggal</th>
                            <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b border-white/[0.09] font-medium">Balance</th>
                            <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b border-white/[0.09] font-medium">Running 5%</th>
                            <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b border-white/[0.09] font-medium">Running 10%</th>
                            <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b border-white/[0.09] font-medium">Profit</th>
                            <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b border-white/[0.09] font-medium">Loss</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="py-12 px-5 text-center text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-body">
                                Belum ada data trading. Mulai dengan menambahkan entri harian pertama.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
