<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\DailyLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class TargetFactory extends Factory
{
    protected $model = \App\Models\Target::class;

    public function definition(): array
    {
        $targetType = fake()->randomElement(['target_1', 'target_2']);
        $targetAmount = fake()->randomFloat(2, 3, 20);

        return [
            'account_id' => Account::factory(),
            'daily_log_id' => DailyLog::factory(),
            'target_type' => $targetType,
            'target_amount' => $targetAmount,
            'running_amount' => fake()->randomFloat(2, -10, $targetAmount),
            'target_closing' => $targetAmount,
            'status' => fake()->randomFloat(2, -5, 5),
        ];
    }
}
