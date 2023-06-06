<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => [
                'en' => fake()->sentence(3),
                'ka' => fake('ka_GE')->sentence(3),
            ],
            'year' => $this->faker->year(),
            'director' => [
                'en' => fake()->name(),
                'ka' => fake('ka_GE')->name(),
            ],
            'description' => [
                'en' => fake()->paragraph(3),
                'ka' => fake('ka_GE')->paragraph(3),
            ],
            'image' => 'image'
        ];
    }
}
