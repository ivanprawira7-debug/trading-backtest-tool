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
        Schema::create('backtest_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('initial_capital', 15, 2)->default(300);
            $table->decimal('default_lot', 8, 2)->default(0.01);
            $table->boolean('auto_fill_sl_tp')->default(false);
            $table->decimal('default_sl_pips', 8, 2)->nullable();
            $table->decimal('default_tp_pips', 8, 2)->nullable();
            $table->enum('status', ['DRAFT', 'COMPLETED'])->default('DRAFT');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backtest_sessions');
    }
};
