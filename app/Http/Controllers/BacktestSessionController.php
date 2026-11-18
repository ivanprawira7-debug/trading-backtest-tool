<?php

namespace App\Http\Controllers;

use App\Models\BacktestSession;
use App\Models\BacktestTrade;
use App\Models\Symbol;
use App\Services\BacktestCalculationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BacktestSessionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected BacktestCalculationService $calculationService
    ) {}

    /**
     * Display a listing of the user's backtest sessions.
     */
    public function index(): View
    {
        $sessions = auth()->user()
            ->backtestSessions()
            ->with(['result', 'symbols'])
            ->withCount('trades')
            ->latest()
            ->paginate(10);

        return view('backtests.index', compact('sessions'));
    }

    /**
     * Show the form for creating a new backtest session.
     */
    public function create(): View
    {
        $symbols = Symbol::orderBy('code')->get();

        return view('backtests.create', compact('symbols'));
    }

    /**
     * Store a newly created backtest session.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'initial_capital' => ['required', 'numeric', 'min:1'],
            'default_lot' => ['required', 'numeric', 'min:0.001'],
            'symbols' => ['nullable', 'array'],
            'symbols.*' => ['exists:symbols,id'],
        ]);

        $session = auth()->user()->backtestSessions()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'initial_capital' => $validated['initial_capital'],
            'default_lot' => $validated['default_lot'],
            'status' => 'DRAFT',
        ]);

        if (!empty($validated['symbols'])) {
            $session->symbols()->sync($validated['symbols']);
        } else {
            // Default to XAUUSD if available
            $defaultSymbol = Symbol::where('code', 'XAUUSD')->first() ?? Symbol::first();
            if ($defaultSymbol) {
                $session->symbols()->attach($defaultSymbol->id);
            }
        }

        return redirect()->route('backtests.workspace', $session)
            ->with('status', 'Backtest session created successfully!');
    }

    /**
     * The interactive Backtest Tool workspace page.
     */
    public function workspace(BacktestSession $backtest): View
    {
        $this->authorize('view', $backtest);

        $backtest->load(['symbols', 'trades' => function ($query) {
            $query->orderBy('sequence_number');
        }, 'trades.symbol', 'result']);

        $availableSymbols = Symbol::orderBy('code')->get();

        return view('backtests.workspace', [
            'session' => $backtest,
            'availableSymbols' => $availableSymbols,
        ]);
    }

    /**
     * Update session metadata and trades from the workspace.
     */
    public function update(Request $request, BacktestSession $backtest): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $backtest);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'initial_capital' => ['required', 'numeric', 'min:1'],
            'default_lot' => ['required', 'numeric', 'min:0.001'],
            'auto_fill_sl_tp' => ['nullable', 'boolean'],
            'default_sl_pips' => ['nullable', 'numeric', 'min:0'],
            'default_tp_pips' => ['nullable', 'numeric', 'min:0'],
            'symbols' => ['nullable', 'array'],
            'symbols.*' => ['exists:symbols,id'],
            'trades' => ['nullable', 'array'],
            'trades.*.id' => ['nullable', 'integer'],
            'trades.*.symbol_id' => ['required', 'exists:symbols,id'],
            'trades.*.sequence_number' => ['required', 'integer', 'min:1'],
            'trades.*.trade_type' => ['required', 'in:BUY,SELL'],
            'trades.*.entry_date' => ['required', 'date'],
            'trades.*.entry_price' => ['required', 'numeric', 'min:0.00001'],
            'trades.*.stop_loss' => ['required', 'numeric', 'min:0'],
            'trades.*.take_profit' => ['required', 'numeric', 'min:0'],
            'trades.*.exit_date' => ['required', 'date'],
            'trades.*.result_status' => ['required', 'in:SL,TP'],
        ]);

        DB::transaction(function () use ($backtest, $validated) {
            $backtest->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'initial_capital' => $validated['initial_capital'],
                'default_lot' => $validated['default_lot'],
                'auto_fill_sl_tp' => (bool) ($validated['auto_fill_sl_tp'] ?? false),
                'default_sl_pips' => $validated['default_sl_pips'] ?? null,
                'default_tp_pips' => $validated['default_tp_pips'] ?? null,
            ]);

            if (isset($validated['symbols'])) {
                $backtest->symbols()->sync($validated['symbols']);
            }

            // Sync trades
            $submittedTradeIds = [];
            $tradesData = $validated['trades'] ?? [];

            foreach ($tradesData as $tradeRow) {
                $data = [
                    'backtest_session_id' => $backtest->id,
                    'symbol_id' => $tradeRow['symbol_id'],
                    'sequence_number' => $tradeRow['sequence_number'],
                    'trade_type' => $tradeRow['trade_type'],
                    'entry_date' => $tradeRow['entry_date'],
                    'entry_price' => $tradeRow['entry_price'],
                    'stop_loss' => $tradeRow['stop_loss'],
                    'take_profit' => $tradeRow['take_profit'],
                    'exit_date' => $tradeRow['exit_date'],
                    'result_status' => $tradeRow['result_status'],
                ];

                if (!empty($tradeRow['id'])) {
                    $trade = BacktestTrade::where('backtest_session_id', $backtest->id)
                        ->where('id', $tradeRow['id'])
                        ->first();

                    if ($trade) {
                        $trade->update($data);
                        $submittedTradeIds[] = $trade->id;
                        continue;
                    }
                }

                $newTrade = BacktestTrade::create($data);
                $submittedTradeIds[] = $newTrade->id;
            }

            // Delete removed trades
            $backtest->trades()->whereNotIn('id', $submittedTradeIds)->delete();
        });

        if ($request->wantsJson()) {
            $backtest->load(['symbols', 'trades' => function ($query) {
                $query->orderBy('sequence_number');
            }, 'trades.symbol']);

            return response()->json([
                'message' => 'Backtest trades saved successfully.',
                'session' => $backtest,
            ]);
        }

        return redirect()->route('backtests.workspace', $backtest)
            ->with('status', 'Backtest workspace saved successfully.');
    }

    /**
     * Calculate results for the backtest session and redirect to results page.
     */
    public function calculate(Request $request, BacktestSession $backtest): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $backtest);

        // If trades are provided in request, update them first
        if ($request->has('trades')) {
            $this->update($request, $backtest);
        }

        if ($backtest->trades()->count() === 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Please add at least one trade before calculating results.',
                ], 422);
            }

            return redirect()->route('backtests.workspace', $backtest)
                ->withErrors(['trades' => 'Please add at least one trade before calculating results.']);
        }

        $result = $this->calculationService->calculate($backtest);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Calculations completed successfully.',
                'result' => $result,
                'redirect_url' => route('backtests.results', $backtest),
            ]);
        }

        return redirect()->route('backtests.results', $backtest)
            ->with('status', 'Backtest results calculated successfully!');
    }

    /**
     * Display detailed backtest results, metrics, and equity curve.
     */
    public function results(BacktestSession $backtest): View
    {
        $this->authorize('view', $backtest);

        $backtest->load(['result', 'trades.symbol', 'symbols']);

        if (!$backtest->result) {
            // If result not calculated yet but trades exist, calculate automatically
            if ($backtest->trades()->count() > 0) {
                $this->calculationService->calculate($backtest);
                $backtest->load('result');
            } else {
                return view('backtests.results', [
                    'session' => $backtest,
                    'result' => null,
                    'chartData' => ['labels' => [], 'data' => []],
                ]);
            }
        }

        $chartData = $this->calculationService->getEquityCurveData($backtest);

        return view('backtests.results', [
            'session' => $backtest,
            'result' => $backtest->result,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Delete a backtest session.
     */
    public function destroy(BacktestSession $backtest): RedirectResponse
    {
        $this->authorize('delete', $backtest);

        $backtest->delete();

        return redirect()->route('backtests.index')
            ->with('status', 'Backtest session deleted successfully.');
    }

    /**
     * Download CSV template for trading data upload.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="backtest_trades_template.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'sequence_number',
                'symbol',
                'trade_type',
                'entry_date',
                'entry_price',
                'stop_loss',
                'take_profit',
                'exit_date',
                'result_status',
            ]);

            // Sample rows
            fputcsv($handle, [
                1,
                'XAUUSD',
                'BUY',
                '2026-01-05',
                2030.50,
                2025.50,
                2045.50,
                '2026-01-05',
                'TP',
            ]);

            fputcsv($handle, [
                2,
                'EURUSD',
                'SELL',
                '2026-01-06',
                1.0950,
                1.0980,
                1.0890,
                '2026-01-06',
                'SL',
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Upload and import trading data from a CSV file into the backtest session.
     */
    public function uploadTrades(Request $request, BacktestSession $backtest): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $backtest);

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $rows = array_map('str_getcsv', file($path));

        if (empty($rows) || count($rows) < 2) {
            $errorMsg = 'CSV file is empty or does not contain trade rows.';
            return $request->wantsJson()
                ? response()->json(['error' => $errorMsg], 422)
                : back()->withErrors(['csv_file' => $errorMsg]);
        }

        $header = array_map('trim', array_map('strtolower', array_shift($rows)));
        $expected = ['symbol', 'trade_type', 'entry_date', 'entry_price', 'stop_loss', 'take_profit', 'exit_date', 'result_status'];

        // Validate required headers
        foreach ($expected as $col) {
            if (!in_array($col, $header, true)) {
                $errorMsg = "CSV missing required column: {$col}";
                return $request->wantsJson()
                    ? response()->json(['error' => $errorMsg], 422)
                    : back()->withErrors(['csv_file' => $errorMsg]);
            }
        }

        $headerIndex = array_flip($header);
        $importedCount = 0;
        $activeSymbolsToSync = [];

        DB::transaction(function () use ($rows, $headerIndex, $backtest, &$importedCount, &$activeSymbolsToSync) {
            // Get current max sequence number
            $currentMaxSeq = (int) $backtest->trades()->max('sequence_number');

            foreach ($rows as $row) {
                if (empty($row) || count($row) < count($headerIndex)) {
                    continue;
                }

                $symbolCode = strtoupper(trim($row[$headerIndex['symbol']]));
                if (empty($symbolCode)) {
                    continue;
                }

                // Resolve or create symbol
                $symbol = Symbol::firstOrCreate(
                    ['code' => $symbolCode],
                    [
                        'name' => $symbolCode,
                        'pip_size' => str_contains($symbolCode, 'JPY') ? 0.01 : (str_contains($symbolCode, 'XAU') ? 0.01 : 0.0001),
                        'contract_size' => str_contains($symbolCode, 'XAU') ? 100 : 100000,
                    ]
                );

                $activeSymbolsToSync[] = $symbol->id;

                $seq = isset($headerIndex['sequence_number']) && !empty(trim($row[$headerIndex['sequence_number']]))
                    ? (int) trim($row[$headerIndex['sequence_number']])
                    : ++$currentMaxSeq;

                $tradeType = strtoupper(trim($row[$headerIndex['trade_type']]));
                $tradeType = in_array($tradeType, ['BUY', 'SELL'], true) ? $tradeType : 'BUY';

                $resultStatus = strtoupper(trim($row[$headerIndex['result_status']]));
                $resultStatus = in_array($resultStatus, ['TP', 'SL'], true) ? $resultStatus : 'TP';

                BacktestTrade::create([
                    'backtest_session_id' => $backtest->id,
                    'symbol_id' => $symbol->id,
                    'sequence_number' => $seq,
                    'trade_type' => $tradeType,
                    'entry_date' => Carbon::parse(trim($row[$headerIndex['entry_date']]))->format('Y-m-d'),
                    'entry_price' => (float) trim($row[$headerIndex['entry_price']]),
                    'stop_loss' => (float) trim($row[$headerIndex['stop_loss']]),
                    'take_profit' => (float) trim($row[$headerIndex['take_profit']]),
                    'exit_date' => Carbon::parse(trim($row[$headerIndex['exit_date']]))->format('Y-m-d'),
                    'result_status' => $resultStatus,
                ]);

                $importedCount++;
            }

            if (!empty($activeSymbolsToSync)) {
                $backtest->symbols()->syncWithoutDetaching(array_unique($activeSymbolsToSync));
            }
        });

        if ($request->wantsJson()) {
            $backtest->load(['symbols', 'trades' => function ($query) {
                $query->orderBy('sequence_number');
            }, 'trades.symbol']);

            return response()->json([
                'message' => "Successfully imported {$importedCount} trades.",
                'session' => $backtest,
            ]);
        }

        return redirect()->route('backtests.workspace', $backtest)
            ->with('status', "Successfully imported {$importedCount} trades from CSV.");
    }
}
