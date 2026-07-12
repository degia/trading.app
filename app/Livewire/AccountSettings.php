<?php

namespace App\Livewire;

use App\Models\Account;
use App\Services\TargetCalculationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class AccountSettings extends Component
{
    public bool $showModal = false;

    public string $formName = '';
    public float $formInitialBalance = 0;
    public float $formCurrentBalance = 0;

    public bool $saved = false;

    protected $listeners = ['accountSwitched' => '$refresh', 'openAccountSettings' => 'openModal'];

    public function getActiveAccountProperty(): ?Account
    {
        $accountId = Session::get('active_account_id');

        return Auth::user()->accounts()->where('id', $accountId)->first()
            ?? Auth::user()->accounts()->first();
    }

    public function openModal(): void
    {
        $account = $this->activeAccount;
        if (! $account) return;

        $this->formName = $account->name;
        $this->formInitialBalance = (float) $account->initial_balance;
        $this->formCurrentBalance = (float) $account->current_balance;
        $this->saved = false;
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
            'formName' => 'required|string|max:100',
            'formInitialBalance' => 'required|numeric|min:0',
            'formCurrentBalance' => 'required|numeric',
        ]);

        $account = $this->activeAccount;
        if (! $account) return;

        $initialChanged = (float) $account->initial_balance !== $this->formInitialBalance;

        $account->update([
            'name' => $this->formName,
            'initial_balance' => round($this->formInitialBalance, 2),
        ]);

        if ($initialChanged) {
            app(TargetCalculationService::class)->recalculateAllForAccount($account);
            $account->refresh();
            $this->formCurrentBalance = (float) $account->current_balance;
        } else {
            $account->update(['current_balance' => round($this->formCurrentBalance, 2)]);
        }

        $this->saved = true;
        $this->dispatch('accountSwitched');
    }

    public function recalculateBalance(): void
    {
        $account = $this->activeAccount;
        if (! $account) return;

        app(TargetCalculationService::class)->recalculateAllForAccount($account);

        $account->refresh();
        $this->formCurrentBalance = (float) $account->current_balance;
        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.account-settings');
    }
}
