<?php

namespace Database\Factories;

use App\Models\SellerWithdrawal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SellerWithdrawal>
 */
class SellerWithdrawalFactory extends Factory
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
            'seller_payout_account_id' => 1,
            'request_key' => (string) Str::uuid(),
            'reference_id' => 'withdrawal-'.Str::uuid(),
            'amount' => fake()->numberBetween(10000, 1000000),
            'currency' => 'IDR',
            'status' => 'queued',
            'account_snapshot' => ['account_number' => '1234567890'],
            'requested_at' => now(),
        ];
    }
}
