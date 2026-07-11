<div class="py-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5 mb-5">
        <div class="glass-card p-5">
            <div class="text-[11.5px] uppercase tracking-[0.06em] mb-2.5 text-[#8b8b93] font-body">Total Profits</div>
            <div class="font-mono text-[26px] font-semibold tracking-tight num-pos">${{ number_format($this->totalProfit, 2) }}</div>
            <div class="text-xs text-[#8b8b93] mt-1.5">Akumulasi bulan berjalan</div>
        </div>
        <div class="glass-card p-5">
            <div class="text-[11.5px] uppercase tracking-[0.06em] mb-2.5 text-[#8b8b93] font-body">Total Loss</div>
            <div class="font-mono text-[26px] font-semibold tracking-tight num-neg">${{ number_format($this->totalLoss, 2) }}</div>
            <div class="text-xs text-[#8b8b93] mt-1.5">Akumulasi bulan berjalan</div>
        </div>
        <div class="glass-card p-5">
            <div class="text-[11.5px] uppercase tracking-[0.06em] mb-2.5 text-[#8b8b93] font-body">P/L Nett</div>
            <div class="font-mono text-[26px] font-semibold tracking-tight {{ $this->netPL >= 0 ? 'num-pos' : 'num-neg' }}">
                ${{ number_format(abs($this->netPL), 2) }}
                @if($this->netPL < 0)<span class="text-[14px] opacity-60">-</span>@endif
            </div>
            <div class="text-xs text-[#8b8b93] mt-1.5">Net setelah profit dan loss</div>
        </div>
        <div class="glass-card p-5">
            <div class="text-[11.5px] uppercase tracking-[0.06em] mb-2.5 text-[#8b8b93] font-body">Balance saat ini</div>
            <div class="font-mono text-[26px] font-semibold tracking-tight">${{ number_format($this->currentBalance, 2) }}</div>
            <div class="text-xs text-[#8b8b93] mt-1.5">{{ $this->activeAccount?->name ?? 'Tidak ada akun' }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1.5fr_1fr] gap-4 mb-4">
        <div class="glass-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display text-[15px] font-semibold">Equity curve</h3>
                <span class="text-[11px] text-[#8b8b93] font-mono">
                    {{ $this->activeAccountType === 'real' ? 'Real' : 'Demo' }}
                </span>
            </div>
            @if(count($this->equityCurveData['data']) > 0)
                <div id="equity-chart" class="h-48" x-data x-init="
                    new ApexCharts($refs.chart, {
                        chart: { type: 'area', height: 192, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
                        series: [{ name: 'Balance', data: @js($this->equityCurveData['data']) }],
                        xaxis: { categories: @js($this->equityCurveData['labels']), labels: { style: { colors: '#8b8b93', fontSize: '11px' } } },
                        yaxis: { labels: { style: { colors: '#8b8b93', fontSize: '11px' }, formatter: (v) => '$' + v.toFixed(0) } },
                        grid: { borderColor: 'rgba(255,255,255,0.04)' },
                        colors: ['#20e3a2'],
                        stroke: { curve: 'smooth', width: 2 },
                        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0.05, stops: [0, 90, 100] } },
                        tooltip: { theme: 'dark' }
                    }).render()
                ">
                    <div x-ref="chart"></div>
                </div>
            @else
                <div class="h-48 flex items-center justify-center text-sm text-[#8b8b93]">
                    Chart akan muncul setelah ada data
                </div>
            @endif
        </div>

        <div class="glass-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display text-[15px] font-semibold">Target harian</h3>
                <span class="text-[11px] text-[#8b8b93] font-mono">Hari ini</span>
            </div>
            <div class="flex gap-5 justify-around mt-1.5">
                <div class="text-center">
                    <svg width="76" height="76" viewBox="0 0 76 76">
                        <circle cx="38" cy="38" r="32" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="6"/>
                        <circle cx="38" cy="38" r="32" fill="none" stroke="#e8c468" stroke-width="6" stroke-linecap="round"
                            stroke-dasharray="201"
                            stroke-dashoffset="{{ 201 - (201 * $this->targetProgress['five'] / 100) }}"
                            transform="rotate(-90 38 38)"/>
                        <text x="38" y="43" text-anchor="middle" font-family="JetBrains Mono" font-size="13" fill="currentColor">
                            {{ number_format($this->targetProgress['five'], 0) }}%
                        </text>
                    </svg>
                    <div class="text-[11px] text-[#8b8b93] mt-2 uppercase tracking-[0.04em]">Target 5%</div>
                    <div class="font-mono text-[13px] font-semibold mt-0.5 num-target">
                        ${{ number_format($this->targetProgress['five_amount'], 2) }}
                    </div>
                </div>
                <div class="text-center">
                    <svg width="76" height="76" viewBox="0 0 76 76">
                        <circle cx="38" cy="38" r="32" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="6"/>
                        <circle cx="38" cy="38" r="32" fill="none" stroke="#ff5470" stroke-width="6" stroke-linecap="round"
                            stroke-dasharray="201"
                            stroke-dashoffset="{{ 201 - (201 * $this->targetProgress['ten'] / 100) }}"
                            transform="rotate(-90 38 38)"/>
                        <text x="38" y="43" text-anchor="middle" font-family="JetBrains Mono" font-size="13" fill="currentColor">
                            {{ number_format($this->targetProgress['ten'], 0) }}%
                        </text>
                    </svg>
                    <div class="text-[11px] text-[#8b8b93] mt-2 uppercase tracking-[0.04em]">Target 10%</div>
                    <div class="font-mono text-[13px] font-semibold mt-0.5 num-neg">
                        ${{ number_format($this->targetProgress['ten_amount'], 2) }}
                    </div>
                </div>
            </div>
            <div class="text-xs text-[#8b8b93] mt-4 text-center">Ring terisi otomatis dari running % harian</div>
        </div>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="flex items-center justify-between px-5 pt-5 pb-3.5">
            <h3 class="font-display text-[15px] font-semibold">Daily trading log</h3>
            <span class="text-[11px] text-[#8b8b93] font-mono">{{ now()->format('F Y') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[13px] min-w-[640px]">
                <thead>
                    <tr>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] border-t border-b border-white/[0.09] font-medium">Status</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] border-t border-b border-white/[0.09] font-medium">Day</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] border-t border-b border-white/[0.09] font-medium">Tanggal</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] border-t border-b border-white/[0.09] font-medium">Balance</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] border-t border-b border-white/[0.09] font-medium">Running 5%</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] border-t border-b border-white/[0.09] font-medium">Running 10%</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] border-t border-b border-white/[0.09] font-medium">Profit</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] border-t border-b border-white/[0.09] font-medium">Loss</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->dailyLogs as $log)
                        @php
                            $runningFive = $log->balance * 0.05;
                            $runningTen = $log->balance * 0.10;
                        @endphp
                        <tr class="border-b border-white/[0.04] hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-5">
                                @if($log->status === 'trading')
                                    <span class="status-chip status-profit px-2.5 py-1 rounded-full text-[10.5px] font-medium bg-profit/10 text-profit">
                                        Trading
                                    </span>
                                @elseif($log->status === 'day_off')
                                    <span class="status-chip status-dayoff px-2.5 py-1 rounded-full text-[10.5px] font-medium bg-white/[0.06] text-[#8b8b93]">
                                        Day Off
                                    </span>
                                @else
                                    <span class="status-chip px-2.5 py-1 rounded-full text-[10.5px] font-medium bg-white/[0.06] text-[#8b8b93]">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-5 text-[#8b8b93] text-[12px]">{{ $log->day_name }}</td>
                            <td class="py-3 px-5 font-mono text-[12px]">{{ $log->log_date->format('d/m/Y') }}</td>
                            <td class="py-3 px-5 font-mono font-medium">${{ number_format($log->balance, 2) }}</td>
                            <td class="py-3 px-5 font-mono text-[12px] num-target">${{ number_format($runningFive, 2) }}</td>
                            <td class="py-3 px-5 font-mono text-[12px] num-neg">${{ number_format($runningTen, 2) }}</td>
                            <td class="py-3 px-5 font-mono text-[12px] num-pos">
                                @if($log->profit_amount > 0) +${{ number_format($log->profit_amount, 2) }} @else — @endif
                            </td>
                            <td class="py-3 px-5 font-mono text-[12px] num-neg">
                                @if($log->loss_amount > 0) -${{ number_format($log->loss_amount, 2) }} @else — @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-5 text-center text-[#8b8b93] font-body">
                                Belum ada data trading untuk akun ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
