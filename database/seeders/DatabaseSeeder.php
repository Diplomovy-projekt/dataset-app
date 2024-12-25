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
            // Seed Metadata Types and Values first (independent)
            MetadataTypeSeeder::class,
            MetadataValueSeeder::class,

            // Seed Categories and Formats (dependent on Metadata Values if needed)
            //AnnotationClassSeeder::class, // Seed categories (Language, Century, etc.)

            // Seed Datasets (depends on Metadata Values and Categories)
            //DatasetSeeder::class,

            // Seed Annotation Data (depends on Categories and Formats)
            //AnnotationDataSeeder::class,

            // Seed Images (depends on Datasets and Annotations)
            //ImageSeeder::class,

            // Seed Users last (can depend on other data)
            UserSeeder::class,

            CategorySeeder::class,
        ]);
    }

}
