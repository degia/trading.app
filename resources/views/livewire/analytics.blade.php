<div class="py-6 space-y-5" x-data="{
    chart: null,
    chartData: @js($this->chartData),
    init() {
        this.$nextTick(() => this.buildChart());
        this.$watch('$store.theme', () => this.rebuildChart());
        this.$wire.on('refreshChart', (data) => {
            this.chartData = data.chartData;
            this.rebuildChart();
        });
    },
    getChartColors() {
        const isDark = (this.$store?.theme || localStorage.getItem('theme')) === 'dark';
        return {
            textColor: isDark ? '#8b8b93' : '#6b6b70',
            gridColor: isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.04)',
            tooltipTheme: isDark ? 'dark' : 'light',
            profitColor: '#20e3a2',
            lossColor: '#ff5470',
        };
    },
    buildChart() {
        if (!this.chartData.categories.length || !this.$refs.barChart) return;
        if (this.chart) { this.chart.destroy(); this.chart = null; }
        this.$refs.barChart.innerHTML = '';
        const c = this.getChartColors();
        this.chart = new ApexCharts(this.$refs.barChart, {
            chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'Inter, sans-serif', background: 'transparent' },
            series: [
                { name: 'Profit', data: this.chartData.profitSeries },
                { name: 'Loss', data: this.chartData.lossSeries },
            ],
            plotOptions: {
                bar: { horizontal: false, borderRadius: 6, borderRadiusApplication: 'end', columnWidth: '55%' }
            },
            xaxis: {
                categories: this.chartData.categories,
                labels: { style: { colors: c.textColor, fontSize: '11px' }, rotate: -45, rotateAlways: false },
                axisBorder: { show: false }, axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: c.textColor, fontSize: '11px' }, formatter: (v) => '$' + v.toFixed(0) },
                forceNiceScale: true
            },
            grid: { borderColor: c.gridColor, strokeDashArray: 3, xaxis: { lines: { show: false } }, yaxis: { lines: { show: true } } },
            colors: [c.profitColor, c.lossColor],
            legend: { show: true, position: 'top', fontSize: '12px', labels: { colors: c.textColor }, markers: { size: 10, strokeWidth: 0 } },
            tooltip: { theme: c.tooltipTheme, style: { fontSize: '12px' }, y: { formatter: (v) => '$' + v.toFixed(2) } },
            dataLabels: { enabled: false }
        });
        this.chart.render();
    },
    rebuildChart() {
        if (this.chart) { this.chart.destroy(); this.chart = null; }
        this.$nextTick(() => this.buildChart());
    }
}" x-init="init()">

    {{-- Header + Filter --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="font-display text-2xl font-bold tracking-tight">Analytics</h2>
            <p class="mt-1 text-sm dark:text-[#8b8b93] text-[#6b6b70]">
                Breakdown performa trading
                <span class="font-semibold text-warn">{{ $this->activeAccount->name ?? '—' }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <input type="date" wire:model.live="dateFrom"
                   class="px-3 py-2 rounded-[9px] text-[12px] font-mono border dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-[#f5f5f4] bg-black/[0.03] border-black/[0.08] text-[#0a0a0c] outline-none focus:border-profit/40 transition-colors cursor-pointer">
            <span class="text-[11px] dark:text-[#8b8b93] text-[#6b6b70]">s/d</span>
            <input type="date" wire:model.live="dateTo"
                   class="px-3 py-2 rounded-[9px] text-[12px] font-mono border dark:bg-white/[0.04] dark:border-white/[0.09] dark:text-[#f5f5f4] bg-black/[0.03] border-black/[0.08] text-[#0a0a0c] outline-none focus:border-profit/40 transition-colors cursor-pointer">
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3.5">
        <div class="glass-card p-4">
            <div class="text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] font-body mb-1.5">Win Rate</div>
            <div class="font-mono text-[22px] font-bold tracking-tight num-pos">{{ $this->winRate }}%</div>
            <div class="text-[10px] text-[#8b8b93] mt-1">{{ $this->profitDays }}W / {{ $this->lossDays }}L</div>
        </div>
        <div class="glass-card p-4">
            <div class="text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] font-body mb-1.5">Lose Rate</div>
            <div class="font-mono text-[22px] font-bold tracking-tight num-neg">{{ $this->loseRate }}%</div>
            <div class="text-[10px] text-[#8b8b93] mt-1">{{ $this->lossDays }} dari {{ $this->profitDays + $this->lossDays }} hari trading</div>
        </div>
        <div class="glass-card p-4">
            <div class="text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] font-body mb-1.5">Avg Profit</div>
            <div class="font-mono text-[22px] font-bold tracking-tight num-pos">${{ number_format($this->avgProfit, 2) }}</div>
            <div class="text-[10px] text-[#8b8b93] mt-1">per hari profit</div>
        </div>
        <div class="glass-card p-4">
            <div class="text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] font-body mb-1.5">Avg Loss</div>
            <div class="font-mono text-[22px] font-bold tracking-tight num-neg">${{ number_format($this->avgLoss, 2) }}</div>
            <div class="text-[10px] text-[#8b8b93] mt-1">per hari loss</div>
        </div>
        <div class="glass-card p-4">
            <div class="text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] font-body mb-1.5">Total Profit</div>
            <div class="font-mono text-[22px] font-bold tracking-tight num-pos">${{ number_format($this->totalProfit, 2) }}</div>
            <div class="text-[10px] text-[#8b8b93] mt-1">{{ $this->profitDays }} hari profit</div>
        </div>
        <div class="glass-card p-4">
            <div class="text-[10.5px] uppercase tracking-[0.06em] text-[#8b8b93] font-body mb-1.5">Total Loss</div>
            <div class="font-mono text-[22px] font-bold tracking-tight num-neg">${{ number_format($this->totalLoss, 2) }}</div>
            <div class="text-[10px] text-[#8b8b93] mt-1">{{ $this->lossDays }} hari loss</div>
        </div>
    </div>

    {{-- Summary bar --}}
    <div class="glass-card p-5 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-6">
            <div>
                <span class="text-[11px] uppercase tracking-wider text-[#8b8b93] font-body">Total Hari</span>
                <span class="ml-2 font-mono text-sm font-semibold">{{ $this->totalDays }}</span>
            </div>
            <div>
                <span class="text-[11px] uppercase tracking-wider text-[#8b8b93] font-body">Trading</span>
                <span class="ml-2 font-mono text-sm font-semibold">{{ $this->profitDays + $this->lossDays }}</span>
            </div>
            <div>
                <span class="text-[11px] uppercase tracking-wider text-[#8b8b93] font-body">Day Off</span>
                <span class="ml-2 font-mono text-sm font-semibold">{{ $this->dayOffDays }}</span>
            </div>
        </div>
        <div class="font-mono text-sm font-semibold {{ $this->net >= 0 ? 'num-pos' : 'num-neg' }}">
            Net: @if($this->net < 0) -@endif${{ number_format(abs($this->net), 2) }}
        </div>
    </div>

    {{-- Win/Lose Donut --}}
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_1.6fr] gap-4">
        <div class="glass-card p-5">
            <h3 class="font-display text-[15px] font-semibold mb-4">Win / Lose Breakdown</h3>
            <div class="flex items-center justify-center gap-8 py-4">
                {{-- Profit ring --}}
                <div class="text-center" x-data="{
                    pct: {{ $this->winRate }},
                    init() {
                        const c = 201;
                        this.$refs.prog.style.strokeDashoffset = c - (c * this.pct / 100);
                    }
                }" x-init="init()">
                    <svg width="90" height="90" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="38" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="7"/>
                        <circle x-ref="prog" cx="45" cy="45" r="38" fill="none" stroke="#20e3a2" stroke-width="7" stroke-linecap="round"
                                stroke-dasharray="239" stroke-dashoffset="239" transform="rotate(-90 45 45)"
                                style="transition: stroke-dashoffset 0.6s ease"/>
                        <text x="45" y="50" text-anchor="middle" font-family="JetBrains Mono" font-size="15" fill="currentColor" class="font-bold">{{ $this->winRate }}%</text>
                    </svg>
                    <div class="text-[11px] uppercase tracking-wider text-profit mt-2 font-semibold">Win</div>
                    <div class="text-[10px] text-[#8b8b93]">{{ $this->profitDays }} hari</div>
                </div>
                {{-- Loss ring --}}
                <div class="text-center" x-data="{
                    pct: {{ $this->loseRate }},
                    init() {
                        const c = 239;
                        this.$refs.prog.style.strokeDashoffset = c - (c * this.pct / 100);
                    }
                }" x-init="init()">
                    <svg width="90" height="90" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="38" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="7"/>
                        <circle x-ref="prog" cx="45" cy="45" r="38" fill="none" stroke="#ff5470" stroke-width="7" stroke-linecap="round"
                                stroke-dasharray="239" stroke-dashoffset="239" transform="rotate(-90 45 45)"
                                style="transition: stroke-dashoffset 0.6s ease"/>
                        <text x="45" y="50" text-anchor="middle" font-family="JetBrains Mono" font-size="15" fill="currentColor" class="font-bold">{{ $this->loseRate }}%</text>
                    </svg>
                    <div class="text-[11px] uppercase tracking-wider text-loss mt-2 font-semibold">Lose</div>
                    <div class="text-[10px] text-[#8b8b93]">{{ $this->lossDays }} hari</div>
                </div>
            </div>
            <div class="text-xs text-[#8b8b93] text-center mt-2">Day off tidak dihitung dalam win rate</div>
        </div>

        {{-- Bar Chart --}}
        <div class="glass-card p-5">
            <h3 class="font-display text-[15px] font-semibold mb-4">Profit / Loss per Bulan</h3>
            @if(count($this->chartData['categories']) > 0)
                <div x-ref="barChart" wire:ignore class="h-[280px]"></div>
            @else
                <div class="h-[280px] flex items-center justify-center text-sm text-[#8b8b93]">
                    Tidak ada data untuk rentang tanggal ini.
                </div>
            @endif
        </div>
    </div>
</div>
