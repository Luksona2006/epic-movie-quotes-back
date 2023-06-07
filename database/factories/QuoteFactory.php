<?php

namespace Database\Factories;

use App\Models\Movie;
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
            'movie_id' => Movie::all()->first()->id,
            'text' => [
                'en' => fake()->sentence(5),
                'ka' => fake('ka_GE')->sentence(5),
            ],
            'image' => 'image'
        ];
    }
}
