<?php

namespace App\Services;

use App\Models\BacktestResult;
use App\Models\BacktestSession;
use App\Models\BacktestTrade;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class BacktestCalculationService
{
    /**
     * Auto-fill stop loss and take profit prices for a trade based on entry price,
     * trade type, and pip target settings.
     *
     * @param  float  $entryPrice The entry price
     * @param  string $tradeType  'BUY' or 'SELL'
     * @param  float  $slPips     Stop loss distance in pips
     * @param  float  $tpPips     Take profit distance in pips
     * @param  float  $pipSize    The pip size for the symbol (e.g. 0.01 for XAUUSD, 0.0001 for EURUSD)
     * @return array{stop_loss: float, take_profit: float}
     */
    public function autoFillSlTp(
        float $entryPrice,
        string $tradeType,
        float $slPips,
        float $tpPips,
        float $pipSize
    ): array {
        if (strtoupper($tradeType) === 'BUY') {
            return [
                'stop_loss' => round($entryPrice - ($slPips * $pipSize), 5),
                'take_profit' => round($entryPrice + ($tpPips * $pipSize), 5),
            ];
        }

        return [
            'stop_loss' => round($entryPrice + ($slPips * $pipSize), 5),
            'take_profit' => round($entryPrice - ($tpPips * $pipSize), 5),
        ];
    }

    /**
     * Apply auto-fill SL and TP values across all trades within a backtest session.
     *
     * @param  BacktestSession $session
     * @param  float           $slPips
     * @param  float           $tpPips
     * @return void
     */
    public function applyAutoFillToSession(BacktestSession $session, float $slPips, float $tpPips): void
    {
        $trades = $session->trades()->with('symbol')->get();

        foreach ($trades as $trade) {
            $pipSize = (float) $trade->symbol->pip_size;

            $prices = $this->autoFillSlTp(
                (float) $trade->entry_price,
                $trade->trade_type,
                $slPips,
                $tpPips,
                $pipSize
            );

            $trade->update($prices);
        }

        $session->update([
            'auto_fill_sl_tp' => true,
            'default_sl_pips' => $slPips,
            'default_tp_pips' => $tpPips,
        ]);
    }

    /**
     * Calculate pips gained for an individual trade.
     *
     * BUY:
     *   TP: (take_profit - entry_price) / pip_size
     *   SL: (stop_loss - entry_price) / pip_size (negative)
     *
     * SELL:
     *   TP: (entry_price - take_profit) / pip_size
     *   SL: (entry_price - stop_loss) / pip_size (negative)
     *
     * @param  BacktestTrade $trade
     * @param  float         $pipSize
     * @return float
     */
    public function calculatePipsGained(BacktestTrade $trade, float $pipSize): float
    {
        $entryPrice = (float) $trade->entry_price;
        $stopLoss = (float) $trade->stop_loss;
        $takeProfit = (float) $trade->take_profit;

        if ($pipSize <= 0) {
            $pipSize = 0.0001;
        }

        if (strtoupper($trade->trade_type) === 'BUY') {
            if ($trade->result_status === 'TP') {
                return round(($takeProfit - $entryPrice) / $pipSize, 2);
            }

            return round(($stopLoss - $entryPrice) / $pipSize, 2);
        }

        // SELL
        if ($trade->result_status === 'TP') {
            return round(($entryPrice - $takeProfit) / $pipSize, 2);
        }

        return round(($entryPrice - $stopLoss) / $pipSize, 2);
    }

    /**
     * Calculate profit/loss in dollars for an individual trade.
     *
     * Formula: pips_gained * default_lot * (pip_size * contract_size)
     *
     * @param  float $pipsGained
     * @param  float $lotSize
     * @param  float $pipSize
     * @param  int   $contractSize
     * @return float
     */
    public function calculateProfitLoss(
        float $pipsGained,
        float $lotSize,
        float $pipSize,
        int $contractSize
    ): float {
        $pipValue = $pipSize * $contractSize;
        $profitLoss = $pipsGained * $lotSize * $pipValue;

        return round($profitLoss, 2);
    }

    /**
     * Process all trades in a session sequentially: calculate pips gained,
     * profit/loss ($), and running balance after each trade.
     *
     * @param  BacktestSession $session
     * @return Collection<int, BacktestTrade>
     */
    public function processSessionTrades(BacktestSession $session): Collection
    {
        $session->loadMissing('trades.symbol');

        $trades = $session->trades()->orderBy('sequence_number')->get();
        $balance = (float) $session->initial_capital;
        $lotSize = (float) $session->default_lot;

        foreach ($trades as $trade) {
            $symbol = $trade->symbol;
            $pipSize = (float) ($symbol?->pip_size ?? 0.0001);
            $contractSize = (int) ($symbol?->contract_size ?? 100);

            $pipsGained = $this->calculatePipsGained($trade, $pipSize);
            $profitLoss = $this->calculateProfitLoss($pipsGained, $lotSize, $pipSize, $contractSize);

            $balance += $profitLoss;

            $trade->update([
                'pips_gained' => $pipsGained,
                'profit_loss' => $profitLoss,
                'balance_after' => round($balance, 2),
            ]);
        }

        return $trades->fresh();
    }

    /**
     * Generate summary statistics for a session and persist them into backtest_results.
     *
     * @param  BacktestSession $session
     * @return BacktestResult
     */
    public function generateSummary(BacktestSession $session): BacktestResult
    {
        $trades = $session->trades()->orderBy('sequence_number')->get();

        if ($trades->isEmpty()) {
            throw new InvalidArgumentException('Cannot generate backtest summary for a session without trades.');
        }

        $totalTrades = $trades->count();
        $wins = $trades->where('result_status', 'TP');
        $losses = $trades->where('result_status', 'SL');

        $totalWin = $wins->count();
        $totalLoss = $losses->count();
        $winRate = $totalTrades > 0 ? round(($totalWin / $totalTrades) * 100, 2) : 0.0;

        $grossProfit = round((float) $wins->sum('profit_loss'), 2);
        $grossLoss = round(abs((float) $losses->sum('profit_loss')), 2);
        $netProfit = round($grossProfit - $grossLoss, 2);

        $finalBalance = (float) $trades->last()->balance_after;

        $maxDrawdownPercent = $this->calculateMaxDrawdown($trades, (float) $session->initial_capital);

        $profitFactor = $grossLoss > 0
            ? round($grossProfit / $grossLoss, 2)
            : ($grossProfit > 0 ? 999.99 : 0.0);

        [$maxConsecutiveWin, $maxConsecutiveLoss] = $this->calculateStreaks($trades);

        $result = BacktestResult::updateOrCreate(
            ['backtest_session_id' => $session->id],
            [
                'total_trades' => $totalTrades,
                'total_win' => $totalWin,
                'total_loss' => $totalLoss,
                'win_rate' => $winRate,
                'gross_profit' => $grossProfit,
                'gross_loss' => $grossLoss,
                'net_profit' => $netProfit,
                'final_balance' => $finalBalance,
                'max_drawdown_percent' => $maxDrawdownPercent,
                'profit_factor' => $profitFactor,
                'max_consecutive_win' => $maxConsecutiveWin,
                'max_consecutive_loss' => $maxConsecutiveLoss,
                'calculated_at' => Carbon::now(),
            ]
        );

        $session->update(['status' => 'COMPLETED']);

        return $result;
    }

    /**
     * Execute full calculation workflow: process trades and generate summary.
     *
     * @param  BacktestSession $session
     * @return BacktestResult
     */
    public function calculate(BacktestSession $session): BacktestResult
    {
        $this->processSessionTrades($session);

        return $this->generateSummary($session);
    }

    /**
     * Calculate maximum drawdown percentage from highest equity peak.
     *
     * @param  Collection<int, BacktestTrade> $trades
     * @param  float                          $initialCapital
     * @return float
     */
    public function calculateMaxDrawdown(Collection $trades, float $initialCapital): float
    {
        $peak = $initialCapital;
        $maxDrawdown = 0.0;

        foreach ($trades as $trade) {
            $balanceAfter = (float) $trade->balance_after;

            if ($balanceAfter > $peak) {
                $peak = $balanceAfter;
            }

            if ($peak > 0) {
                $drawdown = (($peak - $balanceAfter) / $peak) * 100;
                if ($drawdown > $maxDrawdown) {
                    $maxDrawdown = $drawdown;
                }
            }
        }

        return round($maxDrawdown, 2);
    }

    /**
     * Calculate max consecutive wins and losses streaks.
     *
     * @param  Collection<int, BacktestTrade> $trades
     * @return array{0: int, 1: int} [maxConsecutiveWin, maxConsecutiveLoss]
     */
    public function calculateStreaks(Collection $trades): array
    {
        $maxConsecutiveWin = 0;
        $maxConsecutiveLoss = 0;
        $currentWinStreak = 0;
        $currentLossStreak = 0;

        foreach ($trades as $trade) {
            if ($trade->result_status === 'TP') {
                $currentWinStreak++;
                $currentLossStreak = 0;
                $maxConsecutiveWin = max($maxConsecutiveWin, $currentWinStreak);
            } else {
                $currentLossStreak++;
                $currentWinStreak = 0;
                $maxConsecutiveLoss = max($maxConsecutiveLoss, $currentLossStreak);
            }
        }

        return [$maxConsecutiveWin, $maxConsecutiveLoss];
    }

    /**
     * Formats equity curve data for Chart.js rendering.
     *
     * @param  BacktestSession $session
     * @return array{labels: array<int, string>, data: array<int, float>}
     */
    public function getEquityCurveData(BacktestSession $session): array
    {
        $trades = $session->trades()->orderBy('sequence_number')->get();

        $labels = ['Start'];
        $data = [(float) $session->initial_capital];

        foreach ($trades as $trade) {
            $labels[] = "#{$trade->sequence_number}";
            $data[] = (float) $trade->balance_after;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
