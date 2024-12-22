<?php

namespace Database\Factories;

use App\Models\AnnotationData;
use App\Models\AnnotationClass;
use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnnotationData>
 */
class AnnotationDataFactory extends Factory
{
    protected $model = AnnotationData::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image_id' => \App\Models\Image::factory()->create()->id,  // Create image here manually, but associate the id
            'annotation_class_id' => \App\Models\AnnotationClass::factory()->create()->id, // Same for class
            'x' => $this->faker->randomFloat(2, 0, 1),
            'y' => $this->faker->randomFloat(2, 0, 1),
            'width' => $this->faker->randomFloat(2, 0, 1),
            'height' => $this->faker->randomFloat(2, 0, 1),
            'segmentation' => json_encode(
                array_map(function () {
                    return [
                        $this->faker->randomFloat(2, 0, 1),
                        $this->faker->randomFloat(2, 0, 1),
                    ];
                }, range(1, rand(4, 10)))
            ),
        ];
    }
}
