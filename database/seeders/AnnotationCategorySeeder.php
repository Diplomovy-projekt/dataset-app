<?php

namespace Database\Seeders;

use App\Models\AnnotationCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnnotationCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AnnotationCategory::factory()
            ->count(10) // Adjust the number as needed
            ->create();
    }
}
