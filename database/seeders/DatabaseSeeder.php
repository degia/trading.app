<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountRule;
use App\Models\DailyLog;
use App\Models\Target;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Gia',
            'email' => 'admin@tradeledger.io',
            'password' => bcrypt('password'),
        ]);

        $this->seedAccount($user, 'real', 'Real Account', 150.00);
        $this->seedAccount($user, 'demo', 'Demo Account', 1000.00);
    }

    private function seedAccount(User $user, string $type, string $name, float $initialBalance): void
    {
        $account = Account::create([
            'user_id' => $user->id,
            'name' => $name,
            'type' => $type,
            'initial_balance' => $initialBalance,
            'current_balance' => $initialBalance,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $rules = AccountRule::create([
            'account_id' => $account->id,
            'target_1_pct' => 5.00,
            'target_2_pct' => 10.00,
            'off_days' => ['saturday', 'sunday'],
        ]);

        if ($type === 'real') {
            $user->update(['active_account_id' => $account->id]);
        }

        $balance = $initialBalance;
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->startOfDay();

        $running1 = 0;
        $running2 = 0;
        $target1Amount = $rules->getTarget1Amount($balance);
        $target2Amount = $rules->getTarget2Amount($balance);

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayOfWeek = $date->dayOfWeek;

            if ($rules->isOffDay($date->englishDayOfWeek)) {
                $status = 'day_off';
                $profitAmount = 0;
                $lossAmount = 0;
                $dailyPct = 0;
                $running1 = 0;
                $running2 = 0;
                $target1Amount = $rules->getTarget1Amount($balance);
                $target2Amount = $rules->getTarget2Amount($balance);
            } else {
                $roll = mt_rand(1, 100);
                if ($roll <= 55) {
                    $status = 'profit';
                    $profitAmount = round(mt_rand(200, 1500) / 100, 2);
                    $lossAmount = 0;
                    $balance += $profitAmount;
                    $dailyPct = round(($profitAmount / $balance) * 100, 2);
                    $running1 += $profitAmount;
                    $running2 += $profitAmount;
                } elseif ($roll <= 80) {
                    $status = 'loss';
                    $lossAmount = round(mt_rand(100, 1200) / 100, 2);
                    $profitAmount = 0;
                    $balance -= $lossAmount;
                    $dailyPct = round(($lossAmount / $balance) * -100, 2);
                    $running1 -= $lossAmount;
                    $running2 -= $lossAmount;
                } else {
                    $status = 'day_off';
                    $profitAmount = 0;
                    $lossAmount = 0;
                    $dailyPct = 0;
                    $running1 = 0;
                    $running2 = 0;
                    $target1Amount = $rules->getTarget1Amount($balance);
                    $target2Amount = $rules->getTarget2Amount($balance);
                }
            }

            $balance = round($balance, 2);

            $dailyLog = DailyLog::create([
                'account_id' => $account->id,
                'log_date' => $date->toDateString(),
                'status' => $status,
                'balance' => $balance,
                'daily_percent' => $dailyPct,
                'profit_amount' => $profitAmount,
                'loss_amount' => $lossAmount,
                'notes' => $status === 'profit'
                    ? 'Entry sesuai setup SMC'
                    : ($status === 'loss' ? 'Fake breakout, SL kena' : null),
            ]);

            if ($status !== 'day_off') {
                $closing1 = max(round($target1Amount - $running1, 2), 0);
                $closing2 = max(round($target2Amount - $running2, 2), 0);

                Target::create([
                    'account_id' => $account->id,
                    'daily_log_id' => $dailyLog->id,
                    'target_type' => 'target_1',
                    'target_amount' => $target1Amount,
                    'running_amount' => $running1,
                    'target_closing' => $closing1,
                    'status' => $closing1,
                ]);

                Target::create([
                    'account_id' => $account->id,
                    'daily_log_id' => $dailyLog->id,
                    'target_type' => 'target_2',
                    'target_amount' => $target2Amount,
                    'running_amount' => $running2,
                    'target_closing' => $closing2,
                    'status' => $closing2,
                ]);
            }
        }

        $account->update(['current_balance' => $balance]);

        Transaction::create([
            'account_id' => $account->id,
            'type' => 'deposit',
            'amount' => $initialBalance,
            'transaction_date' => $startDate->toDateString(),
            'notes' => 'Initial deposit',
        ]);

        if ($type === 'real') {
            Transaction::create([
                'account_id' => $account->id,
                'type' => 'withdrawal',
                'amount' => 25.00,
                'transaction_date' => $startDate->copy()->addDays(10)->toDateString(),
                'notes' => 'Withdraw profit minggu pertama',
            ]);
        }
    }
}
