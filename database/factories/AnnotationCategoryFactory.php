<?php

namespace Database\Factories;

use App\Models\AnnotationCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnnotationCategory>
 */
class AnnotationCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = AnnotationCategory::class;

    public function definition(): array
    {
        return [
            'dataset_id' => \App\Models\Dataset::factory(),
            'name' => $this->faker->word(),
            'supercategory' => $this->faker->word(),
        ];
    }
}
