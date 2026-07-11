<?php

namespace App\Livewire;

use App\Models\DailyLog;
use App\Models\Scopes\ActiveAccountScope;
use App\Models\Target;
use App\Services\TargetCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class DailyLogTable extends Component
{
    use WithPagination;

    public string $selectedMonth = '';
    public string $selectedStatus = 'all';
    public int $perPage = 15;

    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    public string $formDate = '';
    public string $formStatus = 'profit';
    public float $formBalance = 0;
    public float $formProfitAmount = 0;
    public float $formLossAmount = 0;
    public string $formNotes = '';

    public bool $showDeleteConfirm = false;
    public ?int $deletingId = null;

    public array $selectedIds = [];
    public bool $showBulkConfirm = false;

    protected $listeners = ['accountSwitched' => '$refresh', 'refreshDashboard' => '$refresh'];

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

        $query = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->whereYear('log_date', $carbon->year)
            ->whereMonth('log_date', $carbon->month)
            ->with(['targets' => function ($q) use ($account) {
                $q->where('account_id', $account->id);
            }])
            ->orderByDesc('log_date');

        if ($this->selectedStatus !== 'all') {
            $query->where('status', $this->selectedStatus);
        }

        return $query->paginate($this->perPage);
    }

    public function updatedSelectedMonth(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->isEditing = false;
        $this->editingId = null;
        $this->formDate = now()->format('Y-m-d');
        $this->formStatus = 'profit';
        $this->formBalance = 0;
        $this->formProfitAmount = 0;
        $this->formLossAmount = 0;
        $this->formNotes = '';
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $log = DailyLog::withoutGlobalScope(ActiveAccountScope::class)->findOrFail($id);

        $this->isEditing = true;
        $this->editingId = $id;
        $this->formDate = $log->log_date->format('Y-m-d');
        $this->formStatus = $log->status;
        $this->formBalance = (float) $log->balance;
        $this->formProfitAmount = (float) $log->profit_amount;
        $this->formLossAmount = (float) $log->loss_amount;
        $this->formNotes = $log->notes ?? '';
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate([
            'formDate' => 'required|date',
            'formStatus' => 'required|in:profit,loss,day_off',
            'formProfitAmount' => 'required|numeric|min:0',
            'formLossAmount' => 'required|numeric|min:0',
            'formNotes' => 'nullable|string|max:500',
        ]);

        $account = $this->activeAccount;
        if (! $account) return;

        $profit = $this->formStatus === 'profit' ? round($this->formProfitAmount, 2) : 0;
        $loss = $this->formStatus === 'loss' ? round($this->formLossAmount, 2) : 0;

        if ($this->isEditing && $this->editingId) {
            $log = DailyLog::withoutGlobalScope(ActiveAccountScope::class)->findOrFail($this->editingId);
            $log->update([
                'log_date' => $this->formDate,
                'status' => $this->formStatus,
                'profit_amount' => $profit,
                'loss_amount' => $loss,
                'notes' => $this->formNotes ?: null,
            ]);
        } else {
            $existingDate = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
                ->where('account_id', $account->id)
                ->whereDate('log_date', $this->formDate)
                ->first();

            if ($existingDate) {
                session()->flash('error', 'Entry untuk tanggal ini sudah ada.');
                return;
            }

            $log = DailyLog::create([
                'account_id' => $account->id,
                'log_date' => $this->formDate,
                'status' => $this->formStatus,
                'balance' => 0,
                'daily_percent' => 0,
                'profit_amount' => $profit,
                'loss_amount' => $loss,
                'notes' => $this->formNotes ?: null,
            ]);
        }

        app(TargetCalculationService::class)->calculateForNewEntry($account, $log);

        $this->closeModal();
        $this->dispatch('refreshDashboard');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteConfirm = true;
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
        $this->showDeleteConfirm = false;
    }

    public function deleteEntry(): void
    {
        if (! $this->deletingId) return;

        $account = $this->activeAccount;
        if (! $account) return;

        $log = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->findOrFail($this->deletingId);

        $prevLog = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->where('log_date', '<', $log->log_date)
            ->orderByDesc('log_date')
            ->first();

        $log->delete();

        if ($prevLog) {
            app(TargetCalculationService::class)->recalculateForward($account, $prevLog);
        } else {
            app(TargetCalculationService::class)->recalculateAllForAccount($account);
        }

        $this->showDeleteConfirm = false;
        $this->deletingId = null;
        $this->dispatch('refreshDashboard');
    }

    public function getIsAllSelectedProperty(): bool
    {
        $pageIds = $this->logs->pluck('id')->toArray();

        return count($pageIds) > 0 && count(array_intersect($pageIds, $this->selectedIds)) === count($pageIds);
    }

    public function toggleAll(): void
    {
        $pageIds = $this->logs->pluck('id')->toArray();

        if ($this->isAllSelected) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, $pageIds));
        } else {
            $this->selectedIds = array_unique(array_merge($this->selectedIds, $pageIds));
        }
    }

    public function toggleSelect(int $id): void
    {
        if (in_array($id, $this->selectedIds)) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, [$id]));
        } else {
            $this->selectedIds[] = $id;
        }
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selectedIds)) return;
        $this->showBulkConfirm = true;
    }

    public function cancelBulkDelete(): void
    {
        $this->showBulkConfirm = false;
    }

    public function bulkDelete(): void
    {
        if (empty($this->selectedIds)) return;

        $account = $this->activeAccount;
        if (! $account) return;

        $logs = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->whereIn('id', $this->selectedIds)
            ->orderBy('log_date')
            ->get();

        if ($logs->isEmpty()) {
            $this->selectedIds = [];
            $this->showBulkConfirm = false;
            return;
        }

        $earliestDate = $logs->first()->log_date;

        DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->whereIn('id', $this->selectedIds)
            ->delete();

        $prevLog = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->where('log_date', '<', $earliestDate)
            ->orderByDesc('log_date')
            ->first();

        if ($prevLog) {
            app(TargetCalculationService::class)->recalculateForward($account, $prevLog);
        } else {
            app(TargetCalculationService::class)->recalculateAllForAccount($account);
        }

        $this->selectedIds = [];
        $this->showBulkConfirm = false;
        $this->dispatch('refreshDashboard');
    }

    public function render()
    {
        return view('livewire.daily-log-table');
    }
}
