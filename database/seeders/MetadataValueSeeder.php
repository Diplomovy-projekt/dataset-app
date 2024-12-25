<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\MetadataType;
use App\Models\MetadataValue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MetadataValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch MetadataTypes by name
        $languageType = MetadataType::where('name', 'Language')->first();
        $centuryType = MetadataType::where('name', 'Century')->first();

        // Check if the metadata types exist before proceeding
        if (!$languageType || !$centuryType) {
            throw new \Exception('One or more MetadataTypes not found.');
        }

        // Seed MetadataValues for each MetadataType (Same as your existing seeder)
        $values = [
            // Languages
            [
                'metadata_type_id' => $languageType->id,
                'value' => 'English',
            ],
            [
                'metadata_type_id' => $languageType->id,
                'value' => 'French',
            ],
            [
                'metadata_type_id' => $languageType->id,
                'value' => 'Spanish',
            ],
            // Centuries
            [
                'metadata_type_id' => $centuryType->id,
                'value' => '19th Century',
            ],
            [
                'metadata_type_id' => $centuryType->id,
                'value' => '20th Century',
            ],
            [
                'metadata_type_id' => $centuryType->id,
                'value' => '21st Century',
            ]
        ];

        // Insert the MetadataValues into the MetadataValues table
        foreach ($values as $value) {
            MetadataValue::firstOrCreate($value);
        }

        // Now associate MetadataValues with DatasetMetadata
        // Fetch all datasets (assuming you have already seeded Datasets)
        $datasets = Dataset::all();

        // For each dataset, create DatasetMetadata relationships
        foreach ($datasets as $dataset) {
            // Link MetadataValues to the DatasetMetadata (Randomly or in a predefined way)

            // You can link MetadataValues to a dataset (Example: Linking Languages, Centuries, Categories)
            $dataset->metadata()->createMany([
                [
                    'metadata_value_id' => MetadataValue::where('value', 'English')->first()->id,
                    'dataset_id' => $dataset->id,
                ],
                [
                    'metadata_value_id' => MetadataValue::where('value', '19th Century')->first()->id,
                    'dataset_id' => $dataset->id,
                ]
            ]);
        }
    }
}
