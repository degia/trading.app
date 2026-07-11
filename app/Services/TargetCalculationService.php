<?php

namespace App\Services;

use App\Models\Account;
use App\Models\DailyLog;
use App\Models\Scopes\ActiveAccountScope;
use App\Models\Target;
use Illuminate\Support\Facades\DB;

class TargetCalculationService
{
    public function calculateForNewEntry(Account $account, DailyLog $log): void
    {
        $prevLog = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->where('log_date', '<', $log->log_date)
            ->orderByDesc('log_date')
            ->first();

        $prevRunning5 = 0;
        $prevRunning10 = 0;
        $target5Amount = $account->initial_balance * 0.05;
        $target10Amount = $account->initial_balance * 0.10;

        if ($prevLog && $prevLog->status !== 'day_off') {
            $prevTargets = Target::withoutGlobalScope(ActiveAccountScope::class)
                ->where('account_id', $account->id)
                ->where('daily_log_id', $prevLog->id)
                ->get()
                ->keyBy('target_type');

            $prevRunning5 = (float) ($prevTargets->get('5pct')?->running_amount ?? 0);
            $prevRunning10 = (float) ($prevTargets->get('10pct')?->running_amount ?? 0);
            $target5Amount = (float) ($prevTargets->get('5pct')?->target_amount ?? $target5Amount);
            $target10Amount = (float) ($prevTargets->get('10pct')?->target_amount ?? $target10Amount);
        } elseif ($prevLog && $prevLog->status === 'day_off') {
            $target5Amount = (float) $prevLog->balance * 0.05;
            $target10Amount = (float) $prevLog->balance * 0.10;
        }

        if ($log->status === 'day_off') {
            $newBalance = $prevLog ? (float) $prevLog->balance : (float) $account->current_balance;
            $running5 = 0;
            $running10 = 0;
            $target5Amount = $newBalance * 0.05;
            $target10Amount = $newBalance * 0.10;
            $dailyPct = 0;
        } elseif ($log->status === 'profit') {
            $profitAmt = (float) $log->profit_amount;
            $newBalance = ($prevLog ? (float) $prevLog->balance : (float) $account->current_balance) + $profitAmt;
            $running5 = $prevRunning5 + $profitAmt;
            $running10 = $prevRunning10 + $profitAmt;
            $dailyPct = round(($profitAmt / $newBalance) * 100, 2);
        } else {
            $lossAmt = (float) $log->loss_amount;
            $newBalance = ($prevLog ? (float) $prevLog->balance : (float) $account->current_balance) - $lossAmt;
            $running5 = $prevRunning5 - $lossAmt;
            $running10 = $prevRunning10 - $lossAmt;
            $dailyPct = round(($lossAmt / $newBalance) * -100, 2);
        }

        $log->update([
            'balance' => round($newBalance, 2),
            'daily_percent' => $dailyPct,
        ]);

        Target::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->where('daily_log_id', $log->id)
            ->delete();

        if ($log->status !== 'day_off') {
            $closing5 = max(round($target5Amount - $running5, 2), 0);
            $closing10 = max(round($target10Amount - $running10, 2), 0);

            Target::create([
                'account_id' => $account->id,
                'daily_log_id' => $log->id,
                'target_type' => '5pct',
                'target_amount' => $target5Amount,
                'running_amount' => round($running5, 2),
                'target_closing' => $closing5,
                'status' => $closing5,
            ]);

            Target::create([
                'account_id' => $account->id,
                'daily_log_id' => $log->id,
                'target_type' => '10pct',
                'target_amount' => $target10Amount,
                'running_amount' => round($running10, 2),
                'target_closing' => $closing10,
                'status' => $closing10,
            ]);
        }

        $account->update(['current_balance' => round($newBalance, 2)]);

        $this->recalculateForward($account, $log);
    }

