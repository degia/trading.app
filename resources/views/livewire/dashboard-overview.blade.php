<div class="py-6" x-data="{
    chart: null,
    chartData: @js($this->equityCurveData),
    init() {
        this.$nextTick(() => this.buildChart());
        this.$watch('$store.theme', () => this.rebuildChart());
    },
    getChartColors() {
        const isDark = (this.$store?.theme || localStorage.getItem('theme')) === 'dark';
        return {
            textColor: isDark ? '#8b8b93' : '#6b6b70',
            gridColor: isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.04)',
            tooltipTheme: isDark ? 'dark' : 'light',
            strokeColor: '#20e3a2',
            fillFrom: 0.25,
            fillTo: 0.05,
        };
    },
    buildChart() {
        if (!this.chartData.data.length || !this.$refs.chartContainer) return;
        if (this.chart) { this.chart.destroy(); this.chart = null; }
        this.$refs.chartContainer.innerHTML = '';
        const c = this.getChartColors();
        this.chart = new ApexCharts(this.$refs.chartContainer, {
            chart: { type: 'area', height: 192, toolbar: { show: false }, fontFamily: 'Inter, sans-serif', background: 'transparent' },
            series: [{ name: 'Balance', data: this.chartData.data }],
            xaxis: {
                categories: this.chartData.labels,
                labels: { style: { colors: c.textColor, fontSize: '11px' }, rotate: -45, rotateAlways: false },
                axisBorder: { show: false }, axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: c.textColor, fontSize: '11px' }, formatter: (v) => '$' + v.toFixed(0) },
                forceNiceScale: true
            },
            grid: { borderColor: c.gridColor, strokeDashArray: 3, xaxis: { lines: { show: false } }, yaxis: { lines: { show: true } } },
            colors: [c.strokeColor],
            stroke: { curve: 'smooth', width: 2.2 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: c.fillFrom, opacityTo: c.fillTo, stops: [0, 90, 100], colorStops: [{ offset: 0, color: c.strokeColor, opacity: 0.25 }, { offset: 100, color: c.strokeColor, opacity: 0.03 }] } },
            tooltip: { theme: c.tooltipTheme, style: { fontSize: '12px' }, y: { formatter: (v) => '$' + v.toFixed(2) } },
            markers: { size: 0, hover: { size: 4, sizeOffset: 2 } },
            dataLabels: { enabled: false }
        });
        this.chart.render();
    },
    rebuildChart() {
        if (this.chart) { this.chart.destroy(); this.chart = null; }
        this.$nextTick(() => this.buildChart());
    }
}" x-init="init()">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5 mb-5">
        <div class="glass-card p-5">
            <div class="flex items-center justify-between mb-2.5">
                <div class="text-[11.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-body">Total Profits</div>
            </div>
            <div class="font-mono text-[26px] font-semibold tracking-tight num-pos">${{ number_format($this->totalProfit, 2) }}</div>
            <div class="text-xs text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mt-1.5">{{ $this->selectedMonthLabel }}</div>
        </div>
        <div class="glass-card p-5">
            <div class="flex items-center justify-between mb-2.5">
                <div class="text-[11.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-body">Total Loss</div>
            </div>
            <div class="font-mono text-[26px] font-semibold tracking-tight num-neg">-${{ number_format($this->totalLoss, 2) }}</div>
            <div class="text-xs text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mt-1.5">{{ $this->selectedMonthLabel }}</div>
        </div>
        <div class="glass-card p-5">
            <div class="flex items-center justify-between mb-2.5">
                <div class="text-[11.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-body">P/L Nett</div>
            </div>
            <div class="font-mono text-[26px] font-semibold tracking-tight {{ $this->netPL >= 0 ? 'num-pos' : 'num-neg' }}">
                @if($this->netPL < 0) -@endif${{ number_format(abs($this->netPL), 2) }}
            </div>
            <div class="text-xs text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mt-1.5">Net setelah profit dan loss</div>
        </div>
        <div class="glass-card p-5">
            <div class="flex items-center justify-between mb-2.5">
                <div class="text-[11.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-body">Balance saat ini</div>
            </div>
            <div class="font-mono text-[26px] font-semibold tracking-tight">${{ number_format($this->currentBalance, 2) }}</div>
            <div class="text-xs text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mt-1.5">Update {{ now()->format('d M Y') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1.5fr_1fr] gap-4 mb-4">
        <div class="glass-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display text-[15px] font-semibold">Equity curve — {{ $this->selectedMonthLabel }}</h3>
                @if(count($this->equityCurveData['data']) > 0)
                    <span class="text-[11px] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-mono">
                        {{ count($this->equityCurveData['data']) }} hari
                    </span>
                @endif
            </div>
            @if(count($this->equityCurveData['data']) > 0)
                <div x-ref="chartContainer" wire:ignore class="h-[192px]"></div>
            @else
                <div class="h-[192px] flex items-center justify-center text-sm text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70]">
                    Chart akan muncul setelah ada data
                </div>
            @endif
        </div>

        <div class="glass-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display text-[15px] font-semibold">Target harian</h3>
                <span class="text-[11px] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-mono">
                    {{ $this->targetProgress['last_day'] ?? '—' }}, {{ $this->targetProgress['last_date'] ?? '' }}
                </span>
            </div>
            <div class="flex gap-5 justify-around mt-1.5">
                @php
                    $circumference = 201;
                    $fiveOffset = $circumference - ($circumference * $this->targetProgress['five_pct'] / 100);
                    $tenOffset = $circumference - ($circumference * $this->targetProgress['ten_pct'] / 100);
                @endphp
                <div class="ring-item text-center">
                    <svg width="76" height="76" viewBox="0 0 76 76">
                        <circle cx="38" cy="38" r="32" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="6" class="dark:opacity-100 light:opacity-40"/>
                        <circle cx="38" cy="38" r="32" fill="none" stroke="#e8c468" stroke-width="6" stroke-linecap="round"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $fiveOffset }}"
                            transform="rotate(-90 38 38)"/>
                        <text x="38" y="43" text-anchor="middle" font-family="JetBrains Mono" font-size="13" fill="currentColor">
                            {{ number_format($this->targetProgress['five_pct'], 0) }}%
                        </text>
                    </svg>
                    <div class="text-[11px] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mt-2 uppercase tracking-[0.04em]">Target {{ $this->rules->target_1_pct }}%</div>
                    <div class="font-mono text-[13px] font-semibold mt-0.5 num-target">${{ number_format($this->targetProgress['five_amount'], 2) }}</div>
                </div>
                <div class="ring-item text-center">
                    <svg width="76" height="76" viewBox="0 0 76 76">
                        <circle cx="38" cy="38" r="32" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="6" class="dark:opacity-100 light:opacity-40"/>
                        <circle cx="38" cy="38" r="32" fill="none" stroke="#ff5470" stroke-width="6" stroke-linecap="round"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $tenOffset }}"
                            transform="rotate(-90 38 38)"/>
                        <text x="38" y="43" text-anchor="middle" font-family="JetBrains Mono" font-size="13" fill="currentColor">
                            {{ number_format($this->targetProgress['ten_pct'], 0) }}%
                        </text>
                    </svg>
                    <div class="text-[11px] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mt-2 uppercase tracking-[0.04em]">Target {{ $this->rules->target_2_pct }}%</div>
                    <div class="font-mono text-[13px] font-semibold mt-0.5 num-neg">${{ number_format($this->targetProgress['ten_amount'], 2) }}</div>
                </div>
            </div>
            <div class="text-xs text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mt-4 text-center">Ring terisi otomatis dari running % harian</div>
        </div>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="flex items-center justify-between px-5 pt-5 pb-3.5">
            <h3 class="font-display text-[15px] font-semibold">Daily trading log</h3>
            <div class="flex items-center gap-2">
                @if(count($this->monthOptions) > 1)
                    <select wire:model.live="selectedMonth"
                            class="bg-white/[0.06] dark:bg-white/[0.06] border border-white/[0.09] dark:border-white/[0.09] rounded-[9px] px-3 py-1.5 text-[12px] font-mono text-[#8b8b93] dark:text-[#8b8b93] outline-none focus:border-profit/40 transition-colors cursor-pointer">
                        @foreach($this->monthOptions as $key => $label)
                            <option value="{{ $key }}" class="bg-[#141418] text-[#f5f5f4]">{{ $label }}</option>
                        @endforeach
                    </select>
                @else
                    <span class="text-[11px] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-mono">{{ $this->selectedMonthLabel }}</span>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[13px] min-w-[640px]">
                <thead>
                    <tr>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Status</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Day</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Tanggal</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Balance</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Running {{ $this->rules->target_1_pct }}%</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Running {{ $this->rules->target_2_pct }}%</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Profit</th>
                        <th class="text-left py-2.5 px-5 text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] border-t border-b dark:border-white/[0.09] border-black/[0.08] font-medium">Loss</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->dailyLogs as $log)
                        @php
                            $target5 = $log->targets->firstWhere('target_type', 'target_1');
                            $target10 = $log->targets->firstWhere('target_type', 'target_2');
                            $running5 = $target5 ? (float) $target5->running_amount : 0;
                            $running10 = $target10 ? (float) $target10->running_amount : 0;
                        @endphp
                        <tr class="border-b dark:border-white/[0.04] border-black/[0.04] dark:hover:bg-white/[0.02] hover:bg-black/[0.015] transition-colors">
                            <td class="py-3 px-5">
                                @if($log->status === 'profit')
                                    <span class="status-chip status-profit px-2.5 py-1 rounded-full text-[10.5px] font-medium bg-profit/10 text-profit">Profit</span>
                                @elseif($log->status === 'loss')
                                    <span class="status-chip status-loss px-2.5 py-1 rounded-full text-[10.5px] font-medium bg-loss/10 text-loss">Loss</span>
                                @else
                                    <span class="status-chip status-dayoff px-2.5 py-1 rounded-full text-[10.5px] font-medium bg-white/[0.06] dark:bg-white/[0.06] text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70]">Day off</span>
                                @endif
                            </td>
                            <td class="py-3 px-5 text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] text-[12px]">{{ $log->day_name }}</td>
                            <td class="py-3 px-5 font-mono text-[12px]">{{ $log->log_date->format('d/m/Y') }}</td>
                            <td class="py-3 px-5 font-mono font-medium">${{ number_format($log->balance, 2) }}</td>
                            <td class="py-3 px-5 font-mono text-[12px] {{ $running5 >= 0 ? 'num-target' : 'num-neg' }}">
                                @if($log->status !== 'day_off')${{ number_format($running5, 2) }}@else$0.00
                                @endif
                            </td>
                            <td class="py-3 px-5 font-mono text-[12px] {{ $running10 >= 0 ? 'num-pos' : 'num-neg' }}">
                                @if($log->status !== 'day_off')${{ number_format($running10, 2) }}@else$0.00
                                @endif
                            </td>
                            <td class="py-3 px-5 font-mono text-[12px] num-pos">
                                @if($log->profit_amount > 0)${{ number_format($log->profit_amount, 2) }}@else$0.00
                                @endif
                            </td>
                            <td class="py-3 px-5 font-mono text-[12px] num-neg">
                                @if($log->loss_amount > 0)${{ number_format($log->loss_amount, 2) }}@else$0.00
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-5 text-center text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] font-body">
                                Belum ada data trading untuk bulan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
