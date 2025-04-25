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
            'dataset_id' => \App\Models\Dataset::first()->id ?? \App\Models\Dataset::factory(),
            'dataset_folder' => $this->faker->word(), // You can adjust this to whatever folder structure you need
            'filename' => 'https://picsum.photos/' . $this->faker->numberBetween(100, 5000) . '/' . $this->faker->numberBetween(100, 5000), // Image URL from Lorem Picsum
            'width' => $this->faker->numberBetween(100, 5000),
            'height' => $this->faker->numberBetween(100, 5000),
            'size' => $this->faker->numberBetween(1000, 100000), // Assuming the size is in bytes
        ];
    }

}
