<?php

namespace Database\Seeders;

use App\Models\Account;
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

        if ($type === 'real') {
            $user->update(['active_account_id' => $account->id]);
        }

        $balance = $initialBalance;
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->startOfDay();

        $running5pct = 0;
        $running10pct = 0;
        $target5pctAmount = $balance * 0.05;
        $target10pctAmount = $balance * 0.10;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayOfWeek = $date->dayOfWeek;

            if ($dayOfWeek === Carbon::SATURDAY || $dayOfWeek === Carbon::SUNDAY) {
                $status = 'day_off';
                $profitAmount = 0;
                $lossAmount = 0;
                $dailyPct = 0;
                $running5pct = 0;
                $running10pct = 0;
                $target5pctAmount = $balance * 0.05;
                $target10pctAmount = $balance * 0.10;
            } else {
                $roll = mt_rand(1, 100);
                if ($roll <= 55) {
                    $status = 'profit';
                    $profitAmount = round(mt_rand(200, 1500) / 100, 2);
                    $lossAmount = 0;
                    $balance += $profitAmount;
                    $dailyPct = round(($profitAmount / $balance) * 100, 2);
                    $running5pct += $profitAmount;
                    $running10pct += $profitAmount;
                } elseif ($roll <= 80) {
                    $status = 'loss';
                    $lossAmount = round(mt_rand(100, 1200) / 100, 2);
                    $profitAmount = 0;
                    $balance -= $lossAmount;
                    $dailyPct = round(($lossAmount / $balance) * -100, 2);
                    $running5pct -= $lossAmount;
                    $running10pct -= $lossAmount;
                } else {
                    $status = 'day_off';
                    $profitAmount = 0;
                    $lossAmount = 0;
                    $dailyPct = 0;
                    $running5pct = 0;
                    $running10pct = 0;
                    $target5pctAmount = $balance * 0.05;
                    $target10pctAmount = $balance * 0.10;
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
                $closing5 = round($target5pctAmount - $running5pct, 2);
                $closing10 = round($target10pctAmount - $running10pct, 2);

                Target::create([
                    'account_id' => $account->id,
                    'daily_log_id' => $dailyLog->id,
                    'target_type' => '5pct',
                    'target_amount' => $target5pctAmount,
                    'running_amount' => $running5pct,
                    'target_closing' => $closing5 > 0 ? $closing5 : 0,
                    'status' => $closing5,
                ]);

                Target::create([
                    'account_id' => $account->id,
                    'daily_log_id' => $dailyLog->id,
                    'target_type' => '10pct',
                    'target_amount' => $target10pctAmount,
                    'running_amount' => $running10pct,
                    'target_closing' => $closing10 > 0 ? $closing10 : 0,
                    'status' => $closing10,
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
