<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = \App\Models\Transaction::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'type' => fake()->randomElement(['deposit', 'withdrawal']),
            'amount' => fake()->randomFloat(2, 10, 200),
            'transaction_date' => fake()->dateTimeBetween('-60 days', 'now'),
            'notes' => fake()->optional(0.4)->sentence(),
        ];
    }

    public function deposit(): static
    {
        return $this->state(fn () => ['type' => 'deposit']);
    }

    public function withdrawal(): static
    {
        return $this->state(fn () => ['type' => 'withdrawal']);
    }
}
