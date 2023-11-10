<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\Round;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Story>
 */
class StoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'round_id'  => Round::factory(),
            'writer_id' => User::factory(),
            'media_id'  => Media::factory(),
        ];
    }
}
