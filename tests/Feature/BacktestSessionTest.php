<?php

namespace Tests\Feature;

use App\Models\BacktestSession;
use App\Models\BacktestTrade;
use App\Models\Symbol;
use App\Models\User;
use Database\Seeders\SymbolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BacktestSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SymbolSeeder::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('backtests.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('backtests.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_backtest_history(): void
    {
        $user = User::factory()->create();

        $session = BacktestSession::create([
            'user_id' => $user->id,
            'title' => 'My EURUSD Session',
            'initial_capital' => 500,
            'default_lot' => 0.02,
        ]);

        $response = $this->actingAs($user)->get(route('backtests.index'));

        $response->assertStatus(200);
        $response->assertSee('My EURUSD Session');
        $response->assertSee('$500.00');
    }

    public function test_user_can_create_new_backtest_session(): void
    {
        $user = User::factory()->create();
        $symbol = Symbol::where('code', 'XAUUSD')->first();

        $response = $this->actingAs($user)->post(route('backtests.store'), [
            'title' => 'Gold Strategy 2026',
            'description' => 'Test notes',
            'initial_capital' => 1000,
            'default_lot' => 0.05,
            'symbols' => [$symbol->id],
        ]);

        $this->assertDatabaseHas('backtest_sessions', [
            'user_id' => $user->id,
            'title' => 'Gold Strategy 2026',
            'initial_capital' => 1000,
            'default_lot' => 0.05,
        ]);

        $session = BacktestSession::where('title', 'Gold Strategy 2026')->first();
        $response->assertRedirect(route('backtests.workspace', $session));
    }

    public function test_user_cannot_view_or_modify_another_users_session(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $sessionA = BacktestSession::create([
            'user_id' => $userA->id,
            'title' => 'User A Session',
            'initial_capital' => 300,
            'default_lot' => 0.01,
        ]);

        // User B tries to view User A's workspace
        $response = $this->actingAs($userB)->get(route('backtests.workspace', $sessionA));
        $response->assertStatus(403);

        // User B tries to delete User A's session
        $response = $this->actingAs($userB)->delete(route('backtests.destroy', $sessionA));
        $response->assertStatus(403);
    }

    public function test_user_can_update_workspace_trades(): void
    {
        $user = User::factory()->create();
        $symbol = Symbol::where('code', 'XAUUSD')->first();

        $session = BacktestSession::create([
            'user_id' => $user->id,
            'title' => 'Draft Session',
            'initial_capital' => 300,
            'default_lot' => 0.01,
        ]);

        $payload = [
            'title' => 'Updated Session Title',
            'description' => 'Updated description',
            'initial_capital' => 500,
            'default_lot' => 0.02,
            'auto_fill_sl_tp' => true,
            'default_sl_pips' => 40,
            'default_tp_pips' => 80,
            'symbols' => [$symbol->id],
            'trades' => [
                [
                    'sequence_number' => 1,
                    'symbol_id' => $symbol->id,
                    'trade_type' => 'BUY',
                    'entry_date' => '2026-01-01',
                    'entry_price' => 2050.00,
                    'stop_loss' => 2046.00,
                    'take_profit' => 2058.00,
                    'exit_date' => '2026-01-01',
                    'result_status' => 'TP',
                ],
                [
                    'sequence_number' => 2,
                    'symbol_id' => $symbol->id,
                    'trade_type' => 'SELL',
                    'entry_date' => '2026-01-02',
                    'entry_price' => 2060.00,
                    'stop_loss' => 2064.00,
                    'take_profit' => 2052.00,
                    'exit_date' => '2026-01-02',
                    'result_status' => 'SL',
                ],
            ],
        ];

        $response = $this->actingAs($user)->putJson(route('backtests.update', $session), $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('backtest_sessions', [
            'id' => $session->id,
            'title' => 'Updated Session Title',
            'initial_capital' => 500,
        ]);

        $this->assertEquals(2, $session->trades()->count());
    }

    public function test_user_can_calculate_and_view_results(): void
    {
        $user = User::factory()->create();
        $symbol = Symbol::where('code', 'XAUUSD')->first();

        $session = BacktestSession::create([
            'user_id' => $user->id,
            'title' => 'Calculation Session',
            'initial_capital' => 300,
            'default_lot' => 0.01,
        ]);

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

        // Calculate
        $response = $this->actingAs($user)->postJson(route('backtests.calculate', $session));

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'result', 'redirect_url']);

        $this->assertDatabaseHas('backtest_results', [
            'backtest_session_id' => $session->id,
            'total_trades' => 1,
            'total_win' => 1,
            'total_loss' => 0,
        ]);

        // View results page
        $resultsView = $this->actingAs($user)->get(route('backtests.results', $session));
        $resultsView->assertStatus(200);
        $resultsView->assertSee('Backtest Performance Analytics');
        $resultsView->assertSee('Equity Curve');
    }

    public function test_user_can_download_csv_template(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('backtests.template.download'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_user_can_upload_csv_trades(): void
    {
        $user = User::factory()->create();

        $session = BacktestSession::create([
            'user_id' => $user->id,
            'title' => 'CSV Import Session',
            'initial_capital' => 300,
            'default_lot' => 0.01,
        ]);

        $csvContent = "sequence_number,symbol,trade_type,entry_date,entry_price,stop_loss,take_profit,exit_date,result_status\n"
                    . "1,XAUUSD,BUY,2026-01-05,2030.50,2025.50,2045.50,2026-01-05,TP\n"
                    . "2,EURUSD,SELL,2026-01-06,1.0950,1.0980,1.0890,2026-01-06,SL\n";

        $file = UploadedFile::fake()->createWithContent('trades.csv', $csvContent);

        $response = $this->actingAs($user)->postJson(route('backtests.upload', $session), [
            'csv_file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Successfully imported 2 trades.',
        ]);

        $this->assertEquals(2, $session->trades()->count());
        $this->assertDatabaseHas('backtest_trades', [
            'backtest_session_id' => $session->id,
            'trade_type' => 'BUY',
            'entry_price' => 2030.50,
            'result_status' => 'TP',
        ]);
    }
}
