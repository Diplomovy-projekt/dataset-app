<?php

namespace Database\Seeders;

use App\Models\MetadataType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MetadataTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metadataTypes = [
            [
                'name' => 'Language',
                'description' => 'Defines the languages used in the dataset.',
            ],
            [
                'name' => 'Century',
                'description' => 'Indicates the time period the documents were written.',
            ]
        ];

        foreach ($metadataTypes as $type) {
            MetadataType::create($type);
        }
    }
}
