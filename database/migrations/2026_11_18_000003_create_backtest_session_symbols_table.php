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
        Schema::create('backtest_session_symbols', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backtest_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('symbol_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['backtest_session_id', 'symbol_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backtest_session_symbols');
    }
};
