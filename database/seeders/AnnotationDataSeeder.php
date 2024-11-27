<?php

namespace Database\Seeders;

use App\Models\AnnotationData;
use App\Models\AnnotationFormat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnnotationDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch existing images and categories (or create them if needed)
        $images = \App\Models\Image::take(5)->get();  // Or adjust to your specific dataset
        $categories = \App\Models\AnnotationCategory::take(3)->get();  // Adjust category count

        // Create annotations associated with these images and categories
        foreach ($images as $image) {
            foreach ($categories as $category) {
                AnnotationData::create([
                    'image_id' => $image->id,
                    'annotation_category_id' => $category->id,
                    'center_x' => $this->faker->randomFloat(2, 0, 1),
                    'center_y' => $this->faker->randomFloat(2, 0, 1),
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
                ]);
            }
        }
    }
}
