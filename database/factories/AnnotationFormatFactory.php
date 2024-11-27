<?php

namespace Database\Factories;

use App\Models\AnnotationFormat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnnotationFormat>
 */
class AnnotationFormatFactory extends Factory
{

    protected $model = AnnotationFormat::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['YOLO', 'COCO', 'Pascal VOC', 'CSV']),
            'extension' => $this->faker->randomElement(['.json', '.txt', '.xml']),
        ];
    }
}
