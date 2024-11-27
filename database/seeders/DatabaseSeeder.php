<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Seed Property Types and Values first (independent)
            PropertyTypeSeeder::class,
            PropertyValueSeeder::class,

            // Seed Categories and Formats (dependent on Property Values if needed)
            //AnnotationCategorySeeder::class, // Seed categories (Language, Century, etc.)
            AnnotationFormatSeeder::class,  // Seed annotation formats (after categories)

            // Seed Datasets (depends on Property Values and Categories)
            //DatasetSeeder::class,

            // Seed Annotation Data (depends on Categories and Formats)
            //AnnotationDataSeeder::class,

            // Seed Images (depends on Datasets and Annotations)
            //ImageSeeder::class,

            // Seed Users last (can depend on other data)
            UserSeeder::class,
        ]);
    }

}
