<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BacktestResult extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'backtest_session_id',
        'total_trades',
        'total_win',
        'total_loss',
        'win_rate',
        'gross_profit',
        'gross_loss',
        'net_profit',
        'final_balance',
        'max_drawdown_percent',
        'profit_factor',
        'max_consecutive_win',
        'max_consecutive_loss',
        'calculated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_trades' => 'integer',
            'total_win' => 'integer',
            'total_loss' => 'integer',
            'win_rate' => 'decimal:2',
            'gross_profit' => 'decimal:2',
            'gross_loss' => 'decimal:2',
            'net_profit' => 'decimal:2',
            'final_balance' => 'decimal:2',
            'max_drawdown_percent' => 'decimal:2',
            'profit_factor' => 'decimal:2',
            'max_consecutive_win' => 'integer',
            'max_consecutive_loss' => 'integer',
            'calculated_at' => 'datetime',
        ];
    }

    /**
     * The backtest session this result belongs to.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(BacktestSession::class, 'backtest_session_id');
    }
}
