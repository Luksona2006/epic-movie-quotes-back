<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'movie_id' => 1,
            'user_id' => 1,
            'text' => [
                'en' => fake()->sentence(5),
                'ka' => fake('ka_GE')->sentence(5),
            ],
            'image' => 'image'
        ];
    }
}
