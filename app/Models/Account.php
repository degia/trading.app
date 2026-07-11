<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'initial_balance',
        'current_balance',
        'currency',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rules(): HasOne
    {
        return $this->hasOne(AccountRule::class);
    }

    public function getOrCreateRules(): AccountRule
    {
        return $this->rules()->firstOrCreate(['account_id' => $this->id], [
            'target_1_pct' => 5.00,
            'target_2_pct' => 10.00,
            'off_days' => ['saturday', 'sunday'],
        ]);
    }

    public function dailyLogs(): HasMany
    {
        return $this->hasMany(DailyLog::class);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(Target::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
