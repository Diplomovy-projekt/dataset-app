<?php

namespace Database\Seeders;

use App\Models\MetadataType;
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
            ],
            [
            'name' => 'Image Granularity',
            'description' => 'Indicates if the image is a full page or a fragment.',
            ],
            [
            'name' => 'Dataset Partition',
            'description' => 'Indicates the dataset partitioning.',
            ]
        ];

        foreach ($metadataTypes as $type) {
            MetadataType::firstOrCreate(
                ['name' => $type['name']],
                ['description' => $type['description']]
            );
        }
    }
}
