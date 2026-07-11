<?php

namespace App\Livewire;

use App\Models\DailyLog;
use App\Models\Scopes\ActiveAccountScope;
use App\Models\Target;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DashboardOverview extends Component
{
    public string $selectedMonth = '';

    protected $listeners = ['accountSwitched' => '$refresh', 'refreshDashboard' => '$refresh'];

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    public function updatedSelectedMonth(): void
    {
        // Properties auto-recalculate via getters
    }

    public function getActiveAccountProperty()
    {
        $accountId = Session::get('active_account_id');

        return Auth::user()->accounts()->where('id', $accountId)->first()
            ?? Auth::user()->accounts()->first();
    }

    public function getMonthOptionsProperty(): array
    {
        $account = $this->activeAccount;
        if (! $account) return [];

        $months = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->selectRaw("DATE_FORMAT(log_date, '%Y-%m') as month_key")
            ->selectRaw("DATE_FORMAT(log_date, '%M %Y') as month_label")
            ->groupBy('month_key', 'month_label')
            ->orderByRaw("month_key DESC")
            ->get();

        $options = [];
        foreach ($months as $m) {
            $options[$m->month_key] = $m->month_label;
        }

        return $options;
    }

    protected function scopedQuery()
    {
        $account = $this->activeAccount;
        if (! $account) return null;

        $carbon = Carbon::createFromFormat('Y-m', $this->selectedMonth);

        return DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->whereYear('log_date', $carbon->year)
            ->whereMonth('log_date', $carbon->month);
    }

    public function getTotalProfitProperty()
    {
        $query = $this->scopedQuery();
        if (! $query) return 0;

        return (clone $query)->where('status', 'profit')->sum('profit_amount');
    }

    public function getTotalLossProperty()
    {
        $query = $this->scopedQuery();
        if (! $query) return 0;

        return (clone $query)->where('status', 'loss')->sum('loss_amount');
    }

    public function getNetPLProperty()
    {
        return $this->totalProfit - $this->totalLoss;
    }

    public function getCurrentBalanceProperty()
    {
        $account = $this->activeAccount;
        if (! $account) return 0;

        $lastLog = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->orderByDesc('log_date')
            ->first();

        return (float) ($lastLog?->balance ?? $account->current_balance);
    }

    public function getSelectedMonthLabelProperty(): string
    {
        $carbon = Carbon::createFromFormat('Y-m', $this->selectedMonth);

        return $carbon->translatedFormat('F Y');
    }

    public function getDailyLogsProperty()
    {
        $query = $this->scopedQuery();
        if (! $query) return collect();

        $account = $this->activeAccount;

        return (clone $query)
            ->with(['targets' => function ($q) use ($account) {
                $q->where('account_id', $account->id);
            }])
            ->orderByDesc('log_date')
            ->get();
    }

    public function getLatestTargetDateProperty()
    {
        $account = $this->activeAccount;
        if (! $account) return null;

        return Target::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->orderByDesc('created_at')
            ->value('daily_log_id');
    }

    public function getTargetProgressProperty(): array
    {
        $account = $this->activeAccount;
        $empty = [
            'five_pct' => 0, 'ten_pct' => 0,
            'five_amount' => 0, 'ten_amount' => 0,
            'five_running' => 0, 'ten_running' => 0,
        ];

        if (! $account) return $empty;

        $lastTradingLog = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->where('status', '!=', 'day_off')
            ->orderByDesc('log_date')
            ->first();

        if (! $lastTradingLog) return $empty;

        $targets = Target::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->where('daily_log_id', $lastTradingLog->id)
            ->get()
            ->keyBy('target_type');

        $five = $targets->get('5pct');
        $ten = $targets->get('10pct');

        return [
            'five_pct' => $five ? min(((float) $five->running_amount / (float) $five->target_amount) * 100, 100) : 0,
            'ten_pct' => $ten ? min(((float) $ten->running_amount / (float) $ten->target_amount) * 100, 100) : 0,
            'five_amount' => $five ? (float) $five->target_amount : 0,
            'ten_amount' => $ten ? (float) $ten->target_amount : 0,
            'five_running' => $five ? (float) $five->running_amount : 0,
            'ten_running' => $ten ? (float) $ten->running_amount : 0,
            'last_date' => $lastTradingLog->log_date->format('d M'),
            'last_day' => $lastTradingLog->day_name,
        ];
    }

    public function getEquityCurveDataProperty(): array
    {
        $account = $this->activeAccount;
        if (! $account) return ['labels' => [], 'data' => []];

        $logs = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
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
        return view('livewire.dashboard-overview');
    }
}
