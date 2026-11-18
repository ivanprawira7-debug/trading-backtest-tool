<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Symbol extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'pip_size',
        'contract_size',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pip_size' => 'decimal:5',
            'contract_size' => 'integer',
        ];
    }

    /**
     * The backtest sessions that use this symbol.
     */
    public function backtestSessions(): BelongsToMany
    {
        return $this->belongsToMany(BacktestSession::class, 'backtest_session_symbols');
    }

    /**
     * All trades recorded for this symbol.
     */
    public function trades(): HasMany
    {
        return $this->hasMany(BacktestTrade::class);
    }
}
