<?php

namespace Database\Factories;

use App\Models\SellerBalanceEntry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SellerBalanceEntry>
 */
class SellerBalanceEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller_id' => 1,
            'order_id' => null,
            'seller_withdrawal_id' => null,
            'entry_type' => 'sale_earning',
            'reference_id' => (string) Str::uuid(),
            'amount' => fake()->numberBetween(10000, 1000000),
            'currency' => 'IDR',
            'description' => 'Pendapatan pesanan uji',
        ];
    }
}
