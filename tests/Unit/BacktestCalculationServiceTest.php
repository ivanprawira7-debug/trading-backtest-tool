<?php

namespace Tests\Unit;

use App\Models\BacktestSession;
use App\Models\BacktestTrade;
use App\Models\Symbol;
use App\Models\User;
use App\Services\BacktestCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BacktestCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BacktestCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BacktestCalculationService();
    }

    public function test_autofill_sl_tp_for_buy(): void
    {
        // For XAUUSD: entry 2000.00, SL 50 pips, TP 100 pips, pip_size 0.01
        $result = $this->service->autoFillSlTp(
            entryPrice: 2000.00,
            tradeType: 'BUY',
            slPips: 50,
            tpPips: 100,
            pipSize: 0.01
        );

        $this->assertEquals(1999.50, $result['stop_loss']); // 2000 - (50 * 0.01)
        $this->assertEquals(2001.00, $result['take_profit']); // 2000 + (100 * 0.01)
    }

    public function test_autofill_sl_tp_for_sell(): void
    {
        // For EURUSD: entry 1.1000, SL 30 pips, TP 60 pips, pip_size 0.0001
        $result = $this->service->autoFillSlTp(
            entryPrice: 1.1000,
            tradeType: 'SELL',
            slPips: 30,
            tpPips: 60,
            pipSize: 0.0001
        );

        $this->assertEquals(1.1030, $result['stop_loss']); // 1.1000 + (30 * 0.0001)
        $this->assertEquals(1.0940, $result['take_profit']); // 1.1000 - (60 * 0.0001)
    }

    public function test_pips_gained_calculation(): void
    {
        $symbol = Symbol::create([
            'code' => 'XAUUSD',
            'name' => 'Gold',
            'pip_size' => 0.01,
            'contract_size' => 100,
        ]);

        $user = User::factory()->create();
        $session = BacktestSession::create([
            'user_id' => $user->id,
            'title' => 'Test Session',
            'initial_capital' => 300,
            'default_lot' => 0.01,
        ]);

        // BUY TP
        $buyWin = new BacktestTrade([
            'trade_type' => 'BUY',
            'entry_price' => 2000.00,
            'stop_loss' => 1990.00,
            'take_profit' => 2020.00,
            'result_status' => 'TP',
        ]);
        $pips = $this->service->calculatePipsGained($buyWin, 0.01);
        $this->assertEquals(2000.00, $pips); // (2020 - 2000) / 0.01 = 2000 pips

        // BUY SL
        $buyLoss = new BacktestTrade([
            'trade_type' => 'BUY',
            'entry_price' => 2000.00,
            'stop_loss' => 1990.00,
            'take_profit' => 2020.00,
            'result_status' => 'SL',
        ]);
        $pips = $this->service->calculatePipsGained($buyLoss, 0.01);
        $this->assertEquals(-1000.00, $pips); // (1990 - 2000) / 0.01 = -1000 pips

        // SELL TP
        $sellWin = new BacktestTrade([
            'trade_type' => 'SELL',
            'entry_price' => 2000.00,
            'stop_loss' => 2010.00,
            'take_profit' => 1980.00,
            'result_status' => 'TP',
        ]);
        $pips = $this->service->calculatePipsGained($sellWin, 0.01);
        $this->assertEquals(2000.00, $pips); // (2000 - 1980) / 0.01 = 2000 pips

        // SELL SL
        $sellLoss = new BacktestTrade([
            'trade_type' => 'SELL',
            'entry_price' => 2000.00,
            'stop_loss' => 2010.00,
            'take_profit' => 1980.00,
            'result_status' => 'SL',
        ]);
        $pips = $this->service->calculatePipsGained($sellLoss, 0.01);
        $this->assertEquals(-1000.00, $pips); // (2000 - 2010) / 0.01 = -1000 pips
    }

    public function test_profit_loss_dollar_calculation(): void
    {
        // Formula: pips_gained * default_lot * (pip_size * contract_size)
        // XAUUSD: 100 pips * 0.01 lot * (0.01 * 100) = 100 * 0.01 * 1 = $1.00
        $pl = $this->service->calculateProfitLoss(
            pipsGained: 100,
            lotSize: 0.01,
            pipSize: 0.01,
            contractSize: 100
        );
        $this->assertEquals(1.00, $pl);

        // EURUSD: 50 pips * 0.01 lot * (0.0001 * 100000) = 50 * 0.01 * 10 = $5.00
        $plEurusd = $this->service->calculateProfitLoss(
            pipsGained: 50,
            lotSize: 0.01,
            pipSize: 0.0001,
            contractSize: 100000
        );
        $this->assertEquals(5.00, $plEurusd);
    }

    public function test_full_session_calculation_and_summary(): void
    {
        $symbol = Symbol::create([
            'code' => 'XAUUSD',
            'name' => 'Gold',
            'pip_size' => 0.01,
            'contract_size' => 100,
        ]);

        $user = User::factory()->create();
        $session = BacktestSession::create([
            'user_id' => $user->id,
            'title' => 'Gold Scalp',
            'initial_capital' => 300,
            'default_lot' => 0.01,
        ]);

        // Trade 1: Win +$20
        BacktestTrade::create([
            'backtest_session_id' => $session->id,
            'symbol_id' => $symbol->id,
            'sequence_number' => 1,
            'trade_type' => 'BUY',
            'entry_date' => '2026-01-01',
            'entry_price' => 2000.00,
            'stop_loss' => 1990.00,
            'take_profit' => 2020.00,
            'exit_date' => '2026-01-01',
            'result_status' => 'TP',
        ]);

        // Trade 2: Win +$20
        BacktestTrade::create([
            'backtest_session_id' => $session->id,
            'symbol_id' => $symbol->id,
            'sequence_number' => 2,
            'trade_type' => 'BUY',
            'entry_date' => '2026-01-02',
            'entry_price' => 2020.00,
            'stop_loss' => 2010.00,
            'take_profit' => 2040.00,
            'exit_date' => '2026-01-02',
            'result_status' => 'TP',
        ]);

        // Trade 3: Loss -$10
        BacktestTrade::create([
            'backtest_session_id' => $session->id,
            'symbol_id' => $symbol->id,
            'sequence_number' => 3,
            'trade_type' => 'BUY',
            'entry_date' => '2026-01-03',
            'entry_price' => 2040.00,
            'stop_loss' => 2030.00,
            'take_profit' => 2060.00,
            'exit_date' => '2026-01-03',
            'result_status' => 'SL',
        ]);

        $result = $this->service->calculate($session);

        $this->assertEquals(3, $result->total_trades);
        $this->assertEquals(2, $result->total_win);
        $this->assertEquals(1, $result->total_loss);
        $this->assertEquals(66.67, (float) $result->win_rate);
        $this->assertEquals(40.00, (float) $result->gross_profit);
        $this->assertEquals(10.00, (float) $result->gross_loss);
        $this->assertEquals(30.00, (float) $result->net_profit);
        $this->assertEquals(330.00, (float) $result->final_balance);
        $this->assertEquals(4.00, (float) $result->profit_factor); // 40 / 10
        $this->assertEquals(2, $result->max_consecutive_win);
        $this->assertEquals(1, $result->max_consecutive_loss);
        $this->assertEquals('COMPLETED', $session->fresh()->status);

        // Verify equity curve data
        $curve = $this->service->getEquityCurveData($session);
        $this->assertEquals(['Start', '#1', '#2', '#3'], $curve['labels']);
        $this->assertEquals([300.0, 320.0, 340.0, 330.0], $curve['data']);
    }
}
