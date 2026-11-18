<?php

use App\Http\Controllers\BacktestSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard redirects directly to Backtest History
    Route::get('/dashboard', [BacktestSessionController::class, 'index'])->name('dashboard');

    // Backtest Sessions & Tool
    Route::prefix('backtests')->name('backtests.')->group(function () {
        Route::get('/', [BacktestSessionController::class, 'index'])->name('index');
        Route::get('/template/download', [BacktestSessionController::class, 'downloadTemplate'])->name('template.download');
        Route::get('/create', [BacktestSessionController::class, 'create'])->name('create');
        Route::post('/', [BacktestSessionController::class, 'store'])->name('store');
        Route::get('/{backtest}/workspace', [BacktestSessionController::class, 'workspace'])->name('workspace');
        Route::put('/{backtest}', [BacktestSessionController::class, 'update'])->name('update');
        Route::post('/{backtest}/calculate', [BacktestSessionController::class, 'calculate'])->name('calculate');
        Route::get('/{backtest}/results', [BacktestSessionController::class, 'results'])->name('results');
        Route::delete('/{backtest}', [BacktestSessionController::class, 'destroy'])->name('destroy');
        Route::post('/{backtest}/upload', [BacktestSessionController::class, 'uploadTrades'])->name('upload');
    });

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

