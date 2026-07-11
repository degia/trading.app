<?php

use App\Livewire\Forms\LoginForm;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public string $accountType = 'real';

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = Auth::user();

        $account = Account::firstOrCreate(
            [
                'user_id' => $user->id,
                'type' => $this->accountType,
            ],
            [
                'name' => $this->accountType === 'real' ? 'Real Account' : 'Demo Account',
                'initial_balance' => 0,
                'current_balance' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]
        );

        $user->update(['active_account_id' => $account->id]);
        Session::put('active_account_type', $this->accountType);
        Session::put('active_account_id', $account->id);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="flex items-center gap-2.5 mb-8">
        <div class="w-9 h-9 rounded-[10px] bg-gradient-to-br from-profit to-profit-dim flex items-center justify-center font-display font-bold text-base text-[#04231a]">T</div>
        <span class="font-display font-semibold text-[19px] tracking-tight">TradeLedger</span>
    </div>

    <h2 class="font-display text-[26px] font-semibold tracking-tight mb-1.5">Masuk ke akun kamu</h2>
    <p class="text-sm text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mb-7">Kelola dan pantau performa trading harianmu</p>

    <div class="flex bg-white/[0.04] border border-white/[0.09] rounded-xl p-1 mb-6 gap-1 dark:bg-white/[0.04] dark:border-white/[0.09] light:bg-black/[0.03] light:border-black/[0.08]">
        <button type="button" wire:click="$set('accountType', 'real')"
                class="flex-1 text-center py-2.5 rounded-[9px] text-[13px] font-medium font-body transition-all duration-200
                {{ $accountType === 'real' ? 'bg-profit/14 text-profit' : 'text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70]' }}">
            Real Account
        </button>
        <button type="button" wire:click="$set('accountType', 'demo')"
                class="flex-1 text-center py-2.5 rounded-[9px] text-[13px] font-medium font-body transition-all duration-200
                {{ $accountType === 'demo' ? 'bg-target/14 text-target' : 'text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70]' }}">
            Demo Account
        </button>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <div class="mb-4">
            <x-input-label for="email" value="Email atau Username" />
            <x-text-input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username" placeholder="admin@tradeledger.io" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="password" value="Password" />
            <x-text-input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••••" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mb-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-white/20 bg-white/[0.04] text-profit focus:ring-profit/50" name="remember">
                <span class="ms-2 text-sm text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70]">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-[#8b8b93] hover:text-white dark:text-[#8b8b93] dark:hover:text-white light:text-[#6b6b70] light:hover:text-ink underline" href="{{ route('password.request') }}" wire:navigate>
                    Forgot password?
                </a>
            @endif
        </div>

        <x-primary-button>Masuk ke Dashboard</x-primary-button>
    </form>

    <div class="text-center text-xs text-[#8b8b93] dark:text-[#8b8b93] text-[#6b6b70] mt-5">
        Data akan diisolasi per tipe akun — Real dan Demo terpisah penuh
    </div>
</div>
