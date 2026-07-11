<?php

namespace App\Livewire;

use App\Models\DailyLog;
use App\Models\Scopes\ActiveAccountScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Analytics extends Component
{
    public string $dateFrom = '';
    public string $dateTo = '';

    protected $listeners = ['accountSwitched' => '$refresh'];

    public function mount(): void
    {
        $this->dateFrom = now()->subDays(29)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatedDateFrom(): void
    {
        $this->dispatch('refreshChart', chartData: $this->chartData);
    }

    public function updatedDateTo(): void
    {
        $this->dispatch('refreshChart', chartData: $this->chartData);
    }

    public function getActiveAccountProperty()
    {
        $accountId = Session::get('active_account_id');

        return Auth::user()->accounts()->where('id', $accountId)->first()
            ?? Auth::user()->accounts()->first();
    }

    protected function scopedQuery()
    {
        $account = $this->activeAccount;
        if (! $account) return null;

        return DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->whereDate('log_date', '>=', $this->dateFrom)
            ->whereDate('log_date', '<=', $this->dateTo);
    }

    public function getTotalDaysProperty(): int
    {
        $q = $this->scopedQuery();
        return $q ? (clone $q)->count() : 0;
    }

    public function getProfitDaysProperty(): int
    {
        $q = $this->scopedQuery();
        return $q ? (clone $q)->where('status', 'profit')->count() : 0;
    }

    public function getLossDaysProperty(): int
    {
        $q = $this->scopedQuery();
        return $q ? (clone $q)->where('status', 'loss')->count() : 0;
    }

    public function getDayOffDaysProperty(): int
    {
        $q = $this->scopedQuery();
        return $q ? (clone $q)->where('status', 'day_off')->count() : 0;
    }

    public function getWinRateProperty(): float
    {
        $trading = $this->profitDays + $this->lossDays;
        return $trading > 0 ? round(($this->profitDays / $trading) * 100, 1) : 0;
    }

    public function getLoseRateProperty(): float
    {
        $trading = $this->profitDays + $this->lossDays;
        return $trading > 0 ? round(($this->lossDays / $trading) * 100, 1) : 0;
    }

    public function getAvgProfitProperty(): float
    {
        $q = $this->scopedQuery();
        if (! $q) return 0;
        $sum = (clone $q)->where('status', 'profit')->sum('profit_amount');
        return $this->profitDays > 0 ? round($sum / $this->profitDays, 2) : 0;
    }

    public function getAvgLossProperty(): float
    {
        $q = $this->scopedQuery();
        if (! $q) return 0;
        $sum = (clone $q)->where('status', 'loss')->sum('loss_amount');
        return $this->lossDays > 0 ? round($sum / $this->lossDays, 2) : 0;
    }

    public function getTotalProfitProperty(): float
    {
        $q = $this->scopedQuery();
        return $q ? (clone $q)->where('status', 'profit')->sum('profit_amount') : 0;
    }

    public function getTotalLossProperty(): float
    {
        $q = $this->scopedQuery();
        return $q ? (clone $q)->where('status', 'loss')->sum('loss_amount') : 0;
    }

    public function getNetProperty(): float
    {
        return $this->totalProfit - $this->totalLoss;
    }

    public function getChartDataProperty(): array
    {
        $account = $this->activeAccount;
        if (! $account) return ['categories' => [], 'profitSeries' => [], 'lossSeries' => []];

        $rows = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->whereDate('log_date', '>=', $this->dateFrom)
            ->whereDate('log_date', '<=', $this->dateTo)
            ->selectRaw("DATE_FORMAT(log_date, '%Y-%m') as month_key")
            ->selectRaw("DATE_FORMAT(log_date, '%b %Y') as month_label")
            ->selectRaw("SUM(CASE WHEN status = 'profit' THEN profit_amount ELSE 0 END) as total_profit")
            ->selectRaw("SUM(CASE WHEN status = 'loss' THEN loss_amount ELSE 0 END) as total_loss")
            ->groupBy('month_key', 'month_label')
            ->orderBy('month_key')
            ->get();

        return [
            'categories' => $rows->pluck('month_label')->toArray(),
            'profitSeries' => $rows->pluck('total_profit')->map(fn ($v) => round((float) $v, 2))->toArray(),
            'lossSeries' => $rows->pluck('total_loss')->map(fn ($v) => round((float) $v, 2))->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.analytics');
    }
}
