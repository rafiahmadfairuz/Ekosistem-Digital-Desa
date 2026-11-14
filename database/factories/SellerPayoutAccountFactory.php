<?php

namespace Database\Factories;

use App\Models\SellerPayoutAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SellerPayoutAccount>
 */
class SellerPayoutAccountFactory extends Factory
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
            'bank_name' => 'Bank Central Asia',
            'bank_swift_code' => 'CENAIDJA',
            'account_holder_name' => fake()->name(),
            'account_number' => fake()->numerify('##########'),
            'recipient_address' => fake()->address(),
            'recipient_type' => 'INDIVIDUAL',
            'status' => 'pending',
        ];
    }
}
