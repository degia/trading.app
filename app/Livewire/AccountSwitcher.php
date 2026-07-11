<?php

namespace App\Livewire;

use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class AccountSwitcher extends Component
{
    public bool $showModal = false;

    public string $pendingType = '';

    public function getAccountProperty(): ?Account
    {
        return Auth::user()?->accounts()
            ->where('id', Session::get('active_account_id'))
            ->first();
    }

    public function getAccountsProperty()
    {
        return Auth::user()?->accounts()->get() ?? collect();
    }

    public function getActiveTypeProperty(): string
    {
        return Session::get('active_account_type', 'real');
    }

    public function promptSwitch(string $type): void
    {
        if ($type === $this->activeType) {
            return;
        }

        $this->pendingType = $type;
        $this->showModal = true;
    }

    public function confirmSwitch(): void
    {
        $user = Auth::user();
        $account = $user->accounts()->where('type', $this->pendingType)->first();

        if (! $account) {
            $account = Account::create([
                'user_id' => $user->id,
                'name' => $this->pendingType === 'real' ? 'Real Account' : 'Demo Account',
                'type' => $this->pendingType,
                'initial_balance' => 0,
                'current_balance' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]);
        }

        $user->update(['active_account_id' => $account->id]);
        Session::put('active_account_type', $this->pendingType);
        Session::put('active_account_id', $account->id);

        $this->showModal = false;
        $this->pendingType = '';

        $this->dispatch('accountSwitched');
        $this->redirect(route('dashboard'), navigate: true);
    }

    public function cancelSwitch(): void
    {
        $this->showModal = false;
        $this->pendingType = '';
    }

    public function render()
    {
        return view('livewire.account-switcher');
    }
}
