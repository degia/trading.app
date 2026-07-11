<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyLogFactory extends Factory
{
    protected $model = \App\Models\DailyLog::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['profit', 'loss', 'day_off', 'pending']);
        $profitAmount = $status === 'profit' ? fake()->randomFloat(2, 1, 30) : 0;
        $lossAmount = $status === 'loss' ? fake()->randomFloat(2, 1, 20) : 0;
        $balance = fake()->randomFloat(2, 100, 300);

        return [
            'account_id' => Account::factory(),
            'log_date' => fake()->dateTimeBetween('-60 days', 'now'),
            'status' => $status,
            'balance' => $balance,
            'daily_percent' => $status === 'day_off' ? 0 : fake()->randomFloat(2, -5, 10),
            'profit_amount' => $profitAmount,
            'loss_amount' => $lossAmount,
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function profit(): static
    {
        return $this->state(fn () => [
            'status' => 'profit',
            'profit_amount' => fake()->randomFloat(2, 2, 25),
            'loss_amount' => 0,
        ]);
    }

    public function loss(): static
    {
        return $this->state(fn () => [
            'status' => 'loss',
            'profit_amount' => 0,
            'loss_amount' => fake()->randomFloat(2, 2, 15),
        ]);
    }

    public function dayOff(): static
    {
        return $this->state(fn () => [
            'status' => 'day_off',
            'daily_percent' => 0,
            'profit_amount' => 0,
            'loss_amount' => 0,
        ]);
    }
}
