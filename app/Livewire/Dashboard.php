<?php

namespace App\Livewire;

use App\Models\DailyLog;
use App\Models\Target;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Dashboard extends Component
{
    protected $listeners = ['accountSwitched' => '$refresh'];

    public function getActiveAccountProperty()
    {
        $accountId = Session::get('active_account_id');

        return Auth::user()->accounts()->where('id', $accountId)->first()
            ?? Auth::user()->accounts()->first();
    }

    public function getTotalProfitProperty()
    {
        $account = $this->activeAccount;
        if (! $account) return 0;

        return DailyLog::withoutGlobalScope(\App\Models\Scopes\ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->where('status', 'trading')
            ->whereMonth('log_date', now()->month)
            ->whereYear('log_date', now()->year)
            ->sum('profit_amount');
    }

    public function getTotalLossProperty()
    {
        $account = $this->activeAccount;
        if (! $account) return 0;

        return DailyLog::withoutGlobalScope(\App\Models\Scopes\ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->where('status', 'trading')
            ->whereMonth('log_date', now()->month)
            ->whereYear('log_date', now()->year)
            ->sum('loss_amount');
    }

    public function getNetPLProperty()
    {
        return $this->totalProfit - $this->totalLoss;
    }

    public function getCurrentBalanceProperty()
    {
        $account = $this->activeAccount;
        if (! $account) return 0;

        $lastLog = DailyLog::withoutGlobalScope(\App\Models\Scopes\ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->orderByDesc('log_date')
            ->first();

        return $lastLog?->balance ?? $account->current_balance;
    }

    public function getDailyLogsProperty()
    {
        $account = $this->activeAccount;
        if (! $account) return collect();

        return DailyLog::withoutGlobalScope(\App\Models\Scopes\ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->whereMonth('log_date', now()->month)
            ->whereYear('log_date', now()->year)
            ->orderByDesc('log_date')
            ->get();
    }

    public function getTargetProgressProperty()
    {
        $account = $this->activeAccount;
        if (! $account) return ['five' => 0, 'ten' => 0, 'five_amount' => 0, 'ten_amount' => 0];

        $runningBalance = $this->currentBalance;
        $todayLog = DailyLog::withoutGlobalScope(\App\Models\Scopes\ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->whereDate('log_date', today())
            ->first();

        $dailyPercent = abs($todayLog?->daily_percent ?? 0);
        $fiveTargetAmount = $runningBalance * 0.05;
        $tenTargetAmount = $runningBalance * 0.10;

        return [
            'five' => min(($dailyPercent / 5) * 100, 100),
            'ten' => min(($dailyPercent / 10) * 100, 100),
            'five_amount' => $fiveTargetAmount,
            'ten_amount' => $tenTargetAmount,
        ];
    }

    public function getEquityCurveDataProperty()
    {
        $account = $this->activeAccount;
        if (! $account) return ['labels' => [], 'data' => []];

        $logs = DailyLog::withoutGlobalScope(\App\Models\Scopes\ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->orderBy('log_date')
            ->get();

        return [
            'labels' => $logs->pluck('log_date')->map(fn ($d) => $d->format('d M'))->toArray(),
            'data' => $logs->pluck('balance')->map(fn ($b) => (float) $b)->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
