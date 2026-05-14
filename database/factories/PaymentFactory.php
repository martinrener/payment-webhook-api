<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_id'    => fake()->uuid(),
            'event'         => fake()->randomElement(['payment.created', 'payment.updated']),
            'amount'        => fake()->numberBetween(100, 10000),
            'currency'      => fake()->randomElement(['USD', 'EUR']),
            'user_id'       => fake()->uuid(),
            'last_event_id' => fake()->uuid(),
        ];
    }
}
