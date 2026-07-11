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
class Journal extends Component
{
    public string $selectedMonth = '';
    public ?int $editingId = null;
    public string $editNotes = '';

    protected $listeners = ['accountSwitched' => '$refresh'];

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    public function getActiveAccountProperty()
    {
        $accountId = Session::get('active_account_id');

        return Auth::user()->accounts()->where('id', $accountId)->first()
            ?? Auth::user()->accounts()->first();
    }

    public function getRulesProperty()
    {
        $account = $this->activeAccount;
        if (! $account) return null;

        return $account->getOrCreateRules();
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

    public function getLogsProperty()
    {
        $account = $this->activeAccount;
        if (! $account) return collect();

        $carbon = Carbon::createFromFormat('Y-m', $this->selectedMonth);

        return DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->whereYear('log_date', $carbon->year)
            ->whereMonth('log_date', $carbon->month)
            ->with(['targets' => function ($q) use ($account) {
                $q->where('account_id', $account->id);
            }])
            ->orderByDesc('log_date')
            ->get();
    }

    public function startEdit(int $id, string $notes): void
    {
        $this->editingId = $id;
        $this->editNotes = $notes ?? '';
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editNotes = '';
    }

    public function saveNote(int $id): void
    {
        $account = $this->activeAccount;
        if (! $account) return;

        DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->where('id', $id)
            ->update(['notes' => $this->editNotes ?: null]);

        $this->editingId = null;
        $this->editNotes = '';
    }

    public function render()
    {
        return view('livewire.journal');
    }
}
