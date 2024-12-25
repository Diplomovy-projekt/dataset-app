<?php

namespace Database\Factories;

use App\Models\AnnotationClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnnotationClass>
 */
class AnnotationClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = AnnotationClass::class;

    public function definition(): array
    {
        return [
            'dataset_id' => \App\Models\Dataset::factory(),
            'name' => $this->faker->word(),
            'supercategory' => $this->faker->word(),
        ];
    }
}
