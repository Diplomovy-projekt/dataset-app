<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $propertyTypes = [
            [
                'name' => 'Language',
                'description' => 'Defines the languages used in the dataset.',
            ],
            [
                'name' => 'Century',
                'description' => 'Indicates the time period the documents were written.',
            ],
            [
                'name' => 'Category',
                'description' => 'Represents the classification category of annotated data.',
            ],
        ];

        foreach ($propertyTypes as $type) {
            PropertyType::create($type);
        }
    }
}
