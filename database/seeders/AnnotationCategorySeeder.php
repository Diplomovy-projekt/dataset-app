<?php

namespace Database\Seeders;

use App\Models\AnnotationClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnnotationCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AnnotationClass::factory()
            ->count(10) // Adjust the number as needed
            ->create();
    }
}
