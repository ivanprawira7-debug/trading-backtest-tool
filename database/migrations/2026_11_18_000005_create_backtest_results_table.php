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
        Schema::create('backtest_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backtest_session_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('total_trades');
            $table->integer('total_win');
            $table->integer('total_loss');
            $table->decimal('win_rate', 5, 2);
            $table->decimal('gross_profit', 15, 2);
            $table->decimal('gross_loss', 15, 2);
            $table->decimal('net_profit', 15, 2);
            $table->decimal('final_balance', 15, 2);
            $table->decimal('max_drawdown_percent', 5, 2)->nullable();
            $table->decimal('profit_factor', 8, 2)->nullable();
            $table->integer('max_consecutive_win')->default(0);
            $table->integer('max_consecutive_loss')->default(0);
            $table->timestamp('calculated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backtest_results');
    }
};
