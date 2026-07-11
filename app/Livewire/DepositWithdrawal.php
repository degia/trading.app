<?php

namespace App\Livewire;

use App\Models\Transaction;
use App\Services\TargetCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class DepositWithdrawal extends Component
{
    use WithPagination;

    public string $selectedMonth = '';
    public string $selectedType = 'all';
    public int $perPage = 15;

    public bool $showModal = false;
    public string $formType = 'deposit';
    public float $formAmount = 0;
    public string $formDate = '';
    public string $formNotes = '';

    public bool $showDeleteConfirm = false;
    public ?int $deletingId = null;

    protected $listeners = ['accountSwitched' => '$refresh'];

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
        $this->formDate = now()->format('Y-m-d');
    }

    public function updatedSelectedMonth(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedType(): void
    {
        $this->resetPage();
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

        $months = Transaction::withoutGlobalScope(\App\Models\Scopes\ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as month_key")
            ->selectRaw("DATE_FORMAT(transaction_date, '%M %Y') as month_label")
            ->groupBy('month_key', 'month_label')
            ->orderByRaw("month_key DESC")
            ->get();

        $options = [];
        foreach ($months as $m) {
            $options[$m->month_key] = $m->month_label;
        }

        return $options;
    }

    public function getSelectedMonthLabelProperty(): string
    {
        return Carbon::createFromFormat('Y-m', $this->selectedMonth)->translatedFormat('F Y');
    }

    public function getTransactionsProperty()
    {
        $account = $this->activeAccount;
        if (! $account) return collect();

        $carbon = Carbon::createFromFormat('Y-m', $this->selectedMonth);

        $query = Transaction::withoutGlobalScope(\App\Models\Scopes\ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->whereYear('transaction_date', $carbon->year)
            ->whereMonth('transaction_date', $carbon->month)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        if ($this->selectedType !== 'all') {
            $query->where('type', $this->selectedType);
        }

        return $query->paginate($this->perPage);
    }

    public function getTotalDepositProperty()
    {
        $account = $this->activeAccount;
        if (! $account) return 0;

        $carbon = Carbon::createFromFormat('Y-m', $this->selectedMonth);

        return Transaction::withoutGlobalScope(\App\Models\Scopes\ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->where('type', 'deposit')
            ->whereYear('transaction_date', $carbon->year)
            ->whereMonth('transaction_date', $carbon->month)
            ->sum('amount');
    }

    public function getTotalWithdrawalProperty()
    {
        $account = $this->activeAccount;
        if (! $account) return 0;

        $carbon = Carbon::createFromFormat('Y-m', $this->selectedMonth);

        return Transaction::withoutGlobalScope(\App\Models\Scopes\ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->where('type', 'withdrawal')
            ->whereYear('transaction_date', $carbon->year)
            ->whereMonth('transaction_date', $carbon->month)
            ->sum('amount');
    }

    public function openModal(string $type = 'deposit'): void
    {
        $this->formType = $type;
        $this->formAmount = 0;
        $this->formDate = now()->format('Y-m-d');
        $this->formNotes = '';
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
            'formType' => 'required|in:deposit,withdrawal',
            'formAmount' => 'required|numeric|min:0.01|max:1000000',
            'formDate' => 'required|date',
            'formNotes' => 'nullable|string|max:500',
        ]);

        $account = $this->activeAccount;
        if (! $account) return;

        if ($this->formType === 'withdrawal') {
            if ($this->formAmount > (float) $account->current_balance) {
                session()->flash('error', 'Saldo tidak mencukupi untuk penarikan ini.');
                return;
            }
        }

        DB::transaction(function () use ($account) {
            Transaction::create([
                'account_id' => $account->id,
                'type' => $this->formType,
                'amount' => round($this->formAmount, 2),
                'transaction_date' => $this->formDate,
                'notes' => $this->formNotes ?: null,
            ]);
        });

        app(TargetCalculationService::class)->recalculateAllForAccount($account);

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

        $txn = Transaction::withoutGlobalScope(\App\Models\Scopes\ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->findOrFail($this->deletingId);

        DB::transaction(function () use ($txn) {
            $txn->delete();
        });

        app(TargetCalculationService::class)->recalculateAllForAccount($account);

        $this->showDeleteConfirm = false;
        $this->deletingId = null;
        $this->dispatch('refreshDashboard');
    }

    public function render()
    {
        return view('livewire.deposit-withdrawal');
    }
}
