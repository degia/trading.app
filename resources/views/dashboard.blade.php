@extends('layouts.app')

@section('page-title', 'Overview')

@section('content')
    <div class="space-y-6">
        {{-- Stats Row --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            <div class="glass-card">
                <div class="stat-label">Total Profits</div>
                <div class="stat-value profit">$0.00</div>
                <div class="stat-sub">Akumulasi bulan berjalan</div>
            </div>
            <div class="glass-card">
                <div class="stat-label">Total Loss</div>
                <div class="stat-value loss">-$0.00</div>
                <div class="stat-sub">Akumulasi bulan berjalan</div>
            </div>
            <div class="glass-card">
                <div class="stat-label">P/L Nett</div>
                <div class="stat-value profit">$0.00</div>
                <div class="stat-sub">Net setelah profit dan loss</div>
            </div>
            <div class="glass-card">
                <div class="stat-label">Balance saat ini</div>
                <div class="stat-value">$0.00</div>
                <div class="stat-sub">Belum ada data</div>
            </div>
        </div>

        {{-- Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
            {{-- Equity Curve --}}
            <div class="glass-card lg:col-span-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-[15px] font-semibold text-white light:text-black">Equity curve</h3>
                    <span class="text-xs text-mute-d light:text-mute-l font-mono">--</span>
                </div>
                <div class="h-40 flex items-center justify-center text-mute-d light:text-mute-l text-sm">
                    Belum ada data chart
                </div>
            </div>

            {{-- Target Rings --}}
            <div class="glass-card lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-[15px] font-semibold text-white light:text-black">Target harian</h3>
                    <span class="text-xs text-mute-d light:text-mute-l font-mono">--</span>
                </div>
                <div class="flex gap-5 justify-around mt-2">
                    <div class="text-center">
                        <svg width="76" height="76" viewBox="0 0 76 76">
                            <circle cx="38" cy="38" r="32" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="6"/>
                            <circle cx="38" cy="38" r="32" fill="none" stroke="#e8c468" stroke-width="6" stroke-linecap="round"
                                stroke-dasharray="201" stroke-dashoffset="201" transform="rotate(-90 38 38)"/>
                            <text x="38" y="43" text-anchor="middle" font-family="JetBrains Mono" font-size="13" fill="currentColor">0%</text>
                        </svg>
                        <div class="text-[11px] text-mute-d light:text-mute-l mt-2 uppercase tracking-wide">Target 5%</div>
                        <div class="font-mono text-[13px] font-semibold text-target mt-0.5">$0.00</div>
                    </div>
                    <div class="text-center">
                        <svg width="76" height="76" viewBox="0 0 76 76">
                            <circle cx="38" cy="38" r="32" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="6"/>
                            <circle cx="38" cy="38" r="32" fill="none" stroke="#ff5470" stroke-width="6" stroke-linecap="round"
                                stroke-dasharray="201" stroke-dashoffset="201" transform="rotate(-90 38 38)"/>
                            <text x="38" y="43" text-anchor="middle" font-family="JetBrains Mono" font-size="13" fill="currentColor">0%</text>
                        </svg>
                        <div class="text-[11px] text-mute-d light:text-mute-l mt-2 uppercase tracking-wide">Target 10%</div>
                        <div class="font-mono text-[13px] font-semibold text-loss mt-0.5">$0.00</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daily Log Table --}}
        <div class="glass-card !p-0 overflow-hidden">
            <div class="px-[22px] pt-5 pb-3.5 flex items-center justify-between">
                <h3 class="font-display text-[15px] font-semibold text-white light:text-black">Daily trading log</h3>
                <span class="text-xs text-mute-d light:text-mute-l font-mono">--</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-[13px] min-w-[640px]">
                    <thead>
                        <tr>
                            <th class="text-left py-2.5 px-[22px] text-[10.5px] uppercase tracking-widest text-mute-d light:text-mute-l border-t border-b border-white/[.09] light:border-black/[.08] font-medium">Status</th>
                            <th class="text-left py-2.5 px-[22px] text-[10.5px] uppercase tracking-widest text-mute-d light:text-mute-l border-t border-b border-white/[.09] light:border-black/[.08] font-medium">Day</th>
                            <th class="text-left py-2.5 px-[22px] text-[10.5px] uppercase tracking-widest text-mute-d light:text-mute-l border-t border-b border-white/[.09] light:border-black/[.08] font-medium">Tanggal</th>
                            <th class="text-left py-2.5 px-[22px] text-[10.5px] uppercase tracking-widest text-mute-d light:text-mute-l border-t border-b border-white/[.09] light:border-black/[.08] font-medium">Balance</th>
                            <th class="text-left py-2.5 px-[22px] text-[10.5px] uppercase tracking-widest text-mute-d light:text-mute-l border-t border-b border-white/[.09] light:border-black/[.08] font-medium">Running 5%</th>
                            <th class="text-left py-2.5 px-[22px] text-[10.5px] uppercase tracking-widest text-mute-d light:text-mute-l border-t border-b border-white/[.09] light:border-black/[.08] font-medium">Running 10%</th>
                            <th class="text-left py-2.5 px-[22px] text-[10.5px] uppercase tracking-widest text-mute-d light:text-mute-l border-t border-b border-white/[.09] light:border-black/[.08] font-medium">Profit</th>
                            <th class="text-left py-2.5 px-[22px] text-[10.5px] uppercase tracking-widest text-mute-d light:text-mute-l border-t border-b border-white/[.09] light:border-black/[.08] font-medium">Loss</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-mute-d light:text-mute-l font-body">
                                Belum ada data. Mulai catat trading harianmu.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
