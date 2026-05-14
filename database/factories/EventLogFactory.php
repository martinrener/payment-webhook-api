<?php

namespace Database\Factories;

use App\Models\EventLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventLog>
 */
class EventLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id'    => fake()->uuid(),
            'payment_id'  => fake()->uuid(),
            'event'       => fake()->randomElement(['payment.created', 'payment.updated']),
            'amount'      => fake()->numberBetween(100, 10000),
            'currency'    => fake()->randomElement(['USD', 'EUR']),
            'user_id'     => fake()->uuid(),
            'timestamp'   => now(),
            'received_at' => now(),
        ];
    }
}
