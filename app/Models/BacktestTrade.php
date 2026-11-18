<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BacktestTrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'backtest_session_id',
        'symbol_id',
        'sequence_number',
        'trade_type',
        'entry_date',
        'entry_price',
        'stop_loss',
        'take_profit',
        'exit_date',
        'result_status',
        'pips_gained',
        'profit_loss',
        'balance_after',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'exit_date' => 'date',
            'entry_price' => 'decimal:5',
            'stop_loss' => 'decimal:5',
            'take_profit' => 'decimal:5',
            'pips_gained' => 'decimal:2',
            'profit_loss' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'sequence_number' => 'integer',
        ];
    }

    /**
     * The backtest session this trade belongs to.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(BacktestSession::class, 'backtest_session_id');
    }

    /**
     * The symbol traded.
     */
    public function symbol(): BelongsTo
    {
        return $this->belongsTo(Symbol::class);
    }

    /**
     * Whether this was a winning trade (TP hit).
     */
    public function isWin(): bool
    {
        return $this->result_status === 'TP';
    }

    /**
     * Whether this was a losing trade (SL hit).
     */
    public function isLoss(): bool
    {
        return $this->result_status === 'SL';
    }

    /**
     * Whether this was a BUY trade.
     */
    public function isBuy(): bool
    {
        return $this->trade_type === 'BUY';
    }

    /**
     * Whether this was a SELL trade.
     */
    public function isSell(): bool
    {
        return $this->trade_type === 'SELL';
    }
}
