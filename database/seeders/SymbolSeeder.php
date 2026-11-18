<?php

namespace Database\Seeders;

use App\Models\Symbol;
use Illuminate\Database\Seeder;

class SymbolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $symbols = [
            [
                'code' => 'XAUUSD',
                'name' => 'Gold / US Dollar',
                'pip_size' => 0.01,
                'contract_size' => 100,
            ],
            [
                'code' => 'EURUSD',
                'name' => 'Euro / US Dollar',
                'pip_size' => 0.0001,
                'contract_size' => 100000,
            ],
            [
                'code' => 'GBPUSD',
                'name' => 'British Pound / US Dollar',
                'pip_size' => 0.0001,
                'contract_size' => 100000,
            ],
        ];

        foreach ($symbols as $symbol) {
            Symbol::updateOrCreate(
                ['code' => $symbol['code']],
                $symbol
            );
        }
    }
}
