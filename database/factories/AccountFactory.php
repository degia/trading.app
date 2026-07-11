<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = \App\Models\Account::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['real', 'demo']);
        $balance = fake()->randomFloat(2, 50, 500);

        return [
            'user_id' => User::factory(),
            'name' => $type === 'real' ? 'Real Account' : 'Demo Account',
            'type' => $type,
            'initial_balance' => $balance,
            'current_balance' => $balance,
            'currency' => 'USD',
            'is_active' => true,
        ];
    }

    public function real(): static
    {
        return $this->state(fn () => ['type' => 'real', 'name' => 'Real Account']);
    }

    public function demo(): static
    {
        return $this->state(fn () => ['type' => 'demo', 'name' => 'Demo Account']);
    }
}
