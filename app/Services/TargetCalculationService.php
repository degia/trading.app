<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountRule;
use App\Models\DailyLog;
use App\Models\Scopes\ActiveAccountScope;
use App\Models\Target;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TargetCalculationService
{
    private function getRules(Account $account): AccountRule
    {
        return $account->getOrCreateRules();
    }

    public function calculateForNewEntry(Account $account, DailyLog $log): void
    {
        $rules = $this->getRules($account);

        $prevLog = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->where('log_date', '<', $log->log_date)
            ->orderByDesc('log_date')
            ->first();

        $prevRunning5 = 0;
        $prevRunning10 = 0;
        $target5Amount = $rules->getTarget1Amount((float) $account->initial_balance);
        $target10Amount = $rules->getTarget2Amount((float) $account->initial_balance);

        if ($prevLog && $prevLog->status !== 'day_off') {
            $prevTargets = Target::withoutGlobalScope(ActiveAccountScope::class)
                ->where('account_id', $account->id)
                ->where('daily_log_id', $prevLog->id)
                ->get()
                ->keyBy('target_type');

            $prevRunning5 = (float) ($prevTargets->get('target_1')?->running_amount ?? 0);
            $prevRunning10 = (float) ($prevTargets->get('target_2')?->running_amount ?? 0);
            $target5Amount = (float) ($prevTargets->get('target_1')?->target_amount ?? $target5Amount);
            $target10Amount = (float) ($prevTargets->get('target_2')?->target_amount ?? $target10Amount);
        } elseif ($prevLog && $prevLog->status === 'day_off') {
            $target5Amount = $rules->getTarget1Amount((float) $prevLog->balance);
            $target10Amount = $rules->getTarget2Amount((float) $prevLog->balance);
        }

        if ($log->status === 'day_off') {
            $newBalance = $prevLog ? (float) $prevLog->balance : (float) $account->current_balance;
            $running5 = 0;
            $running10 = 0;
            $target5Amount = $rules->getTarget1Amount($newBalance);
            $target10Amount = $rules->getTarget2Amount($newBalance);
            $dailyPct = 0;
        } else {
            $prevBalance = $prevLog ? (float) $prevLog->balance : (float) $account->current_balance;
            if ($log->status === 'profit') {
                $profitAmt = (float) $log->profit_amount;
                $newBalance = $prevBalance + $profitAmt;
                $running5 = $prevRunning5 + $profitAmt;
                $running10 = $prevRunning10 + $profitAmt;
                $dailyPct = $prevBalance > 0 ? round(($profitAmt / $prevBalance) * 100, 2) : 0;
            } else {
                $lossAmt = (float) $log->loss_amount;
                $newBalance = $prevBalance - $lossAmt;
                $running5 = $prevRunning5 - $lossAmt;
                $running10 = $prevRunning10 - $lossAmt;
                $dailyPct = $prevBalance > 0 ? round(($lossAmt / $prevBalance) * -100, 2) : 0;
            }
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
                'target_type' => 'target_1',
                'target_amount' => $target5Amount,
                'running_amount' => round($running5, 2),
                'target_closing' => $closing5,
                'status' => $closing5,
            ]);

            Target::create([
                'account_id' => $account->id,
                'daily_log_id' => $log->id,
                'target_type' => 'target_2',
                'target_amount' => $target10Amount,
                'running_amount' => round($running10, 2),
                'target_closing' => $closing10,
                'status' => $closing10,
            ]);
        }

        $account->update(['current_balance' => round($newBalance, 2)]);

        $this->recalculateAllForAccount($account);
    }

    public function recalculateForward(Account $account, DailyLog $afterLog): void
    {
        $rules = $this->getRules($account);

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

        $running5 = (float) ($prevTargets->get('target_1')?->running_amount ?? 0);
        $running10 = (float) ($prevTargets->get('target_2')?->running_amount ?? 0);
        $target5Amount = (float) ($prevTargets->get('target_1')?->target_amount ?? $rules->getTarget1Amount((float) $account->initial_balance));
        $target10Amount = (float) ($prevTargets->get('target_2')?->target_amount ?? $rules->getTarget2Amount((float) $account->initial_balance));
        $lastBalance = (float) $afterLog->balance;

        foreach ($futureLogs as $log) {
            if ($log->status === 'day_off') {
                $newBalance = $lastBalance;
                $running5 = 0;
                $running10 = 0;
                $target5Amount = $rules->getTarget1Amount($newBalance);
                $target10Amount = $rules->getTarget2Amount($newBalance);
                $dailyPct = 0;
            } else {
                $prevBalance = $lastBalance;
                if ($log->status === 'profit') {
                    $profitAmt = (float) $log->profit_amount;
                    $newBalance = $prevBalance + $profitAmt;
                    $running5 += $profitAmt;
                    $running10 += $profitAmt;
                    $dailyPct = $prevBalance > 0 ? round(($profitAmt / $prevBalance) * 100, 2) : 0;
                } else {
                    $lossAmt = (float) $log->loss_amount;
                    $newBalance = $prevBalance - $lossAmt;
                    $running5 -= $lossAmt;
                    $running10 -= $lossAmt;
                    $dailyPct = $prevBalance > 0 ? round(($lossAmt / $prevBalance) * -100, 2) : 0;
                }
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
                    'target_type' => 'target_1',
                    'target_amount' => $target5Amount,
                    'running_amount' => round($running5, 2),
                    'target_closing' => $closing5,
                    'status' => $closing5,
                ]);

                Target::create([
                    'account_id' => $account->id,
                    'daily_log_id' => $log->id,
                    'target_type' => 'target_2',
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
        $rules = $this->getRules($account);

        Target::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->delete();

        $transactionsByDate = $this->getTransactionsByDate($account);

        $balance = 0;
        $running5 = 0;
        $running10 = 0;
        $target5Amount = $rules->getTarget1Amount($balance);
        $target10Amount = $rules->getTarget2Amount($balance);

        $logs = DailyLog::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->orderBy('log_date')
            ->get();

        if ($logs->isEmpty()) {
            foreach ($transactionsByDate as $txns) {
                foreach ($txns as $txn) {
                    $balance = $this->applyTransactionToBalance($balance, $txn);
                }
            }

            $account->update(['current_balance' => round($balance, 2)]);
            return;
        }

        $firstLogDate = $logs->first()->log_date->format('Y-m-d');
        $lastLogDate = null;

        foreach ($transactionsByDate as $dateKey => $txns) {
            if ($dateKey < $firstLogDate) {
                foreach ($txns as $txn) {
                    $balance = $this->applyTransactionToBalance($balance, $txn);
                }
            }
        }

        foreach ($logs as $log) {
            $dateKey = $log->log_date->format('Y-m-d');

            if (isset($transactionsByDate[$dateKey])) {
                foreach ($transactionsByDate[$dateKey] as $txn) {
                    $balance = $this->applyTransactionToBalance($balance, $txn);
                }
            }

            if ($log->status === 'day_off') {
                $running5 = 0;
                $running10 = 0;
                $target5Amount = $rules->getTarget1Amount($balance);
                $target10Amount = $rules->getTarget2Amount($balance);
                $dailyPct = 0;
            } else {
                $prevBalance = $balance;
                if ($log->status === 'profit') {
                    $profitAmt = (float) $log->profit_amount;
                    $balance += $profitAmt;
                    $running5 += $profitAmt;
                    $running10 += $profitAmt;
                    $dailyPct = $prevBalance > 0 ? round(($profitAmt / $prevBalance) * 100, 2) : 0;
                } else {
                    $lossAmt = (float) $log->loss_amount;
                    $balance -= $lossAmt;
                    $running5 -= $lossAmt;
                    $running10 -= $lossAmt;
                    $dailyPct = $prevBalance > 0 ? round(($lossAmt / $prevBalance) * -100, 2) : 0;
                }
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
                    'target_type' => 'target_1',
                    'target_amount' => $target5Amount,
                    'running_amount' => round($running5, 2),
                    'target_closing' => $closing5,
                    'status' => $closing5,
                ]);

                Target::create([
                    'account_id' => $account->id,
                    'daily_log_id' => $log->id,
                    'target_type' => 'target_2',
                    'target_amount' => $target10Amount,
                    'running_amount' => round($running10, 2),
                    'target_closing' => $closing10,
                    'status' => $closing10,
                ]);
            }

            $lastLogDate = $dateKey;
        }

        foreach ($transactionsByDate as $dateKey => $txns) {
            if ($lastLogDate && $dateKey > $lastLogDate) {
                foreach ($txns as $txn) {
                    $balance = $this->applyTransactionToBalance($balance, $txn);
                }
            }
        }

        $account->update(['current_balance' => round($balance, 2)]);
    }

    private function getTransactionsByDate(Account $account): \Illuminate\Support\Collection
    {
        return Transaction::withoutGlobalScope(ActiveAccountScope::class)
            ->where('account_id', $account->id)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($t) => $t->transaction_date->format('Y-m-d'));
    }

    private function applyTransactionToBalance(float $balance, Transaction $txn): float
    {
        return match ($txn->type) {
            'deposit' => $balance + (float) $txn->amount,
            'withdrawal' => $balance - (float) $txn->amount,
            default => $balance,
        };
    }
}
