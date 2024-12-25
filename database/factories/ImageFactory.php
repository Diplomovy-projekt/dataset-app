<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dataset_id' => \App\Models\Dataset::first()->id ?? \App\Models\Dataset::factory(), // Use first dataset or create a new one
            'img_folder' => $this->faker->word(),
            'filename' => 'https://picsum.photos/' . $this->faker->numberBetween(100, 5000) . '/' . $this->faker->numberBetween(100, 5000), // Image URL from Lorem Picsum
            'width' => $this->faker->numberBetween(100, 5000),
            'height' => $this->faker->numberBetween(100, 5000)
        ];
    }
}
