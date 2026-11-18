<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('backtest_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backtest_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('symbol_id')->constrained()->restrictOnDelete();
            $table->integer('sequence_number');
            $table->enum('trade_type', ['BUY', 'SELL']);
            $table->date('entry_date');
            $table->decimal('entry_price', 15, 5);
            $table->decimal('stop_loss', 15, 5);
            $table->decimal('take_profit', 15, 5);
            $table->date('exit_date');
            $table->enum('result_status', ['SL', 'TP']);
            $table->decimal('pips_gained', 10, 2)->nullable();
            $table->decimal('profit_loss', 15, 2)->nullable();
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backtest_trades');
    }
};
