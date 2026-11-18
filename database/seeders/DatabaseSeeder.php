<?php

namespace Database\Seeders;

use App\Models\BacktestSession;
use App\Models\BacktestTrade;
use App\Models\Symbol;
use App\Models\User;
use App\Services\BacktestCalculationService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SymbolSeeder::class);

        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Demo Trader',
                'password' => Hash::make('password'),
            ]
        );

        $xauusd = Symbol::where('code', 'XAUUSD')->first();
        $eurusd = Symbol::where('code', 'EURUSD')->first();

        // 1. Completed Demo Session: Gold Breakout Strategy
        $completedSession = BacktestSession::firstOrCreate(
            [
                'user_id' => $user->id,
                'title' => 'XAUUSD London Breakout Strategy',
            ],
            [
                'description' => '15-minute London Open session breakout targeting 1:2 Risk-to-Reward ratio.',
                'initial_capital' => 300.00,
                'default_lot' => 0.01,
                'auto_fill_sl_tp' => true,
                'default_sl_pips' => 50,
                'default_tp_pips' => 100,
                'status' => 'COMPLETED',
            ]
        );

        if ($xauusd) {
            $completedSession->symbols()->syncWithoutDetaching([$xauusd->id]);

            if ($completedSession->trades()->count() === 0) {
                $tradesData = [
                    ['seq' => 1, 'type' => 'BUY', 'date' => '2026-01-05', 'entry' => 2030.50, 'sl' => 2025.50, 'tp' => 2040.50, 'res' => 'TP'],
                    ['seq' => 2, 'type' => 'BUY', 'date' => '2026-01-06', 'entry' => 2035.00, 'sl' => 2030.00, 'tp' => 2045.00, 'res' => 'TP'],
                    ['seq' => 3, 'type' => 'SELL', 'date' => '2026-01-07', 'entry' => 2045.00, 'sl' => 2050.00, 'tp' => 2035.00, 'res' => 'SL'],
                    ['seq' => 4, 'type' => 'BUY', 'date' => '2026-01-08', 'entry' => 2040.00, 'sl' => 2035.00, 'tp' => 2050.00, 'res' => 'TP'],
                    ['seq' => 5, 'type' => 'SELL', 'date' => '2026-01-09', 'entry' => 2055.00, 'sl' => 2060.00, 'tp' => 2045.00, 'res' => 'TP'],
                    ['seq' => 6, 'type' => 'SELL', 'date' => '2026-01-12', 'entry' => 2050.00, 'sl' => 2055.00, 'tp' => 2040.00, 'res' => 'SL'],
                    ['seq' => 7, 'type' => 'BUY', 'date' => '2026-01-13', 'entry' => 2042.00, 'sl' => 2037.00, 'tp' => 2052.00, 'res' => 'TP'],
                ];

                foreach ($tradesData as $t) {
                    BacktestTrade::create([
                        'backtest_session_id' => $completedSession->id,
                        'symbol_id' => $xauusd->id,
                        'sequence_number' => $t['seq'],
                        'trade_type' => $t['type'],
                        'entry_date' => $t['date'],
                        'entry_price' => $t['entry'],
                        'stop_loss' => $t['sl'],
                        'take_profit' => $t['tp'],
                        'exit_date' => $t['date'],
                        'result_status' => $t['res'],
                    ]);
                }

                $calculationService = app(BacktestCalculationService::class);
                $calculationService->calculate($completedSession);
            }
        }

        // 2. Draft Session: EURUSD Scalping
        $draftSession = BacktestSession::firstOrCreate(
            [
                'user_id' => $user->id,
                'title' => 'EURUSD Asian Session Range',
            ],
            [
                'description' => 'Support & Resistance bounces during Asian session.',
                'initial_capital' => 500.00,
                'default_lot' => 0.02,
                'auto_fill_sl_tp' => true,
                'default_sl_pips' => 20,
                'default_tp_pips' => 40,
                'status' => 'DRAFT',
            ]
        );

        if ($eurusd) {
            $draftSession->symbols()->syncWithoutDetaching([$eurusd->id]);

            if ($draftSession->trades()->count() === 0) {
                BacktestTrade::create([
                    'backtest_session_id' => $draftSession->id,
                    'symbol_id' => $eurusd->id,
                    'sequence_number' => 1,
                    'trade_type' => 'BUY',
                    'entry_date' => '2026-01-15',
                    'entry_price' => 1.0920,
                    'stop_loss' => 1.0900,
                    'take_profit' => 1.0960,
                    'exit_date' => '2026-01-15',
                    'result_status' => 'TP',
                ]);
            }
        }
    }
}
