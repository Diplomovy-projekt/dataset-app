<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dataset>
 */
class DatasetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::first()->id ?? \App\Models\User::factory(), // Use first user or create a new one
            'display_name' => $this->faker->word(),
            'unique_name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'num_images' => $this->faker->numberBetween(1, 100),
            'total_size' => $this->faker->numberBetween(1000, 1000000),
            'annotation_technique' => $this->faker->randomElement(['Bounding Box', 'Polygon']),
            'is_public' => $this->faker->boolean(),
        ];
    }
}
