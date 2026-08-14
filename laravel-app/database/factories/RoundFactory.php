<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Round>
 */
class RoundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Center the deadline around "now" so the seeded set holds a
        // healthy mix of ongoing ("en-cours") and finished ("termine") rounds.
        $end   = fake()->dateTimeBetween('-1 month', '+1 month');
        $start = (clone $end)->modify('-2 weeks');

        return [
            'master_id' => User::factory(),
            'word'      => fake()->word(),
            'start_at'  => $start,
            'end_at'    => $end,
            'created_at'=> now(),
        ];
    }
}