    public function recalculateForward(Account $account, DailyLog $afterLog): void
    {
        $futureLogs = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->where('log_date', '>', $afterLog->log_date)
            ->orderBy('log_date')
            ->get();

        $prevLog = $afterLog;
        $prevTargets = Target::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->where('daily_log_id', $afterLog->id)
            ->get()
            ->keyBy('target_type');

        $running5 = (float) ($prevTargets->get('5pct')?->running_amount ?? 0);
        $running10 = (float) ($prevTargets->get('10pct')?->running_amount ?? 0);
        $target5Amount = (float) ($prevTargets->get('5pct')?->target_amount ?? $account->initial_balance * 0.05);
        $target10Amount = (float) ($prevTargets->get('10pct')?->target_amount ?? $account->initial_balance * 0.10);
        $lastBalance = (float) $afterLog->balance;

        foreach ($futureLogs as $log) {
            if ($log->status === 'day_off') {
                $newBalance = $lastBalance;
                $running5 = 0;
                $running10 = 0;
                $target5Amount = $newBalance * 0.05;
                $target10Amount = $newBalance * 0.10;
                $dailyPct = 0;
            } elseif ($log->status === 'profit') {
                $profitAmt = (float) $log->profit_amount;
                $newBalance = $lastBalance + $profitAmt;
                $running5 += $profitAmt;
                $running10 += $profitAmt;
                $dailyPct = round(($profitAmt / $newBalance) * 100, 2);
            } else {
                $lossAmt = (float) $log->loss_amount;
                $newBalance = $lastBalance - $lossAmt;
                $running5 -= $lossAmt;
                $running10 -= $lossAmt;
                $dailyPct = round(($lossAmt / $newBalance) * -100, 2);
            }

            $log->update([
                'balance' => round($newBalance, 2),
                'daily_percent' => $dailyPct,
            ]);

            Target::withoutGlobalScope(ActiveAccountScope::class)
                ->where('account_id', $account->id)
                ->where('daily_log_id', $log->id)
                ->delete();

            if ($log->status !== 'day_off') {
                $closing5 = max(round($target5Amount - $running5, 2), 0);
                $closing10 = max(round($target10Amount - $running10, 2), 0);

                Target::create([
                    'account_id' => $account->id,
                    'daily_log_id' => $log->id,
                    'target_type' => '5pct',
                    'target_amount' => $target5Amount,
                    'running_amount' => round($running5, 2),
                    'target_closing' => $closing5,
                    'status' => $closing5,
                ]);

                Target::create([
                    'account_id' => $account->id,
                    'daily_log_id' => $log->id,
                    'target_type' => '10pct',
                    'target_amount' => $target10Amount,
                    'running_amount' => round($running10, 2),
                    'target_closing' => $closing10,
                    'status' => $closing10,
                ]);
            }

            $lastBalance = $newBalance;
        }

        $account->update(['current_balance' => round($lastBalance, 2)]);
    }

    public function recalculateAllForAccount(Account $account): void
    {
        $firstLog = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->orderBy('log_date')
            ->first();

        if (! $firstLog) return;

        Target::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->delete();

        $balance = (float) $account->initial_balance;
        $running5 = 0;
        $running10 = 0;
        $target5Amount = $balance * 0.05;
        $target10Amount = $balance * 0.10;

        $logs = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->orderBy('log_date')
            ->get();

        foreach ($logs as $log) {
            if ($log->status === 'day_off') {
                $running5 = 0;
                $running10 = 0;
                $target5Amount = $balance * 0.05;
                $target10Amount = $balance * 0.10;
                $dailyPct = 0;
            } elseif ($log->status === 'profit') {
                $profitAmt = (float) $log->profit_amount;
                $balance += $profitAmt;
                $running5 += $profitAmt;
                $running10 += $profitAmt;
                $dailyPct = round(($profitAmt / $balance) * 100, 2);
            } else {
                $lossAmt = (float) $log->loss_amount;
                $balance -= $lossAmt;
                $running5 -= $lossAmt;
                $running10 -= $lossAmt;
                $dailyPct = round(($lossAmt / $balance) * -100, 2);
            }

            $balance = round($balance, 2);

            $log->update([
                'balance' => $balance,
                'daily_percent' => $dailyPct,
            ]);

            if ($log->status !== 'day_off') {
                $closing5 = max(round($target5Amount - $running5, 2), 0);
                $closing10 = max(round($target10Amount - $running10, 2), 0);

                Target::create([
                    'account_id' => $account->id,
                    'daily_log_id' => $log->id,
                    'target_type' => '5pct',
                    'target_amount' => $target5Amount,
                    'running_amount' => round($running5, 2),
                    'target_closing' => $closing5,
                    'status' => $closing5,
                ]);

                Target::create([
                    'account_id' => $account->id,
                    'daily_log_id' => $log->id,
                    'target_type' => '10pct',
                    'target_amount' => $target10Amount,
                    'running_amount' => round($running10, 2),
                    'target_closing' => $closing10,
                    'status' => $closing10,
                ]);
            }
        }

        $account->update(['current_balance' => $balance]);
    }
}
