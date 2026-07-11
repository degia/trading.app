<?php

namespace App\Livewire;

use App\Models\AccountRule;
use App\Services\TargetCalculationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TargetRules extends Component
{
    public int $target_1_pct = 5;
    public int $target_2_pct = 10;
    public array $offDays = ['saturday', 'sunday'];

    public bool $showSaved = false;

    protected $listeners = ['accountSwitched' => '$refresh'];

    public function allDays(): array
    {
        return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    }

    public function mount(): void
    {
        $this->loadRules();
    }

    public function loadRules(): void
    {
        $account = $this->getAccount();
        if (! $account) return;

        $rules = $account->getOrCreateRules();
        $this->target_1_pct = (int) $rules->target_1_pct;
        $this->target_2_pct = (int) $rules->target_2_pct;
        $this->offDays = $rules->off_days ?? ['saturday', 'sunday'];
    }

    public function toggleOffDay(string $day): void
    {
        if (in_array($day, $this->offDays)) {
            $this->offDays = array_values(array_diff($this->offDays, [$day]));
        } else {
            $this->offDays[] = $day;
        }
    }

    public function save(): void
    {
        $account = $this->getAccount();
        if (! $account) return;

        $rules = $account->getOrCreateRules();
        $rules->update([
            'target_1_pct' => max(1, min(100, $this->target_1_pct)),
            'target_2_pct' => max(1, min(100, $this->target_2_pct)),
            'off_days' => $this->offDays,
        ]);

        $service = app(TargetCalculationService::class);
        $service->recalculateAllForAccount($account);

        $this->showSaved = true;
        $this->dispatch('refreshDashboard');

        $this->showSaved = false;
    }

    private function getAccount()
    {
        $accountId = Session::get('active_account_id');

        return Auth::user()->accounts()->where('id', $accountId)->first()
            ?? Auth::user()->accounts()->first();
    }

    public function getActiveAccountProperty()
    {
        return $this->getAccount();
    }

    public function render()
    {
        return view('livewire.target-rules');
    }
}
