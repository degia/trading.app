<?php

namespace App\Models;

use App\Models\Scopes\ActiveAccountScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyLog extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new ActiveAccountScope);
    }

    protected $fillable = [
        'account_id',
        'log_date',
        'status',
        'balance',
        'daily_percent',
        'profit_amount',
        'loss_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'balance' => 'decimal:2',
            'daily_percent' => 'decimal:2',
            'profit_amount' => 'decimal:2',
            'loss_amount' => 'decimal:2',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(Target::class);
    }

    public function getDayNameAttribute(): string
    {
        return $this->log_date->translatedFormat('l');
    }
}
