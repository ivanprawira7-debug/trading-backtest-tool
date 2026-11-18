<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BacktestSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'initial_capital',
        'default_lot',
        'auto_fill_sl_tp',
        'default_sl_pips',
        'default_tp_pips',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'initial_capital' => 'decimal:2',
            'default_lot' => 'decimal:2',
            'auto_fill_sl_tp' => 'boolean',
            'default_sl_pips' => 'decimal:2',
            'default_tp_pips' => 'decimal:2',
        ];
    }

    /**
     * The user who owns this backtest session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The symbols included in this session.
     */
    public function symbols(): BelongsToMany
    {
        return $this->belongsToMany(Symbol::class, 'backtest_session_symbols');
    }

    /**
     * All trades in this backtest session.
     */
    public function trades(): HasMany
    {
        return $this->hasMany(BacktestTrade::class);
    }

    /**
     * The summary result of this backtest session.
     */
    public function result(): HasOne
    {
        return $this->hasOne(BacktestResult::class);
    }

    /**
     * Whether the session status is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETED';
    }

    /**
     * Whether the session status is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }
}
