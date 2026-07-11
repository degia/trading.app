<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'target_1_pct',
        'target_2_pct',
        'off_days',
    ];

    protected function casts(): array
    {
        return [
            'target_1_pct' => 'decimal:2',
            'target_2_pct' => 'decimal:2',
            'off_days' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function isOffDay(string $dayName): bool
    {
        return in_array(strtolower($dayName), $this->off_days ?? ['saturday', 'sunday']);
    }

    public function getTarget1Amount(float $balance): float
    {
        return round($balance * ($this->target_1_pct / 100), 2);
    }

    public function getTarget2Amount(float $balance): float
    {
        return round($balance * ($this->target_2_pct / 100), 2);
    }
}
